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
            $pagesResponse = Http::get('https://graph.facebook.com/v21.0/me/accounts', [
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

            // Se não tem páginas, retornar erro detalhado com debug de permissões
            if (empty($pages)) {
                return response()->json([
                    'error' => 'Nenhuma página encontrada',
                    'message' => 'Este usuário não gerencia nenhuma página do Facebook ou não tem as permissões necessárias',
                    'debug' => $debugInfo,
                    'solutions' => [
                        'missing_pages_permission' => !in_array('pages_show_list', $permissions) ? 'Reconecte o app solicitando a permissão "pages_show_list"' : null,
                        'no_pages_managed' => 'Certifique-se de ser admin/editor de pelo menos uma página do Facebook',
                        'app_review_needed' => 'Se o app está em modo Live, pode precisar de revisão do Facebook',
                        'test_users' => 'Use usuários que sejam Desenvolvedores/Testadores do app durante desenvolvimento'
                    ],
                    'next_steps' => [
                        '1. Verificar se você é administrador de alguma página do Facebook',
                        '2. Reconectar o app com permissão "pages_show_list"',
                        '3. Se necessário, adicionar seu usuário como Desenvolvedor/Tester no app'
                    ]
                ], 404);
            }

            $accountData = ['pages' => $pages];

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
                'success' => true,
                'data' => $accountData,
                'debug' => $debugInfo
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao obter IDs da conta', [
                'platform_id' => $platform->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Erro interno: ' . $e->getMessage(),
                'debug' => [
                    'exception_message' => $e->getMessage(),
                    'exception_file' => $e->getFile(),
                    'exception_line' => $e->getLine()
                ]
            ], 500);
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
}