<?php

// Teste direto do Facebook SDK para debug
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/app.php';

echo "🔍 Teste do Facebook SDK\n";
echo "========================\n";

try {
    // Verificar se a classe existe
    echo "1. Verificando se a classe Facebook existe... ";
    if (class_exists('Facebook\Facebook')) {
        echo "✅ OK\n";
    } else {
        echo "❌ ERRO: Classe Facebook não encontrada\n";
        exit;
    }

    // Tentar criar instância básica
    echo "2. Criando instância do Facebook SDK... ";
    $fb = new \Facebook\Facebook([
        'app_id' => '123',  // Valores falsos para teste
        'app_secret' => '456',
        'default_graph_version' => 'v21.0',
    ]);
    echo "✅ OK\n";

    // Verificar se FacebookService pode ser carregada
    echo "3. Verificando FacebookService... ";
    if (class_exists('App\Services\FacebookService')) {
        echo "✅ OK\n";
        
        // Tentar instanciar
        echo "4. Instanciando FacebookService... ";
        $service = new \App\Services\FacebookService('fake_token', '123', '456');
        echo "✅ OK\n";
        
    } else {
        echo "❌ ERRO: FacebookService não encontrada\n";
        exit;
    }

    // Verificar se Platform model existe
    echo "5. Verificando Platform model... ";
    if (class_exists('App\Models\Platform')) {
        echo "✅ OK\n";
        
        // Tentar buscar plataforma ID 1
        echo "6. Buscando Platform ID 1... ";
        $platform = \App\Models\Platform::find(1);
        if ($platform) {
            echo "✅ Encontrada: {$platform->name}\n";
            echo "   - Conectada: " . ($platform->is_connected ? 'SIM' : 'NÃO') . "\n";
            echo "   - Tem token: " . ($platform->access_token ? 'SIM' : 'NÃO') . "\n";
            echo "   - Token preview: " . ($platform->access_token ? substr($platform->access_token, 0, 20) . '...' : 'N/A') . "\n";
        } else {
            echo "❌ Platform ID 1 não encontrada\n";
            echo "   Criando plataforma de teste...\n";
            
            $platform = \App\Models\Platform::create([
                'name' => 'Teste SDK',
                'type' => 'facebook',
                'app_id' => '123',
                'app_secret' => '456',
                'access_token' => 'fake_token_for_testing',
                'redirect_uri' => 'https://hashtag.betasolucao.com.br/platforms/1/callback',
                'is_connected' => true
            ]);
            
            echo "   ✅ Plataforma criada com ID {$platform->id}\n";
        }
        
    } else {
        echo "❌ ERRO: Platform model não encontrada\n";
        exit;
    }

    echo "\n✅ RESULTADO: Todos os componentes básicos estão funcionando!\n";
    echo "O erro 500 provavelmente está ocorrendo na API call do Facebook.\n";
    echo "Verifique se o token de acesso é válido.\n";

} catch (\Exception $e) {
    echo "\n❌ ERRO ENCONTRADO:\n";
    echo "Tipo: " . get_class($e) . "\n";
    echo "Mensagem: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";