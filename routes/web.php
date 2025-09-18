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

// Rota para política de privacidade
Route::view('/privacidade', 'privacy')->name('privacy');
