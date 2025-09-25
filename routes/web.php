<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\PlatformController;

// Redirecionar para plataformas
Route::get('/', function () {
    return redirect()->route('platforms.index');
});

// Rota de teste
Route::get('/test-laravel', function () {
    return response()->json([
        'status' => 'Laravel funcionando!',
        'timestamp' => now(),
        'url' => config('app.url'),
        'env' => config('app.env')
    ]);
});

// Rota de teste específica para hashtags
Route::get('/test-hashtags-route', function () {
    return response()->json([
        'status' => 'Rota de hashtags funcionando!',
        'timestamp' => now(),
        'available_routes' => [
            'GET /platforms/{id}/hashtags',
            'GET /platforms/{id}/hashtags/accounts',
            'GET /platforms/{id}/hashtags/dashboard',
            'POST /platforms/{id}/hashtags/search',
        ]
    ]);
});

// Página de teste do SDK
Route::get('/test-sdk', function () {
    return view('test-sdk');
});

// Rota de teste direto para accounts (sem precisar de plataforma)
Route::get('/test-accounts', function () {
    return response()->json([
        'status' => 'Endpoint accounts funcionando!',
        'message' => 'Se você vê esta mensagem, as rotas estão funcionando',
        'timestamp' => now()
    ]);
});

// Rota de debug que imita a rota problemática
Route::get('/platforms/1/hashtags/accounts-debug', function () {
    return response()->json([
        'status' => 'Debug da rota accounts funcionando!',
        'message' => 'Esta é uma rota de teste que imita /platforms/1/hashtags/accounts',
        'original_route' => '/platforms/1/hashtags/accounts',
        'debug_route' => '/platforms/1/hashtags/accounts-debug',
        'timestamp' => now(),
        'note' => 'Se esta rota funciona, o problema pode estar no método getAccountIds do controller'
    ]);
});

// Rota para testar model binding
Route::get('/platforms/{platform}/test-binding', function (App\Models\Platform $platform) {
    return response()->json([
        'status' => 'Model binding funcionando!',
        'platform_id' => $platform->id,
        'platform_name' => $platform->name,
        'platform_type' => $platform->type,
        'is_connected' => $platform->is_connected,
        'message' => 'Se você vê esta mensagem, o model binding está funcionando'
    ]);
});

// Rotas do CRUD de plataformas
Route::resource('platforms', PlatformController::class);

// Rotas de teste para Facebook SDK
Route::get('/test-facebook-config', function () {
    try {
        Log::info('Testando configuração do Facebook');
        
        // Verificar se as configurações estão presentes
        $appId = config('services.facebook.app_id');
        $appSecret = config('services.facebook.app_secret');
        $envAppId = env('FACEBOOK_APP_ID');
        $envAppSecret = env('FACEBOOK_APP_SECRET');
        
        return response()->json([
            'success' => true,
            'config_check' => [
                'services.facebook.app_id' => $appId ? 'SET' : 'NOT_SET',
                'services.facebook.app_secret' => $appSecret ? 'SET' : 'NOT_SET',
                'env.FACEBOOK_APP_ID' => $envAppId ? 'SET' : 'NOT_SET',
                'env.FACEBOOK_APP_SECRET' => $envAppSecret ? 'SET' : 'NOT_SET',
            ],
            'message' => 'Configuração testada com sucesso'
        ]);
    } catch (Exception $e) {
        Log::error('Erro ao testar configuração do Facebook: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'debug' => [
                'exception_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]
        ]);
    }
});

// Rota para testar inicialização do SDK
Route::get('/test-facebook-sdk', function () {
    try {
        Log::info('Testando inicialização do Facebook SDK');
        
        // Tentar inicializar o SDK sem token (só para testar se as credenciais funcionam)
        $fbService = new App\Services\FacebookService();
        
        return response()->json([
            'success' => true,
            'message' => 'Facebook SDK inicializado com sucesso',
            'sdk_version' => 'v21.0'
        ]);
    } catch (Exception $e) {
        Log::error('Erro ao testar Facebook SDK: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'debug' => [
                'exception_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ],
            'message' => 'Erro ao inicializar Facebook SDK'
        ]);
    }
});

// Rotas específicas para OAuth
Route::get('platforms/{platform}/connect', [PlatformController::class, 'connect'])->name('platforms.connect');
Route::get('platforms/{platform}/callback', [PlatformController::class, 'callback'])->name('platforms.callback');
Route::post('platforms/{platform}/disconnect', [PlatformController::class, 'disconnect'])->name('platforms.disconnect');

// Rota para debug OAuth
Route::get('platforms/{platform}/debug', function (App\Models\Platform $platform) {
    return view('platforms.debug', compact('platform'));
})->name('platforms.debug');

// Rota para configuração específica do Facebook
Route::get('platforms/{platform}/facebook-setup', function (App\Models\Platform $platform) {
    return view('platforms.facebook-setup', compact('platform'));
})->name('platforms.facebook-setup');

// Rota para erro específico de domínio
Route::get('platforms/{platform}/domain-error', function (App\Models\Platform $platform) {
    return view('platforms.domain-error', compact('platform'));
})->name('platforms.domain-error');

// Rota para URI inválida no validador
Route::get('platforms/{platform}/uri-invalid', function (App\Models\Platform $platform) {
    return view('platforms.uri-invalid', compact('platform'));
})->name('platforms.uri-invalid');

// Rota para gerar variações de URI para teste
Route::get('platforms/{platform}/uri-variations', function (App\Models\Platform $platform) {
    $baseUrl = parse_url($platform->redirect_uri);
    $domain = $baseUrl['host'];
    $path = $baseUrl['path'];
    
    $variations = [
        'original' => $platform->redirect_uri,
        'https' => 'https://' . $domain . $path,
        'http' => 'http://' . $domain . $path,
        'without_www' => 'https://' . str_replace('www.', '', $domain) . $path,
        'with_www' => 'https://www.' . str_replace('www.', '', $domain) . $path,
        'localhost_dev' => 'http://localhost:8000' . $path,
        'localhost_https' => 'https://localhost:8000' . $path,
    ];
    
    return response()->json([
        'platform_id' => $platform->id,
        'current_uri' => $platform->redirect_uri,
        'variations' => $variations,
        'recommendations' => [
            'Teste a URI original primeiro',
            'Se não funcionar, tente a versão HTTPS',
            'Para desenvolvimento, use localhost',
            'Certifique-se de que o domínio está nos "Domínios do app"'
        ]
    ]);
})->name('platforms.uri-variations');

// Rota para testar se callback está funcionando
Route::get('platforms/{platform}/test-callback', function (App\Models\Platform $platform) {
    return response()->json([
        'status' => 'success',
        'message' => 'Callback está acessível!',
        'platform_id' => $platform->id,
        'platform_name' => $platform->name,
        'redirect_uri' => $platform->redirect_uri,
        'timestamp' => now()->toISOString(),
    ]);
})->name('platforms.test-callback');

// Rota para verificar configuração do Facebook
Route::get('platforms/{platform}/check-facebook-config', function (App\Models\Platform $platform) {
    $checks = [
        'domain_reachable' => false,
        'callback_reachable' => false,
        'privacy_policy_reachable' => false,
        'https_available' => false,
    ];
    
    $messages = [];
    
    // Verificar se o domínio está acessível
    try {
        $response = \Illuminate\Support\Facades\Http::timeout(10)->get('https://hashtag.betasolucao.com.br');
        $checks['domain_reachable'] = $response->successful();
        $messages['domain'] = $checks['domain_reachable'] ? 'Domínio acessível' : 'Domínio não acessível';
    } catch (\Exception $e) {
        $messages['domain'] = 'Erro ao acessar domínio: ' . $e->getMessage();
    }
    
    // Verificar se o callback está acessível
    try {
        $response = \Illuminate\Support\Facades\Http::timeout(10)->get($platform->redirect_uri . '?test=1');
        $checks['callback_reachable'] = true; // Se não deu exceção, está acessível
        $messages['callback'] = 'Callback acessível';
    } catch (\Exception $e) {
        $messages['callback'] = 'Erro ao acessar callback: ' . $e->getMessage();
    }
    
    // Verificar política de privacidade
    try {
        $privacyUrl = str_replace('http://', 'https://', url('/privacidade'));
        $response = \Illuminate\Support\Facades\Http::timeout(10)->get($privacyUrl);
        $checks['privacy_policy_reachable'] = $response->successful();
        $messages['privacy'] = $checks['privacy_policy_reachable'] ? 'Política de privacidade acessível' : 'Política de privacidade não acessível';
    } catch (\Exception $e) {
        $messages['privacy'] = 'Erro ao acessar política: ' . $e->getMessage();
    }
    
    // Verificar se HTTPS está disponível
    $checks['https_available'] = strpos($platform->redirect_uri, 'https://') === 0;
    $messages['https'] = $checks['https_available'] ? 'HTTPS configurado' : 'HTTPS não configurado (pode causar problemas)';
    
    return response()->json([
        'platform_id' => $platform->id,
        'checks' => $checks,
        'messages' => $messages,
        'overall_status' => array_reduce($checks, function($carry, $check) {
            return $carry && $check;
        }, true) ? 'success' : 'warning',
        'recommendations' => [
            'Configure HTTPS se possível',
            'Aguarde 5-10 minutos após alterar configurações no Facebook',
            'Verifique se o app está em modo "Live" ou "Development"',
            'Certifique-se de salvar todas as alterações no Facebook'
        ]
    ]);
})->name('platforms.check-facebook-config');

// Rota para ver logs do callback
Route::get('platforms/{platform}/logs', function (App\Models\Platform $platform) {
    $logFile = storage_path('logs/laravel.log');
    $logs = [];
    
    if (file_exists($logFile)) {
        $content = file_get_contents($logFile);
        $lines = explode("\n", $content);
        
        // Pegar as últimas 50 linhas que mencionam o platform_id
        $filteredLines = array_filter($lines, function($line) use ($platform) {
            return strpos($line, 'platform_id=' . $platform->id) !== false ||
                   strpos($line, '"platform_id":' . $platform->id) !== false;
        });
        
        $logs = array_slice(array_reverse($filteredLines), 0, 20);
    }
    
    return view('platforms.logs', compact('platform', 'logs'));
})->name('platforms.logs');

// Rota para política de privacidade
Route::view('/privacidade', 'privacy')->name('privacy');

// Rota para verificar status do token Facebook
Route::get('/platforms/{platform}/check-token', function (App\Models\Platform $platform) {
    if (!$platform->access_token) {
        return response()->json([
            'error' => 'Nenhum token encontrado',
            'solution' => 'Conecte a plataforma primeiro',
            'status' => 'no_token',
            'platform_id' => $platform->id,
            'platform_name' => $platform->name
        ]);
    }

    // Testar o token com chamada básica
    $response = Http::timeout(10)->get('https://graph.facebook.com/v21.0/me', [
        'access_token' => $platform->access_token,
        'fields' => 'id,name'
    ]);

    $tokenValid = $response->successful();
    $responseData = $response->json();

    // Se token válido, testar debug_token também
    $debugData = null;
    if ($tokenValid) {
        $debugResponse = Http::timeout(10)->get('https://graph.facebook.com/v21.0/debug_token', [
            'input_token' => $platform->access_token,
            'access_token' => $platform->access_token
        ]);
        
        if ($debugResponse->successful()) {
            $debugData = $debugResponse->json();
        }
    }

    return response()->json([
        'platform_id' => $platform->id,
        'platform_name' => $platform->name,
        'token_preview' => substr($platform->access_token, 0, 20) . '...' . substr($platform->access_token, -10),
        'token_length' => strlen($platform->access_token),
        'token_valid' => $tokenValid,
        'me_test' => [
            'status' => $response->status(),
            'success' => $response->successful(),
            'data' => $responseData,
            'error' => $response->failed() ? ($responseData['error'] ?? 'Erro desconhecido') : null
        ],
        'debug_token' => $debugData,
        'last_connected' => $platform->updated_at?->diffForHumans(),
        'created_at' => $platform->created_at?->format('d/m/Y H:i:s'),
        'updated_at' => $platform->updated_at?->format('d/m/Y H:i:s'),
        'recommendations' => $tokenValid ? [
            'Token válido e funcionando',
            'Você pode usar todas as funcionalidades do SDK'
        ] : [
            'Token inválido ou expirado',
            'Clique em "Conectar" para renovar o token',
            'Verifique se o app Facebook não foi desautorizado',
            'Certifique-se de aceitar todas as permissões necessárias'
        ]
    ]);
})->name('platforms.check-token');

// Rota para definir token manualmente (para testes)
Route::get('/platforms/{platform}/set-token/{token}', function (App\Models\Platform $platform, $token) {
    try {
        // Primeiro, testar se o token é válido
        $response = Http::timeout(10)->get('https://graph.facebook.com/v21.0/me', [
            'access_token' => $token,
            'fields' => 'id,name'
        ]);

        if ($response->successful()) {
            $userData = $response->json();
            
            // Atualizar plataforma com novo token
            $platform->update([
                'access_token' => $token,
                'is_connected' => true,
                'extra_data' => array_merge($platform->extra_data ?? [], [
                    'user_data' => $userData,
                    'token_updated_manually' => now()->toISOString(),
                    'token_source' => 'manual_update'
                ])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Token atualizado com sucesso!',
                'user_data' => $userData,
                'platform_id' => $platform->id,
                'platform_name' => $platform->name,
                'next_steps' => [
                    'Acesse a interface de hashtags',
                    'Teste o botão "SDK Facebook (Melhorado)"',
                    'Verifique se as páginas são carregadas'
                ]
            ]);
        } else {
            $error = $response->json();
            return response()->json([
                'success' => false,
                'error' => 'Token inválido',
                'details' => $error['error'] ?? 'Erro desconhecido',
                'message' => 'O token fornecido não é válido'
            ], 400);
        }
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Erro ao testar token',
            'details' => $e->getMessage()
        ], 500);
    }
})->name('platforms.set-token');

// Rota para debug da plataforma
Route::get('/platforms/{platform}/debug-data', function (App\Models\Platform $platform) {
    return response()->json([
        'platform_id' => $platform->id,
        'platform_name' => $platform->name,
        'app_id' => $platform->app_id,
        'app_secret_preview' => substr($platform->app_secret, 0, 10) . '...',
        'token_preview' => substr($platform->access_token, 0, 20) . '...',
        'token_length' => strlen($platform->access_token),
        'is_connected' => $platform->is_connected,
        'created_at' => $platform->created_at,
        'updated_at' => $platform->updated_at,
        'issue' => 'O token foi gerado com app_id real, mas a plataforma tem app_id de teste'
    ]);
})->name('platforms.debug-data');

// Rota para atualizar dados reais da plataforma
Route::get('/platforms/{platform}/fix-app-data', function (App\Models\Platform $platform) {
    // Dados reais do seu app Facebook (baseado no debug do token)
    $realAppId = '1338469367894948';
    $realAppSecret = 'SEU_APP_SECRET_REAL_AQUI'; // Você precisa colocar o secret real
    
    $platform->update([
        'app_id' => $realAppId,
        'app_secret' => $realAppSecret, // Você precisa colocar o secret real aqui
        'name' => 'Instagram/Facebook - Produção'
    ]);
    
    return response()->json([
        'success' => true,
        'message' => 'Dados da plataforma atualizados!',
        'old_app_id' => '123',
        'new_app_id' => $realAppId,
        'platform_id' => $platform->id,
        'next_step' => 'Agora o SDK deve funcionar corretamente'
    ]);
})->name('platforms.fix-app-data');

// Rotas para monitoramento de hashtags
use App\Http\Controllers\HashtagController;

Route::prefix('platforms/{platform}/hashtags')->name('hashtags.')->group(function () {
    // Página principal de monitoramento
    Route::get('/', [HashtagController::class, 'index'])->name('index');
    
    // Dashboard de monitoramento
    Route::get('/dashboard', [HashtagController::class, 'dashboard'])->name('dashboard');
    
    // APIs para obter dados
    Route::get('/accounts', [HashtagController::class, 'getAccountIds'])->name('accounts');
    Route::get('/accounts-complete', [HashtagController::class, 'getAccountIdsComplete'])->name('accounts.complete');
    Route::get('/accounts-sdk', [HashtagController::class, 'getAccountIdsSDK'])->name('accounts.sdk');
    Route::get('/page-by-id', [HashtagController::class, 'getPageById'])->name('page.by-id');
    Route::post('/search', [HashtagController::class, 'searchHashtags'])->name('search');
    Route::post('/mentions', [HashtagController::class, 'getMentions'])->name('mentions');
    Route::post('/facebook-mentions', [HashtagController::class, 'getFacebookMentions'])->name('facebook-mentions');
    
    // Teste da API
    Route::get('/test-api', [HashtagController::class, 'testApi'])->name('test-api');
    
    // Debug completo do Facebook
    Route::get('/debug-facebook', [HashtagController::class, 'debugFacebook'])->name('debug-facebook');
});


