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
