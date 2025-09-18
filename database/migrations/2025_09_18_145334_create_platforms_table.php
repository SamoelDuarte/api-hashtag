<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('platforms', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nome da plataforma (Facebook/Instagram, YouTube, TikTok)
            $table->string('type'); // Tipo: facebook, instagram, youtube, tiktok
            $table->string('app_id')->nullable(); // ID do aplicativo
            $table->string('app_secret')->nullable(); // Secret do aplicativo
            $table->text('redirect_uri')->nullable(); // URL de callback
            $table->text('access_token')->nullable(); // Token de acesso após OAuth
            $table->text('refresh_token')->nullable(); // Token de refresh
            $table->timestamp('token_expires_at')->nullable(); // Quando o token expira
            $table->boolean('is_connected')->default(false); // Se está conectado
            $table->text('scopes')->nullable(); // Permissões OAuth
            $table->json('extra_data')->nullable(); // Dados extras específicos da plataforma
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platforms');
    }
};
