<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PlatformController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $platforms = Platform::all();
        return view('platforms.index', compact('platforms'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $types = Platform::getTypes();
        return view('platforms.create', compact('types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:facebook,youtube,tiktok',
            'app_id' => 'required|string',
            'app_secret' => 'required|string',
            'redirect_uri' => 'required|url'
        ]);

        $platform = Platform::create($request->all());

        // Atualiza a URL de callback com o ID real da plataforma
        $callbackUrl = str_replace('PLATFORM_ID', $platform->id, $platform->redirect_uri);
        $platform->update(['redirect_uri' => $callbackUrl]);

        return redirect()->route('platforms.show', $platform)
            ->with('success', 'Plataforma criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Platform $platform)
    {
        return view('platforms.show', compact('platform'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Platform $platform)
    {
        $types = Platform::getTypes();
        return view('platforms.edit', compact('platform', 'types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Platform $platform)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:facebook,youtube,tiktok',
            'app_id' => 'required|string',
            'app_secret' => 'nullable|string',
            'redirect_uri' => 'required|url'
        ]);

        $data = $request->except(['app_secret']);
        
        // Só atualiza o app_secret se foi fornecido
        if ($request->filled('app_secret')) {
            $data['app_secret'] = $request->app_secret;
        }

        $platform->update($data);

        return redirect()->route('platforms.show', $platform)
            ->with('success', 'Plataforma atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Platform $platform)
    {
        $platform->delete();

        return redirect()->route('platforms.index')
            ->with('success', 'Plataforma removida com sucesso!');
    }

    /**
     * Inicia o processo de autenticação OAuth
     */
    public function connect(Platform $platform)
    {
        if ($platform->type === 'facebook') {
            $oauthUrl = $platform->getOAuthUrl();
            return redirect($oauthUrl);
        }

        return redirect()->route('platforms.show', $platform)
            ->with('error', 'OAuth não implementado para esta plataforma ainda.');
    }

    /**
     * Callback do OAuth
     */
    public function callback(Request $request, Platform $platform)
    {
        if ($request->has('error')) {
            return redirect()->route('platforms.show', $platform)
                ->with('error', 'Autorização negada: ' . $request->get('error_description'));
        }

        if ($platform->type === 'facebook' && $request->has('code')) {
            try {
                // Trocar código por access token
                $response = Http::get('https://graph.facebook.com/v21.0/oauth/access_token', [
                    'client_id' => $platform->app_id,
                    'client_secret' => $platform->app_secret,
                    'redirect_uri' => $platform->redirect_uri,
                    'code' => $request->get('code')
                ]);

                $data = $response->json();

                if (isset($data['access_token'])) {
                    $platform->update([
                        'access_token' => $data['access_token'],
                        'is_connected' => true,
                        'token_expires_at' => isset($data['expires_in']) 
                            ? now()->addSeconds($data['expires_in']) 
                            : null
                    ]);

                    return redirect()->route('platforms.show', $platform)
                        ->with('success', 'Conectado com sucesso ao Facebook/Instagram!');
                } else {
                    return redirect()->route('platforms.show', $platform)
                        ->with('error', 'Erro ao obter token de acesso: ' . ($data['error']['message'] ?? 'Erro desconhecido'));
                }
            } catch (\Exception $e) {
                return redirect()->route('platforms.show', $platform)
                    ->with('error', 'Erro na autenticação: ' . $e->getMessage());
            }
        }

        return redirect()->route('platforms.show', $platform)
            ->with('error', 'Callback inválido.');
    }

    /**
     * Desconecta a plataforma
     */
    public function disconnect(Platform $platform)
    {
        $platform->update([
            'access_token' => null,
            'refresh_token' => null,
            'token_expires_at' => null,
            'is_connected' => false
        ]);

        return redirect()->route('platforms.show', $platform)
            ->with('success', 'Desconectado com sucesso!');
    }
}
