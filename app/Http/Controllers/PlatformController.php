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
        // Log para debug - salvar todos os parâmetros recebidos
        \Log::info('OAuth Callback recebido', [
            'platform_id' => $platform->id,
            'platform_type' => $platform->type,
            'request_params' => $request->all(),
            'request_url' => $request->fullUrl(),
            'user_agent' => $request->userAgent(),
        ]);

        // Verificar se houve erro na autorização
        if ($request->has('error') || $request->has('error_code') || $request->has('error_message')) {
            $error = $request->get('error');
            $errorCode = $request->get('error_code');
            $errorMessage = $request->get('error_message');
            $errorDescription = $request->get('error_description');
            
            \Log::warning('OAuth Error recebido', [
                'error' => $error,
                'error_code' => $errorCode,
                'error_message' => $errorMessage,
                'error_description' => $errorDescription,
                'platform_id' => $platform->id,
                'all_params' => $request->all(),
            ]);
            
            // Erro específico de domínio (código 1349048)
            if ($errorCode == '1349048' || strpos($errorMessage, 'domínio') !== false || strpos($errorMessage, 'domain') !== false) {
                return redirect()->route('platforms.domain-error', $platform)
                    ->with('error', 'Erro de domínio do Facebook: ' . $errorMessage)
                    ->with('error_details', [
                        'error_code' => $errorCode,
                        'error_message' => $errorMessage,
                        'solution' => 'domain_config'
                    ]);
            }
            
            // Erro de URI inválida
            if ($error === 'redirect_uri_mismatch' || 
                strpos($errorDescription, 'redirect_uri') !== false ||
                strpos($errorDescription, 'Invalid') !== false ||
                strpos($errorMessage, 'URI de redirecionamento inválido') !== false) {
                
                return redirect()->route('platforms.uri-invalid', $platform)
                    ->with('error', 'URI rejeitada pelo Facebook: ' . ($errorDescription ?: $errorMessage));
            }
            
            return redirect()->route('platforms.show', $platform)
                ->with('error', 'Erro de autorização: ' . ($errorDescription ?: $errorMessage ?: $error));
        }

        // Processar callback do Facebook
        if ($platform->type === 'facebook') {
            // Verificar se recebeu o código de autorização
            if (!$request->has('code')) {
                \Log::error('Callback sem código de autorização', [
                    'platform_id' => $platform->id,
                    'request_params' => $request->all(),
                ]);
                
                return redirect()->route('platforms.show', $platform)
                    ->with('error', 'Código de autorização não recebido. Parâmetros: ' . json_encode($request->all()));
            }

            try {
                // Log da tentativa de troca do código por token
                \Log::info('Tentando trocar código por token', [
                    'platform_id' => $platform->id,
                    'code' => substr($request->get('code'), 0, 20) . '...',
                    'redirect_uri' => $platform->redirect_uri,
                ]);

                // Trocar código por access token
                $response = Http::get('https://graph.facebook.com/v21.0/oauth/access_token', [
                    'client_id' => $platform->app_id,
                    'client_secret' => $platform->app_secret,
                    'redirect_uri' => $platform->redirect_uri,
                    'code' => $request->get('code')
                ]);

                $data = $response->json();
                
                \Log::info('Resposta do Facebook', [
                    'platform_id' => $platform->id,
                    'status_code' => $response->status(),
                    'response_data' => $data,
                ]);

                if (isset($data['access_token'])) {
                    $platform->update([
                        'access_token' => $data['access_token'],
                        'is_connected' => true,
                        'token_expires_at' => isset($data['expires_in']) 
                            ? now()->addSeconds($data['expires_in']) 
                            : null
                    ]);

                    \Log::info('Token salvo com sucesso', [
                        'platform_id' => $platform->id,
                        'expires_at' => $platform->token_expires_at,
                    ]);

                    return redirect()->route('platforms.show', $platform)
                        ->with('success', 'Conectado com sucesso ao Facebook/Instagram!');
                } else {
                    $errorMessage = $data['error']['message'] ?? 'Erro desconhecido';
                    \Log::error('Erro ao obter token', [
                        'platform_id' => $platform->id,
                        'error_data' => $data,
                    ]);
                    
                    return redirect()->route('platforms.show', $platform)
                        ->with('error', 'Erro ao obter token de acesso: ' . $errorMessage . '. Debug: ' . json_encode($data));
                }
            } catch (\Exception $e) {
                \Log::error('Exceção no callback', [
                    'platform_id' => $platform->id,
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                
                return redirect()->route('platforms.show', $platform)
                    ->with('error', 'Erro na autenticação: ' . $e->getMessage());
            }
        }

        // Se chegou até aqui, o callback é inválido
        \Log::warning('Callback inválido', [
            'platform_id' => $platform->id,
            'platform_type' => $platform->type,
            'request_params' => $request->all(),
        ]);

        return redirect()->route('platforms.show', $platform)
            ->with('error', 'Callback inválido. Tipo: ' . $platform->type . ', Parâmetros: ' . json_encode($request->all()));
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
