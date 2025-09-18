<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Platform extends Model
{
    protected $fillable = [
        'name',
        'type',
        'app_id',
        'app_secret',
        'redirect_uri',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'is_connected',
        'scopes',
        'extra_data'
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'is_connected' => 'boolean',
        'extra_data' => 'array'
    ];

    // Verifica se o token ainda é válido
    public function isTokenValid()
    {
        if (!$this->access_token) {
            return false;
        }

        if ($this->token_expires_at && $this->token_expires_at->isPast()) {
            return false;
        }

        return true;
    }

    // Gera a URL de autorização OAuth para Facebook/Instagram
    public function getOAuthUrl()
    {
        if ($this->type === 'facebook') {
            $params = [
                'client_id' => $this->app_id,
                'redirect_uri' => $this->redirect_uri,
                'scope' => 'pages_show_list,instagram_basic,instagram_manage_comments,pages_read_engagement',
                'response_type' => 'code'
            ];

            return 'https://www.facebook.com/v21.0/dialog/oauth?' . http_build_query($params);
        }

        return null;
    }

    // Tipos de plataforma disponíveis
    public static function getTypes()
    {
        return [
            'facebook' => 'Facebook/Instagram',
            'youtube' => 'YouTube',
            'tiktok' => 'TikTok'
        ];
    }
}
