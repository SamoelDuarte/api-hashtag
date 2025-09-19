<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlatformController;

// Redirecionar para plataformas
Route::get('/', function () {
    return redirect()->route('platforms.index');
});

// Rotas do CRUD de plataformas
Route::resource('platforms', PlatformController::class);

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
