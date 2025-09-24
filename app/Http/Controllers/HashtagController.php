<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HashtagController extends Controller
{
    /**
     * Página principal de monitoramento
     */
    public function index(Platform $platform)
    {
        if (!$platform->is_connected) {
            return redirect()->route('platforms.show', $platform)
                ->with('error', 'Plataforma não está conectada. Conecte primeiro para monitorar hashtags.');
        }

        return view('hashtags.index', compact('platform'));
    }

    /**
     * Obter IDs necessários (Página e Instagram Business)
     */
    public function getAccountIds(Platform $platform)
    {
        if (!$platform->is_connected || !$platform->access_token) {
            return response()->json([
                'error' => 'Plataforma não conectada',
                'debug' => [
                    'is_connected' => $platform->is_connected,
                    'has_token' => !empty($platform->access_token),
                    'token_preview' => $platform->access_token ? substr($platform->access_token, 0, 10) . '...' : null
                ]
            ], 400);
        }

        try {
            // 1. Verificar permissões primeiro
            $permissionsResponse = Http::get('https://graph.facebook.com/v21.0/me/permissions', [
                'access_token' => $platform->access_token
            ]);

            $permissions = [];
            if ($permissionsResponse->successful()) {
                $permissionsData = $permissionsResponse->json();
                $permissions = collect($permissionsData['data'] ?? [])
                    ->where('status', 'granted')
                    ->pluck('permission')
                    ->toArray();
            }

            // 2. Testar token básico
            $meResponse = Http::get('https://graph.facebook.com/v21.0/me', [
                'fields' => 'id,name',
                'access_token' => $platform->access_token
            ]);

            $debugInfo = [
                'me_response_status' => $meResponse->status(),
                'me_response_success' => $meResponse->successful(),
                'me_response_body' => $meResponse->json(),
                'granted_permissions' => $permissions,
                'has_pages_show_list' => in_array('pages_show_list', $permissions),
                'token_used' => substr($platform->access_token, 0, 20) . '...'
            ];

            if (!$meResponse->successful()) {
                return response()->json([
                    'error' => 'Token inválido ou expirado',
                    'debug' => $debugInfo
                ], 400);
            }

            // 3. Obter páginas do Facebook
            $pagesResponse = Http::get('https://graph.facebook.com/v23.0/me/accounts', [
                'fields' => 'id,name,access_token,tasks',
                'access_token' => $platform->access_token
            ]);

            $debugInfo['pages_response_status'] = $pagesResponse->status();
            $debugInfo['pages_response_success'] = $pagesResponse->successful();
            $debugInfo['pages_response_body'] = $pagesResponse->json();

            if (!$pagesResponse->successful()) {
                return response()->json([
                    'error' => 'Erro ao obter páginas do Facebook',
                    'debug' => $debugInfo
                ], 400);
            }

            $pagesData = $pagesResponse->json();
            $pages = $pagesData['data'] ?? [];
            
            $debugInfo['pages_count'] = count($pages);
            $debugInfo['pages_raw'] = $pagesData;

            $accountData = ['pages' => $pages];

            // Se não tem páginas, ainda retornar sucesso mas com informações de diagnóstico
            if (empty($pages)) {
                $debugInfo['diagnosis'] = [
                    'issue' => 'Nenhuma página encontrada',
                    'possible_causes' => [
                        'missing_pages_permission' => !in_array('pages_show_list', $permissions),
                        'no_pages_managed' => 'Usuário não é admin/editor de nenhuma página',
                        'app_in_development' => 'App pode estar em modo desenvolvimento',
                        'app_needs_review' => 'App pode precisar de revisão do Facebook'
                    ],
                    'solutions' => [
                        'create_page' => 'Criar uma página de teste no Facebook',
                        'add_permission' => 'Reconectar com permissão pages_show_list',
                        'add_to_app' => 'Adicionar usuário como Desenvolvedor/Testador do app',
                        'check_app_mode' => 'Verificar se app está em modo Live ou Development'
                    ],
                    'next_steps' => [
                        '1. Verificar permissões concedidas acima',
                        '2. Criar ou ter acesso a uma página do Facebook',
                        '3. Reconectar se necessário',
                        '4. Verificar modo do app no Facebook Developers'
                    ]
                ];
            }

            // 4. Para cada página, verificar se tem Instagram Business vinculado
            foreach ($pages as $index => $page) {
                $instagramResponse = Http::get("https://graph.facebook.com/v21.0/{$page['id']}", [
                    'fields' => 'instagram_business_account',
                    'access_token' => $platform->access_token
                ]);

                if ($instagramResponse->successful()) {
                    $instagram = $instagramResponse->json();
                    if (isset($instagram['instagram_business_account'])) {
                        $accountData['pages'][$index]['instagram_business_account'] = $instagram['instagram_business_account'];
                    }
                }
            }

            // Salvar dados extras na plataforma
            try {
                $platform->update([
                    'extra_data' => array_merge($platform->extra_data ?? [], [
                        'account_data' => $accountData,
                        'last_sync' => now()->toISOString(),
                        'last_debug' => $debugInfo
                    ])
                ]);
            } catch (\Exception $updateException) {
                Log::error('Erro ao salvar extra_data', [
                    'error' => $updateException->getMessage()
                ]);
                // Continua mesmo se falhar para salvar
            }

            return response()->json([
                'success' => count($pages) > 0,
                'data' => $accountData,
                'debug' => $debugInfo,
                'message' => count($pages) > 0 ? 'Páginas encontradas com sucesso' : 'Nenhuma página encontrada - veja debug para mais detalhes'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao obter IDs da conta', [
                'platform_id' => $platform->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'data' => null,
                'error' => 'Erro interno capturado',
                'debug' => [
                    'exception_message' => $e->getMessage(),
                    'exception_file' => $e->getFile(),
                    'exception_line' => $e->getLine(),
                    'exception_trace' => $e->getTraceAsString()
                ],
                'message' => 'Erro interno - veja debug para detalhes'
            ]);
        }
    }

    /**
     * Buscar hashtags no Instagram
     */
    public function searchHashtags(Request $request, Platform $platform)
    {
        $request->validate([
            'hashtag' => 'required|string|max:100',
            'instagram_business_id' => 'required|string'
        ]);

        $hashtag = $request->hashtag;
        $instagramBusinessId = $request->instagram_business_id;

        try {
            // 1. Buscar ID da hashtag
            $hashtagSearchResponse = Http::get('https://graph.facebook.com/v21.0/ig_hashtag_search', [
                'user_id' => $instagramBusinessId,
                'q' => $hashtag,
                'access_token' => $platform->access_token
            ]);

            if (!$hashtagSearchResponse->successful()) {
                return response()->json(['error' => 'Erro ao buscar hashtag'], 400);
            }

            $hashtagData = $hashtagSearchResponse->json();
            if (empty($hashtagData['data'])) {
                return response()->json(['error' => 'Hashtag não encontrada'], 404);
            }

            $hashtagId = $hashtagData['data'][0]['id'];

            // 2. Buscar posts recentes da hashtag
            $postsResponse = Http::get("https://graph.facebook.com/v21.0/{$hashtagId}/recent_media", [
                'user_id' => $instagramBusinessId,
                'fields' => 'id,caption,media_type,media_url,permalink,timestamp,username',
                'limit' => 25,
                'access_token' => $platform->access_token
            ]);

            if (!$postsResponse->successful()) {
                return response()->json(['error' => 'Erro ao buscar posts da hashtag'], 400);
            }

            $posts = $postsResponse->json();

            return response()->json([
                'success' => true,
                'hashtag' => $hashtag,
                'hashtag_id' => $hashtagId,
                'posts' => $posts['data'] ?? [],
                'total_posts' => count($posts['data'] ?? [])
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar hashtags', [
                'platform_id' => $platform->id,
                'hashtag' => $hashtag,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Buscar menções no Instagram
     */
    public function getMentions(Request $request, Platform $platform)
    {
        $request->validate([
            'instagram_business_id' => 'required|string'
        ]);

        $instagramBusinessId = $request->instagram_business_id;

        try {
            $mentionsResponse = Http::get("https://graph.facebook.com/v21.0/{$instagramBusinessId}/mentions", [
                'fields' => 'id,username,caption,media_type,media_url,permalink,timestamp',
                'limit' => 25,
                'access_token' => $platform->access_token
            ]);

            if (!$mentionsResponse->successful()) {
                return response()->json(['error' => 'Erro ao buscar menções'], 400);
            }

            $mentions = $mentionsResponse->json();

            return response()->json([
                'success' => true,
                'mentions' => $mentions['data'] ?? [],
                'total_mentions' => count($mentions['data'] ?? [])
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar menções', [
                'platform_id' => $platform->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Buscar menções na página do Facebook
     */
    public function getFacebookMentions(Request $request, Platform $platform)
    {
        $request->validate([
            'page_id' => 'required|string'
        ]);

        $pageId = $request->page_id;

        try {
            // Posts que mencionam a página
            $taggedResponse = Http::get("https://graph.facebook.com/v21.0/{$pageId}/tagged", [
                'fields' => 'id,from,message,created_time,permalink_url',
                'limit' => 25,
                'access_token' => $platform->access_token
            ]);

            $tagged = $taggedResponse->successful() ? $taggedResponse->json() : ['data' => []];

            return response()->json([
                'success' => true,
                'tagged_posts' => $tagged['data'] ?? [],
                'total_tagged' => count($tagged['data'] ?? [])
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar menções do Facebook', [
                'platform_id' => $platform->id,
                'page_id' => $pageId,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Dashboard completo de monitoramento
     */
    public function dashboard(Platform $platform)
    {
        if (!$platform->is_connected) {
            return redirect()->route('platforms.show', $platform)
                ->with('error', 'Plataforma não está conectada.');
        }

        $accountData = $platform->extra_data['account_data'] ?? null;

        return view('hashtags.dashboard', compact('platform', 'accountData'));
    }

    /**
     * API endpoint para teste rápido
     */
    public function testApi(Platform $platform)
    {
        try {
            // Teste simples: obter informações básicas da conta
            $response = Http::get('https://graph.facebook.com/v21.0/me', [
                'fields' => 'id,name',
                'access_token' => $platform->access_token
            ]);

            return response()->json([
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'data' => $response->json(),
                'platform_connected' => $platform->is_connected,
                'token_exists' => !empty($platform->access_token)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Debug completo da conexão Facebook
     */
    public function debugFacebook(Platform $platform)
    {
        try {
            $results = [];
            
            // 1. Verificar token básico
            $meResponse = Http::get('https://graph.facebook.com/v21.0/me', [
                'fields' => 'id,name',
                'access_token' => $platform->access_token
            ]);
            
            $results['me_test'] = [
                'success' => $meResponse->successful(),
                'status' => $meResponse->status(),
                'data' => $meResponse->json()
            ];

            // 2. Verificar permissões
            $permissionsResponse = Http::get('https://graph.facebook.com/v21.0/me/permissions', [
                'access_token' => $platform->access_token
            ]);
            
            $results['permissions'] = [
                'success' => $permissionsResponse->successful(),
                'status' => $permissionsResponse->status(),
                'data' => $permissionsResponse->json()
            ];

            // 3. Testar pages com diferentes parâmetros
            $pagesResponse = Http::get('https://graph.facebook.com/v21.0/me/accounts', [
                'access_token' => $platform->access_token
            ]);
            
            $results['pages_simple'] = [
                'success' => $pagesResponse->successful(),
                'status' => $pagesResponse->status(),
                'data' => $pagesResponse->json()
            ];

            // 4. Testar pages com fields detalhados
            $pagesDetailedResponse = Http::get('https://graph.facebook.com/v21.0/me/accounts', [
                'fields' => 'id,name,access_token,tasks,category,verification_status',
                'access_token' => $platform->access_token
            ]);
            
            $results['pages_detailed'] = [
                'success' => $pagesDetailedResponse->successful(),
                'status' => $pagesDetailedResponse->status(),
                'data' => $pagesDetailedResponse->json()
            ];

            // 5. Informações do token
            $tokenDebugResponse = Http::get('https://graph.facebook.com/v21.0/debug_token', [
                'input_token' => $platform->access_token,
                'access_token' => $platform->access_token
            ]);
            
            $results['token_debug'] = [
                'success' => $tokenDebugResponse->successful(),
                'status' => $tokenDebugResponse->status(),
                'data' => $tokenDebugResponse->json()
            ];

            return response()->json([
                'success' => true,
                'platform_info' => [
                    'id' => $platform->id,
                    'name' => $platform->name,
                    'is_connected' => $platform->is_connected,
                    'token_preview' => $platform->access_token ? substr($platform->access_token, 0, 20) . '...' : null,
                    'updated_at' => $platform->updated_at
                ],
                'tests' => $results,
                'recommendations' => $this->getRecommendations($results)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Gerar recomendações baseadas nos resultados dos testes
     */
    private function getRecommendations($results)
    {
        $recommendations = [];

        // Verificar se o token funciona
        if (!$results['me_test']['success']) {
            $recommendations[] = 'Token inválido ou expirado. Reconecte a plataforma.';
        }

        // Verificar permissões
        if ($results['permissions']['success']) {
            $permissions = collect($results['permissions']['data']['data'] ?? [])
                ->where('status', 'granted')
                ->pluck('permission');
                
            if (!$permissions->contains('pages_show_list')) {
                $recommendations[] = 'Permissão "pages_show_list" não foi concedida. Reconecte pedindo esta permissão.';
            }
            
            if (!$permissions->contains('pages_read_engagement')) {
                $recommendations[] = 'Permissão "pages_read_engagement" não foi concedida. Necessária para ler posts e engajamento.';
            }
        }

        // Verificar se retornou páginas
        if ($results['pages_simple']['success']) {
            $pagesCount = count($results['pages_simple']['data']['data'] ?? []);
            if ($pagesCount === 0) {
                $recommendations[] = 'O usuário não gerencia nenhuma página do Facebook ou o app não tem acesso.';
                $recommendations[] = 'Certifique-se de que o usuário que fez login é administrador de pelo menos uma página.';
                $recommendations[] = 'Se o app está em modo Live, verifique se passou pela revisão do Facebook.';
            }
        }

        // Verificar informações do token
        if ($results['token_debug']['success'] && isset($results['token_debug']['data']['data'])) {
            $tokenInfo = $results['token_debug']['data']['data'];
            
            if (isset($tokenInfo['expires_at']) && $tokenInfo['expires_at'] < time()) {
                $recommendations[] = 'Token expirado. Gere um novo token.';
            }
            
            if (isset($tokenInfo['app_id'])) {
                $recommendations[] = "Token pertence ao app: {$tokenInfo['app_id']}";
            }
            
            if (isset($tokenInfo['type']) && $tokenInfo['type'] !== 'USER') {
                $recommendations[] = 'Token não é de usuário. Para acessar me/accounts, use um User Access Token.';
            }
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Todos os testes passaram! A API deveria estar funcionando.';
        }

        return $recommendations;
    }

    /**
     * Buscar páginas tanto pessoais quanto do Business Manager
     */
    public function getAccountIdsComplete(Platform $platform)
    {
        if (!$platform->is_connected || !$platform->access_token) {
            return response()->json([
                'success' => false,
                'error' => 'Plataforma não conectada',
                'debug' => [
                    'is_connected' => $platform->is_connected,
                    'has_token' => !empty($platform->access_token)
                ]
            ]);
        }

        try {
            $allPages = [];
            $debugInfo = [
                'searches_performed' => [],
                'errors' => []
            ];

            // 1. Buscar páginas pessoais (/me/accounts)
            $personalPagesResponse = Http::get('https://graph.facebook.com/v21.0/me/accounts', [
                'fields' => 'id,name,access_token,tasks',
                'access_token' => $platform->access_token
            ]);

            $debugInfo['searches_performed']['personal_pages'] = [
                'endpoint' => '/me/accounts',
                'status' => $personalPagesResponse->status(),
                'success' => $personalPagesResponse->successful(),
                'response' => $personalPagesResponse->json()
            ];

            if ($personalPagesResponse->successful()) {
                $personalPages = $personalPagesResponse->json()['data'] ?? [];
                foreach ($personalPages as $page) {
                    $page['source'] = 'personal';
                    $allPages[] = $page;
                }
            }

            // 2. Buscar Business Managers (/me/businesses)
            $businessesResponse = Http::get('https://graph.facebook.com/v21.0/me/businesses', [
                'fields' => 'id,name',
                'access_token' => $platform->access_token
            ]);

            $debugInfo['searches_performed']['businesses'] = [
                'endpoint' => '/me/businesses',
                'status' => $businessesResponse->status(),
                'success' => $businessesResponse->successful(),
                'response' => $businessesResponse->json()
            ];

            if ($businessesResponse->successful()) {
                $businesses = $businessesResponse->json()['data'] ?? [];
                
                // 3. Para cada Business Manager, buscar suas páginas
                foreach ($businesses as $business) {
                    $businessPagesResponse = Http::get("https://graph.facebook.com/v21.0/{$business['id']}/client_pages", [
                        'fields' => 'id,name,access_token,tasks',
                        'access_token' => $platform->access_token
                    ]);

                    $debugInfo['searches_performed']['business_pages'][$business['id']] = [
                        'endpoint' => "/{$business['id']}/client_pages",
                        'business_name' => $business['name'],
                        'status' => $businessPagesResponse->status(),
                        'success' => $businessPagesResponse->successful(),
                        'response' => $businessPagesResponse->json()
                    ];

                    if ($businessPagesResponse->successful()) {
                        $businessPages = $businessPagesResponse->json()['data'] ?? [];
                        foreach ($businessPages as $page) {
                            $page['source'] = 'business';
                            $page['business_name'] = $business['name'];
                            $page['business_id'] = $business['id'];
                            $allPages[] = $page;
                        }
                    }
                }
            }

            // 4. Remover páginas duplicadas (mesmo ID)
            $uniquePages = [];
            $pageIds = [];
            foreach ($allPages as $page) {
                if (!in_array($page['id'], $pageIds)) {
                    $pageIds[] = $page['id'];
                    $uniquePages[] = $page;
                }
            }

            // 5. Para cada página única, verificar Instagram Business
            foreach ($uniquePages as $index => $page) {
                $instagramResponse = Http::get("https://graph.facebook.com/v21.0/{$page['id']}", [
                    'fields' => 'instagram_business_account',
                    'access_token' => $platform->access_token
                ]);

                if ($instagramResponse->successful()) {
                    $instagram = $instagramResponse->json();
                    if (isset($instagram['instagram_business_account'])) {
                        $uniquePages[$index]['instagram_business_account'] = $instagram['instagram_business_account'];
                    }
                }
            }

            $accountData = [
                'pages' => $uniquePages,
                'total_found' => count($uniquePages),
                'sources' => [
                    'personal' => count(array_filter($uniquePages, fn($p) => ($p['source'] ?? '') === 'personal')),
                    'business' => count(array_filter($uniquePages, fn($p) => ($p['source'] ?? '') === 'business'))
                ]
            ];

            // Salvar dados extras na plataforma
            try {
                $platform->update([
                    'extra_data' => array_merge($platform->extra_data ?? [], [
                        'account_data_complete' => $accountData,
                        'last_sync_complete' => now()->toISOString(),
                        'last_debug_complete' => $debugInfo
                    ])
                ]);
            } catch (\Exception $updateException) {
                $debugInfo['errors']['update_failed'] = $updateException->getMessage();
            }

            return response()->json([
                'success' => true,
                'message' => "Encontradas {$accountData['total_found']} páginas ({$accountData['sources']['personal']} pessoais, {$accountData['sources']['business']} do Business Manager)",
                'data' => $accountData,
                'debug' => $debugInfo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erro interno capturado',
                'debug' => [
                    'exception_message' => $e->getMessage(),
                    'exception_file' => $e->getFile(),
                    'exception_line' => $e->getLine(),
                    'exception_trace' => $e->getTraceAsString()
                ],
                'message' => 'Erro interno - veja debug para detalhes'
            ]);
        }
    }

    /**
     * Versão melhorada usando Facebook SDK
     */
    public function getAccountIdsSDK(Platform $platform)
    {
        if (!$platform->is_connected || !$platform->access_token) {
            return response()->json([
                'success' => false,
                'error' => 'Plataforma não conectada',
                'debug' => [
                    'is_connected' => $platform->is_connected,
                    'has_token' => !empty($platform->access_token)
                ]
            ]);
        }

        try {
            // Se app_id for de teste, usar configurações do .env
            $appId = $platform->app_id;
            $appSecret = $platform->app_secret;
            
            if ($appId === '123' || $appId === 'test' || empty($appId)) {
                $appId = config('services.facebook.app_id', env('FACEBOOK_APP_ID'));
                $appSecret = config('services.facebook.app_secret', env('FACEBOOK_APP_SECRET'));
            }

            $facebook = new \App\Services\FacebookService(
                $platform->access_token, 
                $appId, 
                $appSecret
            );

            // Verificar se token é válido
            if (!$facebook->isTokenValid()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Token inválido ou expirado',
                    'message' => 'Por favor, reconecte a plataforma',
                    'debug' => [
                        'using_app_id' => $appId,
                        'platform_app_id' => $platform->app_id,
                        'token_preview' => substr($platform->access_token, 0, 20) . '...'
                    ]
                ]);
            }

            // Obter informações completas
            $result = $facebook->getAllPagesComplete();
            
            $accountData = [
                'pages' => $result['pages'],
                'total_found' => count($result['pages']),
                'sources' => [
                    'personal' => count(array_filter($result['pages'], fn($p) => ($p['source'] ?? '') === 'personal')),
                    'business' => count(array_filter($result['pages'], fn($p) => ($p['source'] ?? '') === 'business'))
                ],
                'instagram_accounts' => count(array_filter($result['pages'], fn($p) => isset($p['instagram_business_account'])))
            ];

            // Obter permissões para debug
            $permissions = $facebook->getPermissions();
            $grantedPermissions = [];
            if (isset($permissions['data'])) {
                $grantedPermissions = array_filter(
                    array_map(fn($p) => $p['status'] === 'granted' ? $p['permission'] : null, $permissions['data'])
                );
            }

            $debugInfo = [
                'sdk_version' => 'Facebook Graph SDK',
                'granted_permissions' => $grantedPermissions,
                'has_pages_show_list' => in_array('pages_show_list', $grantedPermissions),
                'has_instagram_basic' => in_array('instagram_basic', $grantedPermissions),
                'api_calls_made' => $result['debug'],
                'token_preview' => substr($platform->access_token, 0, 20) . '...'
            ];

            // Salvar dados na plataforma
            try {
                $platform->update([
                    'extra_data' => array_merge($platform->extra_data ?? [], [
                        'account_data_sdk' => $accountData,
                        'last_sync_sdk' => now()->toISOString(),
                        'last_debug_sdk' => $debugInfo
                    ])
                ]);
            } catch (\Exception $updateException) {
                $debugInfo['update_error'] = $updateException->getMessage();
            }

            $message = "✅ SDK: {$accountData['total_found']} páginas encontradas";
            if ($accountData['sources']['personal'] > 0) {
                $message .= " ({$accountData['sources']['personal']} pessoais";
            }
            if ($accountData['sources']['business'] > 0) {
                $message .= ", {$accountData['sources']['business']} business)";
            } else {
                $message .= ")";
            }
            
            if ($accountData['instagram_accounts'] > 0) {
                $message .= " - {$accountData['instagram_accounts']} com Instagram";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $accountData,
                'debug' => $debugInfo
            ]);

        } catch (\Exception $e) {
            Log::error('Erro no FacebookService', [
                'platform_id' => $platform->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao usar Facebook SDK',
                'debug' => [
                    'exception_message' => $e->getMessage(),
                    'exception_file' => $e->getFile(),
                    'exception_line' => $e->getLine(),
                    'suggestion' => 'Verifique se o Facebook SDK foi instalado corretamente'
                ],
                'message' => 'Erro interno - veja debug para detalhes'
            ]);
        }
    }
}