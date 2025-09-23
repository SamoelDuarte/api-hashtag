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
            return response()->json(['error' => 'Plataforma não conectada'], 400);
        }

        try {
            // 1. Obter páginas do Facebook
            $pagesResponse = Http::get('https://graph.facebook.com/v21.0/me/accounts', [
                'access_token' => $platform->access_token
            ]);

            if (!$pagesResponse->successful()) {
                return response()->json(['error' => 'Erro ao obter páginas do Facebook'], 400);
            }

            $pages = $pagesResponse->json()['data'] ?? [];
            $accountData = ['pages' => $pages];

            // 2. Para cada página, verificar se tem Instagram Business vinculado
            foreach ($pages as $page) {
                $instagramResponse = Http::get("https://graph.facebook.com/v21.0/{$page['id']}", [
                    'fields' => 'instagram_business_account',
                    'access_token' => $platform->access_token
                ]);

                if ($instagramResponse->successful()) {
                    $instagram = $instagramResponse->json();
                    if (isset($instagram['instagram_business_account'])) {
                        $page['instagram_business_account'] = $instagram['instagram_business_account'];
                    }
                }
            }

            $accountData['pages'] = $pages;

            // Salvar dados extras na plataforma
            $platform->update([
                'extra_data' => array_merge($platform->extra_data ?? [], [
                    'account_data' => $accountData,
                    'last_sync' => now()->toISOString()
                ])
            ]);

            return response()->json([
                'success' => true,
                'data' => $accountData
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao obter IDs da conta', [
                'platform_id' => $platform->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
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
}