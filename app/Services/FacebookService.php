<?php

namespace App\Services;

use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Illuminate\Support\Facades\Log;
use Exception;

class FacebookService
{
    private $fb;
    private $accessToken;

    public function __construct($accessToken = null, $appId = null, $appSecret = null)
    {
        $this->accessToken = $accessToken;
        
        // Obter credenciais com fallback
        $finalAppId = $appId ?: config('services.facebook.app_id', env('FACEBOOK_APP_ID'));
        $finalAppSecret = $appSecret ?: config('services.facebook.app_secret', env('FACEBOOK_APP_SECRET'));
        
        // Validar se as credenciais estão presentes
        if (!$finalAppId || !$finalAppSecret) {
            Log::error('FacebookService - Credenciais não configuradas', [
                'app_id_present' => !empty($finalAppId),
                'app_secret_present' => !empty($finalAppSecret),
                'config_app_id' => config('services.facebook.app_id'),
                'env_app_id' => env('FACEBOOK_APP_ID')
            ]);
            throw new Exception('Facebook App ID e App Secret devem ser configurados no .env');
        }
        
        Log::info('FacebookService - Inicializando SDK', [
            'app_id' => substr($finalAppId, 0, 10) . '...',
            'app_secret_present' => !empty($finalAppSecret)
        ]);
        
        try {
            $this->fb = new Facebook([
                'app_id' => $finalAppId,
                'app_secret' => $finalAppSecret,
                'default_graph_version' => 'v21.0',
            ]);
        } catch (Exception $e) {
            Log::error('FacebookService - Erro ao inicializar SDK', [
                'error' => $e->getMessage(),
                'exception_class' => get_class($e)
            ]);
            throw new Exception('Erro ao inicializar Facebook SDK: ' . $e->getMessage());
        }
    }

    /**
     * Obter informações do usuário atual
     */
    public function getMe($fields = 'id,name')
    {
        try {
            $response = $this->fb->get("/me?fields={$fields}", $this->accessToken);
            return $response->getDecodedBody();
        } catch (Exception $e) {
            Log::error('Facebook API Error - getMe: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obter permissões do usuário
     */
    public function getPermissions()
    {
        try {
            $response = $this->fb->get('/me/permissions', $this->accessToken);
            return $response->getDecodedBody();
        } catch (Exception $e) {
            Log::error('Facebook API Error - getPermissions: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obter páginas do usuário
     */
    public function getPages($fields = 'id,name,access_token,tasks')
    {
        try {
            $response = $this->fb->get("/me/accounts?fields={$fields}", $this->accessToken);
            return $response->getDecodedBody();
        } catch (Exception $e) {
            Log::error('Facebook API Error - getPages: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obter Business Managers do usuário
     */
    public function getBusinesses($fields = 'id,name')
    {
        try {
            $response = $this->fb->get("/me/businesses?fields={$fields}", $this->accessToken);
            return $response->getDecodedBody();
        } catch (Exception $e) {
            Log::error('Facebook API Error - getBusinesses: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obter páginas de um Business Manager
     */
    public function getBusinessPages($businessId, $fields = 'id,name,access_token,tasks')
    {
        try {
            $response = $this->fb->get("/{$businessId}/client_pages?fields={$fields}", $this->accessToken);
            return $response->getDecodedBody();
        } catch (Exception $e) {
            Log::error("Facebook API Error - getBusinessPages({$businessId}): " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obter Instagram Business Account de uma página
     */
    public function getInstagramBusiness($pageId)
    {
        try {
            $response = $this->fb->get("/{$pageId}?fields=instagram_business_account", $this->accessToken);
            return $response->getDecodedBody();
        } catch (Exception $e) {
            Log::error("Facebook API Error - getInstagramBusiness({$pageId}): " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Buscar hashtag no Instagram
     */
    public function searchInstagramHashtag($hashtag, $userId)
    {
        try {
            $response = $this->fb->get("/ig_hashtag_search?user_id={$userId}&q={$hashtag}", $this->accessToken);
            return $response->getDecodedBody();
        } catch (Exception $e) {
            Log::error("Facebook API Error - searchInstagramHashtag({$hashtag}): " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obter posts recentes de uma hashtag
     */
    public function getHashtagRecentMedia($hashtagId, $userId, $fields = 'id,caption,media_type,media_url,permalink,timestamp,username', $limit = 25)
    {
        try {
            $response = $this->fb->get("/{$hashtagId}/recent_media?user_id={$userId}&fields={$fields}&limit={$limit}", $this->accessToken);
            return $response->getDecodedBody();
        } catch (Exception $e) {
            Log::error("Facebook API Error - getHashtagRecentMedia({$hashtagId}): " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obter menções do Instagram
     */
    public function getInstagramMentions($userId, $fields = 'id,username,caption,media_type,media_url,permalink,timestamp', $limit = 25)
    {
        try {
            $response = $this->fb->get("/{$userId}/mentions?fields={$fields}&limit={$limit}", $this->accessToken);
            return $response->getDecodedBody();
        } catch (Exception $e) {
            Log::error("Facebook API Error - getInstagramMentions({$userId}): " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obter posts que mencionam uma página do Facebook
     */
    public function getFacebookTagged($pageId, $fields = 'id,from,message,created_time,permalink_url', $limit = 25)
    {
        try {
            $response = $this->fb->get("/{$pageId}/tagged?fields={$fields}&limit={$limit}", $this->accessToken);
            return $response->getDecodedBody();
        } catch (Exception $e) {
            Log::error("Facebook API Error - getFacebookTagged({$pageId}): " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Debug de token
     */
    public function debugToken($token = null)
    {
        try {
            $tokenToDebug = $token ?: $this->accessToken;
            $response = $this->fb->get("/debug_token?input_token={$tokenToDebug}", $this->accessToken);
            return $response->getDecodedBody();
        } catch (Exception $e) {
            Log::error('Facebook API Error - debugToken: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Método para busca completa (páginas pessoais + business)
     */
    public function getAllPagesComplete()
    {
        $result = [
            'pages' => [],
            'debug' => [
                'personal_pages' => null,
                'businesses' => null,
                'business_pages' => []
            ]
        ];

        try {
            // 1. Buscar páginas pessoais
            $personalPages = $this->getPages();
            $result['debug']['personal_pages'] = $personalPages;
            
            if (isset($personalPages['data'])) {
                foreach ($personalPages['data'] as $page) {
                    $page['source'] = 'personal';
                    $result['pages'][] = $page;
                }
            }

            // 2. Buscar Business Managers
            $businesses = $this->getBusinesses();
            $result['debug']['businesses'] = $businesses;

            if (isset($businesses['data'])) {
                foreach ($businesses['data'] as $business) {
                    // 3. Buscar páginas de cada Business Manager
                    $businessPages = $this->getBusinessPages($business['id']);
                    $result['debug']['business_pages'][$business['id']] = $businessPages;

                    if (isset($businessPages['data'])) {
                        foreach ($businessPages['data'] as $page) {
                            $page['source'] = 'business';
                            $page['business_name'] = $business['name'];
                            $page['business_id'] = $business['id'];
                            $result['pages'][] = $page;
                        }
                    }
                }
            }

            // 4. Remover duplicatas por ID
            $uniquePages = [];
            $pageIds = [];
            foreach ($result['pages'] as $page) {
                if (!in_array($page['id'], $pageIds)) {
                    $pageIds[] = $page['id'];
                    $uniquePages[] = $page;
                }
            }
            $result['pages'] = $uniquePages;

            // 5. Buscar Instagram Business para cada página
            foreach ($result['pages'] as $index => $page) {
                try {
                    $instagram = $this->getInstagramBusiness($page['id']);
                    if (isset($instagram['instagram_business_account'])) {
                        $result['pages'][$index]['instagram_business_account'] = $instagram['instagram_business_account'];
                    }
                } catch (Exception $e) {
                    // Continuar mesmo se não conseguir buscar Instagram para uma página específica
                    Log::warning("Não foi possível buscar Instagram para página {$page['id']}: " . $e->getMessage());
                }
            }

            return $result;

        } catch (Exception $e) {
            Log::error('Facebook API Error - getAllPagesComplete: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verificar se o token é válido
     */
    public function isTokenValid()
    {
        try {
            if (!$this->accessToken) {
                Log::warning('FacebookService - isTokenValid: Token não fornecido');
                return false;
            }
            
            $result = $this->getMe('id');
            Log::info('FacebookService - isTokenValid SUCCESS', [
                'result' => $result,
                'token_preview' => substr($this->accessToken, 0, 20) . '...'
            ]);
            return true;
        } catch (FacebookResponseException $e) {
            Log::error('FacebookService - isTokenValid ResponseException', [
                'error' => $e->getMessage(),
                'token_preview' => substr($this->accessToken, 0, 20) . '...',
                'response_code' => method_exists($e, 'getHttpStatusCode') ? $e->getHttpStatusCode() : 'unknown',
                'response_data' => method_exists($e, 'getResponseData') ? $e->getResponseData() : 'unavailable'
            ]);
            return false;
        } catch (FacebookSDKException $e) {
            Log::error('FacebookService - isTokenValid SDKException', [
                'error' => $e->getMessage(),
                'token_preview' => substr($this->accessToken, 0, 20) . '...',
                'exception_class' => get_class($e)
            ]);
            return false;
        } catch (Exception $e) {
            Log::error('FacebookService - isTokenValid ERROR', [
                'error' => $e->getMessage(),
                'token_preview' => substr($this->accessToken, 0, 20) . '...',
                'exception_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return false;
        }
    }

    /**
     * Obter URL de login do Facebook
     */
    public function getLoginUrl($permissions = ['pages_show_list', 'pages_read_engagement', 'instagram_basic'], $redirectUrl = null)
    {
        $helper = $this->fb->getRedirectLoginHelper();
        
        return $helper->getLoginUrl($redirectUrl, $permissions);
    }

    /**
     * Obter token de acesso do código de autorização
     */
    public function getAccessTokenFromCode($code, $redirectUrl = null)
    {
        try {
            $helper = $this->fb->getRedirectLoginHelper();
            $accessToken = $helper->getAccessToken($redirectUrl);
            
            return $accessToken ? $accessToken->getValue() : null;
        } catch (Exception $e) {
            Log::error('Facebook API Error - getAccessTokenFromCode: ' . $e->getMessage());
            throw $e;
        }
    }
}
