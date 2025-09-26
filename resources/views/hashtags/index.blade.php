@extends('layouts.app')

@section('title', 'Monitoramento de Hashtags - ' . $platform->name)

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="bi bi-hash"></i>
                        Monitoramento de Hashtags
                    </h1>
                    <p class="text-muted mb-0">{{ $platform->name }} - {{ ucfirst($platform->type) }}</p>
                </div>
                <div>
                    <a href="{{ route('platforms.show', $platform) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                    <a href="{{ route('hashtags.dashboard', $platform) }}" class="btn btn-primary">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </div>
            </div>

            <!-- Status da Conexão -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                @if($platform->is_connected)
                                    <div class="text-success me-3">
                                        <i class="bi bi-check-circle-fill fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Plataforma Conectada</h6>
                                        <small class="text-muted">Última sincronização: {{ $platform->updated_at->diffForHumans() }}</small>
                                    </div>
                                @else
                                    <div class="text-danger me-3">
                                        <i class="bi bi-x-circle-fill fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Plataforma Desconectada</h6>
                                        <small class="text-muted">Conecte a plataforma para monitorar hashtags</small>
                                    </div>
                                @endif
                                <div class="ms-auto">
                                    <div class="btn-group">
                                        <button class="btn btn-outline-primary btn-sm" onclick="testConnection()">
                                            <i class="bi bi-arrow-clockwise"></i> Testar Conexão
                                        </button>
                                        <button class="btn btn-outline-info btn-sm" onclick="testFacebookAPI()">
                                            <i class="bi bi-facebook"></i> Testar Facebook API
                                        </button>
                                        <button class="btn btn-outline-success btn-sm" onclick="checkToken()">
                                            <i class="bi bi-shield-check"></i> Verificar Token
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm" onclick="debugFacebookComplete()">
                                            <i class="bi bi-bug"></i> Debug Completo
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção de Configuração -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-gear"></i>
                                Configuração de Contas
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="loading-accounts" class="text-center py-4" style="display: none;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Carregando...</span>
                                </div>
                                <p class="mt-2 text-muted">Carregando contas conectadas...</p>
                            </div>

                            <div id="accounts-container">
                                <div class="text-center py-4">
                                    <i class="bi bi-info-circle text-muted fs-4"></i>
                                    <p class="text-muted mt-2">Busque suas contas e páginas do Facebook/Instagram:</p>
                                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                                        <button class="btn btn-info" onclick="loadAccountsComplete()">
                                            <i class="bi bi-building"></i> Buscar Páginas + Business
                                        </button>
                                    </div>
                                            <i class="bi bi-question-circle"></i> Ajuda
                                        </button>
                                    </div>
                                    <div id="accounts-help" class="mt-3" style="display: none;">
                                        <div class="alert alert-info">
                                            <strong>Diferença entre os métodos:</strong><br>
                                            <strong>Carregar Contas (Pessoais):</strong> Busca apenas páginas conectadas diretamente ao seu usuário<br>
                                            <strong>Buscar Páginas + Business:</strong> Busca por ID específico - ideal para páginas em Business Manager<br>
                                            <strong>SDK Facebook (Melhorado):</strong> Usa a biblioteca oficial do Facebook com melhor tratamento de erros<br>
                                            <strong>Buscar por ID da Página:</strong> Busca manual por ID - para páginas conectadas com Instagram (portfólio)<br>
                                            <small class="text-muted">Use <strong>Carregar Contas</strong> primeiro. Se sua página não aparecer, use <strong>Buscar por ID</strong> ou <strong>Business</strong> com o ID específico</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção de Monitoramento -->
            <div class="row">
                <!-- Busca de Hashtags -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-search"></i>
                                Buscar Hashtags (Instagram)
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="hashtag-form">
                                <div class="mb-3">
                                    <label class="form-label">Conta do Instagram</label>
                                    <select class="form-select" id="instagram-account" required disabled>
                                        <option value="">Carregue as contas primeiro</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Hashtag</label>
                                    <div class="input-group">
                                        <span class="input-group-text">#</span>
                                        <input type="text" class="form-control" id="hashtag-input" placeholder="minhahashtag" required disabled>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary" disabled id="search-hashtag-btn">
                                    <i class="bi bi-search"></i> Buscar Posts
                                </button>
                            </form>

                            <div id="hashtag-results" class="mt-4" style="display: none;">
                                <!-- Resultados aparecerão aqui -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Busca de Menções -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-at"></i>
                                Monitorar Menções
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary" onclick="loadInstagramMentions()" disabled id="instagram-mentions-btn">
                                    <i class="bi bi-instagram"></i> Menções no Instagram
                                </button>
                                <button class="btn btn-outline-primary" onclick="loadFacebookMentions()" disabled id="facebook-mentions-btn">
                                    <i class="bi bi-facebook"></i> Menções no Facebook
                                </button>
                            </div>

                            <div id="mentions-results" class="mt-4" style="display: none;">
                                <!-- Resultados aparecerão aqui -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Buscar Página por ID -->
<div class="modal fade" id="manualPageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-search"></i> Buscar Página por ID
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>Quando usar este método:</strong><br>
                    • Sua página está conectada com Instagram (portfólio)<br>
                    • A página não aparece nos outros métodos de busca<br>
                    • Você tem o ID específico da página do Facebook
                </div>
                
                <form id="manual-page-form">
                    <div class="mb-3">
                        <label for="page-id-input" class="form-label">ID da Página do Facebook</label>
                        <input type="text" 
                               class="form-control" 
                               id="page-id-input" 
                               placeholder="Digite o ID da página (ex: 123456789012345)"
                               required>
                        <div class="form-text">
                            <i class="bi bi-lightbulb"></i> 
                            Para encontrar o ID: vá na sua página do Facebook, clique em "Sobre" e procure por "ID da Página"
                        </div>
                    </div>
                </form>
                
                <div id="manual-search-loading" class="text-center" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Buscando...</span>
                    </div>
                    <p class="mt-2 text-muted">Buscando informações da página...</p>
                </div>
                
                <div id="manual-search-result" style="display: none;">
                    <!-- Resultado da busca aparecerá aqui -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="searchPageById()">
                    <i class="bi bi-search"></i> Buscar Página
                </button>
                <button type="button" class="btn btn-success" onclick="useManualPage()" id="use-manual-page-btn" style="display: none;">
                    <i class="bi bi-check-circle"></i> Usar Esta Página
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Detalhes do Post -->
<div class="modal fade" id="postModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="post-modal-body">
                <!-- Conteúdo do post será carregado aqui -->
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
let platformData = {
    id: {{ $platform->id }},
    type: '{{ $platform->type }}',
    accounts: null
};

// Testar conexão
function testConnection() {
    fetch(`/platforms/{{ $platform->id }}/hashtags/test-api`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Conexão ativa! API funcionando corretamente.', 'success');
            } else {
                showAlert('Erro na conexão: ' + data.error, 'danger');
            }
        })
        .catch(error => {
            showAlert('Erro ao testar conexão: ' + error, 'danger');
        });
}

// Testar Facebook API diretamente
function testFacebookAPI() {
    showAlert('Testando Facebook API...', 'info');
    
    // Fazer chamada direta para testar o token
    fetch(`https://graph.facebook.com/v21.0/me?access_token={{ $platform->access_token }}&fields=id,name`)
        .then(response => response.json())
        .then(data => {
            console.log('Facebook API Test:', data);
            
            if (data.error) {
                showAlert(`Erro no Facebook API: ${data.error.message}`, 'danger');
            } else {
                showAlert(`Facebook API OK! Usuário: ${data.name} (ID: ${data.id})`, 'success');
                
                // Agora testar pages
                testFacebookPages();
            }
        })
        .catch(error => {
            showAlert('Erro ao conectar com Facebook API: ' + error, 'danger');
            console.error('Facebook API Error:', error);
        });
}

// Testar especificamente o endpoint de páginas
function testFacebookPages() {
    fetch(`https://graph.facebook.com/v21.0/me/accounts?access_token={{ $platform->access_token }}&fields=id,name,tasks`)
        .then(response => response.json())
        .then(data => {
            console.log('Facebook Pages Test:', data);
            
            if (data.error) {
                showAlert(`Erro ao buscar páginas: ${data.error.message}`, 'danger');
            } else {
                const pages = data.data || [];
                if (pages.length === 0) {
                    showAlert('Facebook API funciona, mas não retornou páginas. Verifique permissões ou se o usuário gerencia páginas.', 'warning');
                } else {
                    showAlert(`Encontradas ${pages.length} páginas via Facebook API direta!`, 'success');
                }
            }
        })
        .catch(error => {
            showAlert('Erro ao testar páginas do Facebook: ' + error, 'danger');
            console.error('Facebook Pages Error:', error);
        });
}

// Debug completo do Facebook
function debugFacebookComplete() {
    showAlert('Executando debug completo do Facebook...', 'info');
    
    fetch(`/platforms/{{ $platform->id }}/hashtags/debug-facebook`)
        .then(response => response.json())
        .then(data => {
            console.log('Debug Completo:', data);
            
            if (data.success) {
                // Mostrar modal com resultados detalhados
                showDebugModal(data);
                
                // Mostrar resumo das recomendações
                const recommendations = data.recommendations || [];
                if (recommendations.length > 0) {
                    const firstRecommendation = recommendations[0];
                    showAlert(`Debug concluído. ${firstRecommendation}`, 
                             firstRecommendation.includes('Todos os testes passaram') ? 'success' : 'warning');
                }
            } else {
                showAlert(`Erro no debug: ${data.error}`, 'danger');
            }
        })
        .catch(error => {
            showAlert('Erro ao executar debug: ' + error, 'danger');
            console.error('Debug Error:', error);
        });
}

// Mostrar modal com resultados do debug
function showDebugModal(debugData) {
    // Criar modal dinamicamente
    const modalId = 'debugModal';
    const existingModal = document.getElementById(modalId);
    if (existingModal) {
        existingModal.remove();
    }
    
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = modalId;
    modal.innerHTML = `
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-bug"></i> Debug Completo do Facebook
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Informações da Plataforma</h6>
                            <pre class="bg-light p-2 rounded small">${JSON.stringify(debugData.platform_info, null, 2)}</pre>
                            
                            <h6 class="mt-3">Recomendações</h6>
                            <ul class="list-group">
                                ${debugData.recommendations.map(rec => `<li class="list-group-item">${rec}</li>`).join('')}
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Resultados dos Testes</h6>
                            <div class="accordion" id="testAccordion">
                                ${Object.entries(debugData.tests).map(([key, test], index) => `
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button ${index === 0 ? '' : 'collapsed'}" type="button" 
                                                    data-bs-toggle="collapse" data-bs-target="#test-${index}">
                                                ${key.replace('_', ' ').toUpperCase()} 
                                                <span class="badge ${test.success ? 'bg-success' : 'bg-danger'} ms-2">
                                                    ${test.success ? 'OK' : 'ERRO'}
                                                </span>
                                            </button>
                                        </h2>
                                        <div id="test-${index}" class="accordion-collapse collapse ${index === 0 ? 'show' : ''}" 
                                             data-bs-parent="#testAccordion">
                                            <div class="accordion-body">
                                                <small><strong>Status:</strong> ${test.status}</small>
                                                <pre class="bg-light p-2 rounded small mt-2">${JSON.stringify(test.data, null, 2)}</pre>
                                            </div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" onclick="copyDebugInfo()">
                        <i class="bi bi-clipboard"></i> Copiar Informações
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Salvar dados para cópia
    window.lastFullDebugInfo = debugData;
    
    // Mostrar modal
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
}

// Copiar informações de debug
function copyDebugInfo() {
    if (window.lastFullDebugInfo) {
        navigator.clipboard.writeText(JSON.stringify(window.lastFullDebugInfo, null, 2))
            .then(() => showAlert('Informações de debug copiadas!', 'success'))
            .catch(() => showAlert('Erro ao copiar informações', 'danger'));
    }
}

// Carregar contas (páginas e Instagram)
function loadAccounts() {
    const container = document.getElementById('accounts-container');
    const loading = document.getElementById('loading-accounts');
    
    container.style.display = 'none';
    loading.style.display = 'block';
    
    // Debug: mostrar qual URL está sendo chamada
    const platformId = {{ $platform->id }};
    const url = `/platforms/${platformId}/hashtags/accounts`;
    console.log('Platform ID:', platformId);
    console.log('Chamando URL:', window.location.origin + url);
    
    // VERIFICAÇÃO: Se o ID for inválido, mostrar erro
    if (!platformId || platformId === 'undefined') {
        console.error('ERRO: Platform ID é inválido:', platformId);
        loading.style.display = 'none';
        container.innerHTML = '<div class="alert alert-danger">Erro: ID da plataforma inválido</div>';
        container.style.display = 'block';
        return;
    }

    // *** DEBUG: Testar se alguma rota básica funciona ***
    console.log('🔍 Testando rotas básicas primeiro...');
    fetch('/test-laravel')
        .then(resp => resp.json())
        .then(data => {
            console.log('✅ /test-laravel funciona:', data);
            
            // Testar rota específica de plataformas
            return fetch(`/platforms/${platformId}`);
        })
        .then(resp => {
            console.log(`✅ /platforms/${platformId} status:`, resp.status);
            
            // Agora fazer a requisição real
            console.log('🎯 Fazendo requisição para accounts...');
            makeAccountsRequest();
        })
        .catch(err => {
            console.log('❌ Erro nos testes básicos:', err);
            makeAccountsRequest(); // Fazer mesmo assim
        });
}

function makeAccountsRequest() {
    const platformId = {{ $platform->id }};
    const url = `/platforms/${platformId}/hashtags/accounts`;
    console.log('✅ Usando rota original (Nginx corrigido):', url);
    
    const container = document.getElementById('accounts-container');
    const loading = document.getElementById('loading-accounts');
    
    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', [...response.headers.entries()]);
            
            // *** DEBUG: Mostrar o conteúdo HTML completo ***
            if (response.status === 404) {
                return response.text().then(htmlContent => {
                    console.log('=== CONTEÚDO HTML COMPLETO DO ERRO 404 ===');
                    console.log(htmlContent);
                    
                    // Procurar por pistas no HTML
                    if (htmlContent.includes('404')) {
                        console.log('✅ É realmente um erro 404');
                    }
                    if (htmlContent.includes('NotFoundHttpException')) {
                        console.log('✅ Laravel NotFoundHttpException');
                    }
                    if (htmlContent.includes('Route [') && htmlContent.includes('] not defined')) {
                        console.log('✅ Erro de rota não definida');
                    }
                    if (htmlContent.includes('nginx')) {
                        console.log('❌ Erro do Nginx, não do Laravel');
                    }
                    
                    throw new Error(`404 Error - HTML Response: ${htmlContent.substring(0, 500)}...`);
                });
            }
            
            // Verificar se a resposta é JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(textContent => {
                    console.log('=== RESPOSTA NÃO-JSON ===');
                    console.log('Content-Type:', contentType);
                    console.log('Conteúdo:', textContent);
                    throw new Error(`Resposta não é JSON. Content-Type: ${contentType}`);
                });
            }
            
            return response.json();
        })
        .then(data => {
            loading.style.display = 'none';
            container.style.display = 'block';
            
            // Log completo para debug
            console.log('Response completa da API:', data);
            
            if (data.success) {
                platformData.accounts = data.data;
                displayAccounts(data.data);
                enableControls();
                showAlert('Contas carregadas com sucesso!', 'success');
                
                // Mostrar debug info se disponível
                if (data.debug) {
                    console.log('Debug info:', data.debug);
                }
            } else {
                // Mostrar erro detalhado
                let errorHtml = `
                    <div class="alert alert-danger">
                        <h6><i class="bi bi-exclamation-triangle"></i> Erro ao carregar contas</h6>
                        <p><strong>Erro:</strong> ${data.error}</p>
                `;
                
                // Mostrar mensagem adicional se existir
                if (data.message) {
                    errorHtml += `<p><strong>Detalhes:</strong> ${data.message}</p>`;
                }
                
                // Mostrar sugestões se existirem
                if (data.suggestions && data.suggestions.length > 0) {
                    errorHtml += `
                        <hr>
                        <p><strong>Possíveis soluções:</strong></p>
                        <ul>
                    `;
                    data.suggestions.forEach(suggestion => {
                        errorHtml += `<li>${suggestion}</li>`;
                    });
                    errorHtml += `</ul>`;
                }
                
                // Botão para mostrar debug
                if (data.debug) {
                    errorHtml += `
                        <hr>
                        <button class="btn btn-outline-info btn-sm" onclick="showDebugInfo()">
                            <i class="bi bi-bug"></i> Mostrar Informações Técnicas
                        </button>
                        <div id="debug-info" style="display: none;" class="mt-3">
                            <h6>Informações de Debug:</h6>
                            <pre class="bg-light p-2 rounded small">${JSON.stringify(data.debug, null, 2)}</pre>
                        </div>
                    `;
                    
                    // Salvar debug info globalmente
                    window.lastDebugInfo = data.debug;
                }
                
                errorHtml += `</div>`;
                container.innerHTML = errorHtml;
                
                // Log do erro para análise
                console.error('Erro na API de contas:', data);
            }
        })
        .catch(error => {
            loading.style.display = 'none';
            container.style.display = 'block';
            
            console.error('Erro completo:', error);
            
            let errorMessage = error.message;
            if (error.message.includes('Resposta não é JSON')) {
                errorMessage += '<br><strong>Isso indica que o Laravel não está processando a rota corretamente.</strong>';
                errorMessage += '<br>Verifique se o servidor web está configurado para o Laravel.';
            }
            
            container.innerHTML = `
                <div class="alert alert-danger">
                    <h6><i class="bi bi-exclamation-triangle"></i> Erro de Conexão</h6>
                    <p><strong>Erro:</strong> ${errorMessage}</p>
                    <p><strong>URL chamada:</strong> ${window.location.origin}/platforms/{{ $platform->id }}/hashtags/accounts</p>
                    <hr>
                    <p><strong>Possíveis causas:</strong></p>
                    <ul>
                        <li>Laravel não está processando as rotas (verifique o .htaccess)</li>
                        <li>DocumentRoot do servidor não aponta para a pasta 'public'</li>
                        <li>Servidor web retornando página HTML em vez de JSON</li>
                    </ul>
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-info btn-sm" onclick="testLaravelConnection()">
                            <i class="bi bi-arrow-clockwise"></i> 1. Testar Laravel Básico
                        </button>
                        <button class="btn btn-outline-warning btn-sm" onclick="testAccountsDebug()">
                            <i class="bi bi-bug"></i> 2. Testar Rota Debug
                        </button>
                        <button class="btn btn-outline-primary btn-sm" onclick="testModelBinding()">
                            <i class="bi bi-database"></i> 3. Testar Model Binding
                        </button>
                        <button class="btn btn-outline-success btn-sm" onclick="testHashtagsRoute()">
                            <i class="bi bi-hash"></i> 4. Testar Rotas Hashtags
                        </button>
                    </div>
                </div>
            `;
            
            console.error('Erro de conexão:', error);
        });
}

// Exibir contas carregadas
function displayAccounts(accountData) {
    const container = document.getElementById('accounts-container');
    const pages = accountData.pages || [];
    
    let html = `
        <div class="row">
            <div class="col-12 mb-3">
                <h6><i class="bi bi-facebook"></i> Páginas do Facebook (${pages.length})</h6>
            </div>
    `;
    
    if (pages.length === 0) {
        html += `
            <div class="col-12">
                <div class="alert alert-warning">
                    <i class="bi bi-info-circle"></i>
                    Nenhuma página encontrada. Certifique-se de que sua conta tem páginas do Facebook.
                </div>
            </div>
        `;
    } else {
        pages.forEach(page => {
            const hasInstagram = page.instagram_business_account ? true : false;
            html += `
                <div class="col-md-6 mb-3">
                    <div class="card border-0 bg-light">
                        <div class="card-body p-3">
                            <h6 class="mb-1">${page.name}</h6>
                            <small class="text-muted">ID: ${page.id}</small>
                            ${hasInstagram ? `
                                <div class="mt-2">
                                    <span class="badge bg-success">
                                        <i class="bi bi-instagram"></i> Instagram Conectado
                                    </span>
                                    <br><small class="text-muted">ID: ${page.instagram_business_account.id}</small>
                                </div>
                            ` : `
                                <div class="mt-2">
                                    <span class="badge bg-secondary">Sem Instagram Business</span>
                                </div>
                            `}
                        </div>
                    </div>
                </div>
            `;
        });
    }
    
    html += '</div>';
    container.innerHTML = html;
    
    // Preencher select do Instagram
    const instagramSelect = document.getElementById('instagram-account');
    instagramSelect.innerHTML = '<option value="">Selecione uma conta</option>';
    
    pages.forEach(page => {
        if (page.instagram_business_account) {
            const option = document.createElement('option');
            option.value = page.instagram_business_account.id;
            option.textContent = `${page.name} (Instagram)`;
            instagramSelect.appendChild(option);
        }
    });
}

// Carregar contas completas (pessoais + business) - agora via modal
function loadAccountsComplete() {
    showManualPageModal('business');
}

// Mostrar contas completas
function displayAccountsComplete(data) {
    const container = document.getElementById('accounts-container');
    
    let html = `
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6><i class="bi bi-check-circle-fill text-success"></i> ${data.message}</h6>
            <button class="btn btn-sm btn-outline-info" onclick="showCompleteDebug(${JSON.stringify(data).replace(/"/g, '&quot;')})">
                <i class="bi bi-code-slash"></i> Debug
            </button>
        </div>`;

    if (data.data.pages && data.data.pages.length > 0) {
        html += '<div class="row">';
        
        data.data.pages.forEach(page => {
            const hasInstagram = page.instagram_business_account;
            const sourceIcon = page.source === 'business' ? 'bi-building' : 'bi-person-fill';
            const sourceText = page.source === 'business' ? 'Business Manager' : 'Página Pessoal';
            
            html += `
                <div class="col-md-6 mb-3">
                    <div class="card h-100 ${hasInstagram ? 'border-success' : 'border-warning'}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="card-title mb-0">${page.name}</h6>
                                <small class="text-muted">
                                    <i class="bi ${sourceIcon}"></i> ${sourceText}
                                </small>
                            </div>
                            <p class="text-muted small mb-2">ID: ${page.id}</p>
                            ${page.business_name ? `<p class="text-info small mb-2"><i class="bi bi-building"></i> ${page.business_name}</p>` : ''}
                            
                            <div class="d-flex align-items-center">
                                ${hasInstagram ? 
                                    `<div class="text-success">
                                        <i class="bi bi-instagram"></i> 
                                        <small>Instagram Business: ${page.instagram_business_account.id}</small>
                                    </div>` : 
                                    `<div class="text-warning">
                                        <i class="bi bi-exclamation-triangle"></i> 
                                        <small>Sem Instagram Business</small>
                                    </div>`
                                }
                            </div>
                        </div>
                    </div>
                </div>`;
        });
        
        html += '</div>';
        
        // Popular selects
        populatePageSelects(data.data.pages);
        populateInstagramSelects(data.data.pages);
    } else {
        html += `
            <div class="alert alert-warning">
                <h6><i class="bi bi-exclamation-triangle"></i> Nenhuma página encontrada</h6>
                <p>Não foram encontradas páginas nem pessoais nem no Business Manager.</p>
                <button class="btn btn-sm btn-outline-primary" onclick="showCompleteDebug(${JSON.stringify(data).replace(/"/g, '&quot;')})">
                    Ver Debug Completo
                </button>
            </div>`;
    }
    
    container.innerHTML = html;
}

// Mostrar debug das contas completas
function showCompleteDebug(data) {
    showFullDebugModal(data, 'Debug - Busca Completa (Pessoais + Business)');
}

// Mostrar/ocultar ajuda dos botões
function showAccountsHelp() {
    const helpDiv = document.getElementById('accounts-help');
    const isVisible = helpDiv.style.display !== 'none';
    helpDiv.style.display = isVisible ? 'none' : 'block';
}

// Mostrar modal para busca manual por ID
function showManualPageModal(type = 'manual') {
    const modal = new bootstrap.Modal(document.getElementById('manualPageModal'));
    
    // Ajustar título e texto baseado no tipo
    const modalTitle = document.querySelector('#manualPageModal .modal-title');
    const alertText = document.querySelector('#manualPageModal .alert-info');
    
    if (type === 'business') {
        modalTitle.innerHTML = '<i class="bi bi-building"></i> Buscar Página + Business';
        alertText.innerHTML = `
            <i class="bi bi-info-circle"></i>
            <strong>Busca por ID - Páginas + Business:</strong><br>
            • Para páginas que estão em Business Manager<br>
            • Páginas conectadas com Instagram (portfólio)<br>
            • Páginas que não aparecem na busca automática<br>
            • Digite o ID específico da página do Facebook
        `;
    } else {
        modalTitle.innerHTML = '<i class="bi bi-search"></i> Buscar Página por ID';
        alertText.innerHTML = `
            <i class="bi bi-info-circle"></i>
            <strong>Quando usar este método:</strong><br>
            • Sua página está conectada com Instagram (portfólio)<br>
            • A página não aparece nos outros métodos de busca<br>
            • Você tem o ID específico da página do Facebook
        `;
    }
    
    // Limpar campos anteriores
    document.getElementById('page-id-input').value = '';
    document.getElementById('manual-search-result').style.display = 'none';
    document.getElementById('use-manual-page-btn').style.display = 'none';
    
    modal.show();
}

// Buscar página por ID
let currentManualPage = null;
function searchPageById() {
    const pageId = document.getElementById('page-id-input').value.trim();
    
    if (!pageId) {
        alert('Por favor, digite o ID da página');
        return;
    }
    
    // Mostrar loading
    document.getElementById('manual-search-loading').style.display = 'block';
    document.getElementById('manual-search-result').style.display = 'none';
    document.getElementById('use-manual-page-btn').style.display = 'none';
    
    fetch(`/platforms/${platformData.id}/hashtags/page-by-id?page_id=${encodeURIComponent(pageId)}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('manual-search-loading').style.display = 'none';
            
            if (data.success) {
                currentManualPage = data.page;
                displayManualPageResult(data.page);
                document.getElementById('use-manual-page-btn').style.display = 'inline-block';
            } else {
                displayManualPageError(data.error, data.debug);
            }
        })
        .catch(error => {
            console.error('Erro ao buscar página:', error);
            document.getElementById('manual-search-loading').style.display = 'none';
            displayManualPageError('Erro de conexão: ' + error.message);
        });
}

// Exibir resultado da busca manual
function displayManualPageResult(page) {
    const resultDiv = document.getElementById('manual-search-result');
    
    const instagramInfo = page.instagram_business_account ? 
        `<div class="badge bg-success me-2">
            <i class="bi bi-instagram"></i> Instagram: @${page.instagram_business_account.username}
        </div>` : 
        '<div class="badge bg-warning">Sem Instagram conectado</div>';
    
    const accessInfo = page.has_access ?
        '<div class="badge bg-success">Você tem acesso</div>' :
        '<div class="badge bg-danger">Acesso limitado</div>';
    
    const pictureHtml = page.picture ? 
        `<img src="${page.picture}" alt="Foto da página" class="rounded me-2" style="width: 50px; height: 50px; object-fit: cover;">` :
        '<i class="bi bi-image text-muted me-2" style="font-size: 50px;"></i>';
    
    resultDiv.innerHTML = `
        <div class="alert alert-success">
            <div class="d-flex align-items-start">
                ${pictureHtml}
                <div class="flex-grow-1">
                    <h6 class="mb-1">
                        <i class="bi bi-facebook text-primary"></i> ${page.name}
                    </h6>
                    <p class="mb-2 text-muted small">ID: ${page.id}</p>
                    <div class="mb-2">
                        ${instagramInfo}
                        ${accessInfo}
                    </div>
                    ${page.category ? `<small class="text-muted">Categoria: ${page.category}</small>` : ''}
                    ${page.fan_count ? `<br><small class="text-muted">Seguidores: ${page.fan_count.toLocaleString()}</small>` : ''}
                </div>
            </div>
        </div>
    `;
    
    resultDiv.style.display = 'block';
}

// Exibir erro na busca manual
function displayManualPageError(error, debug = null) {
    const resultDiv = document.getElementById('manual-search-result');
    
    resultDiv.innerHTML = `
        <div class="alert alert-danger">
            <h6><i class="bi bi-exclamation-triangle"></i> Erro ao buscar página</h6>
            <p class="mb-0">${error}</p>
            ${debug ? `<small class="text-muted mt-2 d-block">Debug: ${JSON.stringify(debug, null, 2)}</small>` : ''}
        </div>
    `;
    
    resultDiv.style.display = 'block';
}

// Usar página encontrada manualmente
function useManualPage() {
    if (!currentManualPage) {
        alert('Nenhuma página selecionada');
        return;
    }
    
    // Salvar conta no banco de dados
    saveAccountToDatabase(currentManualPage);
    
    // Fechar modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('manualPageModal'));
    modal.hide();
    
    // Simular o resultado como se viesse de uma busca normal
    const mockResult = {
        success: true,
        pages: [currentManualPage],
        debug: {
            method: 'manual_search',
            page_id: currentManualPage.id
        },
        total_pages: 1,
        message: `Página ${currentManualPage.name} carregada e salva no banco`
    };
    
    // Processar como se fosse uma resposta normal
    displayAccounts(mockResult, 'manual');
    
    // Popular os selects de Instagram
    populateInstagramSelects([currentManualPage]);
    
    // Habilitar controles
    enableControls();
    
    // Se a página tem Instagram, selecionar automaticamente
    if (currentManualPage.instagram_business_account) {
        setTimeout(() => {
            const instagramSelect = document.getElementById('instagram-account');
            if (instagramSelect) {
                const option = instagramSelect.querySelector(`option[value="${currentManualPage.instagram_business_account.id}"]`);
                if (option) {
                    instagramSelect.value = currentManualPage.instagram_business_account.id;
                    
                    // Mostrar feedback visual
                    showAlert(`Conta Instagram selecionada: @${currentManualPage.instagram_business_account.username}`, 'success');
                }
            }
        }, 100);
    }
    
    // Limpar página atual
    currentManualPage = null;
}

// Carregar contas usando Facebook SDK
function loadAccountsSDK() {
    const container = document.getElementById('accounts-container');
    const loading = document.getElementById('loading-accounts');
    
    container.style.display = 'none';
    loading.style.display = 'block';
    
    const platformId = {{ $platform->id }};
    const url = `/platforms/${platformId}/hashtags/accounts-sdk`;
    
    console.log('⚙️ Usando Facebook SDK:', url);
    
    fetch(url)
        .then(response => {
            console.log('SDK Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('SDK Response data:', data);
            loading.style.display = 'none';
            
            if (data.success) {
                displayAccountsSDK(data);
                enableControls();
            } else {
                displayAccountsError(data, 'SDK');
            }
            
            container.style.display = 'block';
        })
        .catch(error => {
            console.error('SDK Fetch error:', error);
            loading.style.display = 'none';
            container.innerHTML = `
                <div class="alert alert-danger">
                    <h6><i class="bi bi-exclamation-triangle"></i> Erro no Facebook SDK</h6>
                    <p>Não foi possível carregar as contas via SDK: ${error.message}</p>
                    <button class="btn btn-sm btn-outline-success" onclick="loadAccountsSDK()">Tentar Novamente</button>
                </div>`;
            container.style.display = 'block';
        });
}

// Mostrar contas do SDK
function displayAccountsSDK(data) {
    const container = document.getElementById('accounts-container');
    
    let html = `
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6><i class="bi bi-check-circle-fill text-success"></i> ${data.message}</h6>
            <div class="d-flex gap-2">
                <span class="badge bg-success">SDK Oficial</span>
                <button class="btn btn-sm btn-outline-info" onclick="showSDKDebug(${JSON.stringify(data).replace(/"/g, '&quot;')})">
                    <i class="bi bi-code-slash"></i> Debug
                </button>
            </div>
        </div>`;

    if (data.data.pages && data.data.pages.length > 0) {
        html += '<div class="row">';
        
        data.data.pages.forEach(page => {
            const hasInstagram = page.instagram_business_account;
            const sourceIcon = page.source === 'business' ? 'bi-building' : 'bi-person-fill';
            const sourceText = page.source === 'business' ? 'Business Manager' : 'Página Pessoal';
            const borderClass = hasInstagram ? 'border-success' : 'border-warning';
            
            html += `
                <div class="col-md-6 mb-3">
                    <div class="card h-100 ${borderClass}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="card-title mb-0">${page.name}</h6>
                                <div class="d-flex flex-column align-items-end">
                                    <small class="text-muted mb-1">
                                        <i class="bi ${sourceIcon}"></i> ${sourceText}
                                    </small>
                                    <span class="badge bg-info">SDK</span>
                                </div>
                            </div>
                            <p class="text-muted small mb-2">ID: ${page.id}</p>
                            ${page.business_name ? `<p class="text-info small mb-2"><i class="bi bi-building"></i> ${page.business_name}</p>` : ''}
                            
                            <div class="d-flex align-items-center">
                                ${hasInstagram ? 
                                    `<div class="text-success">
                                        <i class="bi bi-instagram"></i> 
                                        <small>Instagram Business: ${page.instagram_business_account.id}</small>
                                    </div>` : 
                                    `<div class="text-warning">
                                        <i class="bi bi-exclamation-triangle"></i> 
                                        <small>Sem Instagram Business</small>
                                    </div>`
                                }
                            </div>
                        </div>
                    </div>
                </div>`;
        });
        
        html += '</div>';
        
        // Popular selects
        populatePageSelects(data.data.pages);
        populateInstagramSelects(data.data.pages);
        
        // Mostrar estatísticas
        html += `
            <div class="alert alert-info">
                <div class="row text-center">
                    <div class="col-3">
                        <strong>${data.data.total_found}</strong><br>
                        <small>Total de Páginas</small>
                    </div>
                    <div class="col-3">
                        <strong>${data.data.sources.personal}</strong><br>
                        <small>Pessoais</small>
                    </div>
                    <div class="col-3">
                        <strong>${data.data.sources.business}</strong><br>
                        <small>Business Manager</small>
                    </div>
                    <div class="col-3">
                        <strong class="text-success">${data.data.instagram_accounts}</strong><br>
                        <small>Com Instagram</small>
                    </div>
                </div>
            </div>`;
    } else {
        html += `
            <div class="alert alert-warning">
                <h6><i class="bi bi-exclamation-triangle"></i> Nenhuma página encontrada pelo SDK</h6>
                <p>O Facebook SDK não encontrou páginas. Verifique suas permissões.</p>
                <button class="btn btn-sm btn-outline-primary" onclick="showSDKDebug(${JSON.stringify(data).replace(/"/g, '&quot;')})">
                    Ver Debug do SDK
                </button>
            </div>`;
    }
    
    container.innerHTML = html;
}

// Mostrar debug do SDK
function showSDKDebug(data) {
    showFullDebugModal(data, 'Debug - Facebook SDK (Oficial)');
}

// Mostrar erros de contas (função que estava faltando)
function displayAccountsError(data, methodType) {
    const container = document.getElementById('accounts-container');
    
    let html = `
        <div class="alert alert-danger">
            <h6><i class="bi bi-exclamation-triangle"></i> Erro ao carregar contas - ${methodType}</h6>
            <p><strong>Erro:</strong> ${data.error || 'Erro desconhecido'}</p>
    `;
    
    // Mostrar mensagem adicional se existir
    if (data.message) {
        html += `<p><strong>Detalhes:</strong> ${data.message}</p>`;
    }
    
    // Mostrar sugestões se existirem
    if (data.suggestions && data.suggestions.length > 0) {
        html += `
            <hr>
            <p><strong>Possíveis soluções:</strong></p>
            <ul>
        `;
        data.suggestions.forEach(suggestion => {
            html += `<li>${suggestion}</li>`;
        });
        html += `</ul>`;
    }
    
    // Botão para debug se disponível
    if (data.debug) {
        html += `
            <hr>
            <button class="btn btn-outline-info btn-sm" onclick="showMethodDebug('${methodType}', ${JSON.stringify(data).replace(/"/g, '&quot;')})">
                <i class="bi bi-bug"></i> Ver Debug
            </button>
        `;
    }
    
    // Botões de ação baseados no tipo
    html += `
        <hr>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-primary" onclick="${methodType === 'SDK' ? 'loadAccountsSDK' : methodType === 'Complete' ? 'loadAccountsComplete' : 'loadAccounts'}()">
                <i class="bi bi-arrow-clockwise"></i> Tentar Novamente
            </button>
    `;
    
    // Botões alternativos se o método atual falhou
    if (methodType === 'SDK') {
        html += `
            <button class="btn btn-sm btn-outline-warning" onclick="loadAccountsComplete()">
                <i class="bi bi-building"></i> Tentar Método Completo
            </button>
            <button class="btn btn-sm btn-outline-secondary" onclick="loadAccounts()">
                <i class="bi bi-person-fill"></i> Tentar Método Básico
            </button>
        `;
    } else if (methodType === 'Complete') {
        html += `
            <button class="btn btn-sm btn-outline-success" onclick="loadAccountsSDK()">
                <i class="bi bi-gear-wide-connected"></i> Tentar SDK
            </button>
            <button class="btn btn-sm btn-outline-secondary" onclick="loadAccounts()">
                <i class="bi bi-person-fill"></i> Tentar Método Básico
            </button>
        `;
    } else {
        html += `
            <button class="btn btn-sm btn-outline-success" onclick="loadAccountsSDK()">
                <i class="bi bi-gear-wide-connected"></i> Tentar SDK
            </button>
            <button class="btn btn-sm btn-outline-info" onclick="loadAccountsComplete()">
                <i class="bi bi-building"></i> Tentar Método Completo
            </button>
        `;
    }
    
    html += `
        </div>
    </div>`;
    
    container.innerHTML = html;
}

// Mostrar debug específico do método
function showMethodDebug(methodType, data) {
    showFullDebugModal(data, `Debug - ${methodType} Method`);
}

// Mostrar modal de debug completo (função que estava faltando)
function showFullDebugModal(debugData, title) {
    // Criar modal dinamicamente
    const modalId = 'fullDebugModal';
    let existingModal = document.getElementById(modalId);
    if (existingModal) {
        existingModal.remove();
    }
    
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = modalId;
    modal.innerHTML = `
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-bug"></i> ${title || 'Debug Completo'}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Informações Principais</h6>
                            <div class="mb-3">
                                <strong>Status:</strong> ${debugData.success ? '<span class="text-success">Sucesso</span>' : '<span class="text-danger">Erro</span>'}
                            </div>
                            ${debugData.message ? `<div class="mb-3"><strong>Mensagem:</strong> ${debugData.message}</div>` : ''}
                            ${debugData.error ? `<div class="mb-3"><strong>Erro:</strong> <span class="text-danger">${debugData.error}</span></div>` : ''}
                            
                            ${debugData.debug ? `
                                <h6 class="mt-3">Debug Info</h6>
                                <pre class="bg-light p-2 rounded small" style="max-height: 300px; overflow-y: auto;">${JSON.stringify(debugData.debug, null, 2)}</pre>
                            ` : ''}
                        </div>
                        <div class="col-md-6">
                            <h6>Dados Completos</h6>
                            <pre class="bg-light p-2 rounded small" style="max-height: 400px; overflow-y: auto;">${JSON.stringify(debugData, null, 2)}</pre>
                            
                            ${debugData.suggestions && debugData.suggestions.length > 0 ? `
                                <h6 class="mt-3">Sugestões</h6>
                                <ul class="list-group list-group-flush">
                                    ${debugData.suggestions.map(suggestion => `<li class="list-group-item py-1">${suggestion}</li>`).join('')}
                                </ul>
                            ` : ''}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" onclick="copyFullDebugInfo()">
                        <i class="bi bi-clipboard"></i> Copiar Debug
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Salvar dados para cópia
    window.lastFullDebugData = debugData;
    
    // Mostrar modal
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    // Remover modal quando fechar para evitar duplicatas
    modal.addEventListener('hidden.bs.modal', function () {
        modal.remove();
    });
}

// Copiar informações de debug completo
function copyFullDebugInfo() {
    if (window.lastFullDebugData) {
        navigator.clipboard.writeText(JSON.stringify(window.lastFullDebugData, null, 2))
            .then(() => showAlert('Informações de debug copiadas!', 'success'))
            .catch(() => showAlert('Erro ao copiar informações', 'danger'));
    }
}

// Habilitar controles após carregar contas
function enableControls() {
    document.getElementById('instagram-account').disabled = false;
    document.getElementById('hashtag-input').disabled = false;
    document.getElementById('search-hashtag-btn').disabled = false;
    document.getElementById('instagram-mentions-btn').disabled = false;
    document.getElementById('facebook-mentions-btn').disabled = false;
}

// Buscar hashtags
document.getElementById('hashtag-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const instagramSelect = document.getElementById('instagram-account');
    const instagramId = instagramSelect.value;
    const hashtag = document.getElementById('hashtag-input').value;
    
    // Debug: verificar o que está sendo selecionado
    console.log('🔍 Valores do formulário:');
    console.log('- Instagram ID selecionado:', instagramId);
    console.log('- Hashtag:', hashtag);
    console.log('- Opção selecionada:', instagramSelect.selectedOptions[0]?.textContent);
    console.log('- Data da opção:', instagramSelect.selectedOptions[0]?.getAttribute('data-instagram-id'));
    console.log('- Page ID:', instagramSelect.selectedOptions[0]?.getAttribute('data-page-id'));
    
    if (!instagramId || !hashtag) {
        showAlert('Selecione uma conta e digite uma hashtag', 'warning');
        return;
    }
    
    searchHashtag(instagramId, hashtag);
});

function searchHashtag(instagramId, hashtag) {
    console.log('🔍 Iniciando busca de hashtag:', { instagramId, hashtag }); // Debug
    
    const resultsDiv = document.getElementById('hashtag-results');
    resultsDiv.style.display = 'block';
    resultsDiv.innerHTML = `
        <div class="text-center py-3">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Buscando posts com #${hashtag}...</p>
            <small class="text-muted d-block">Instagram ID: ${instagramId}</small>
        </div>
    `;
    
    // Obter CSRF token com proteção
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    const token = csrfToken ? csrfToken.getAttribute('content') : '';
    
    if (!token) {
        console.warn('CSRF token não encontrado');
    }
    
    const requestData = {
        hashtag: hashtag,
        instagram_business_id: instagramId
    };
    
    console.log('📤 Enviando requisição:', requestData); // Debug
    
    fetch(`/platforms/{{ $platform->id }}/hashtags/search`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        console.log('📥 Status da resposta:', response.status); // Debug
        return response.json();
    })
    .then(data => {
        console.log('📥 Dados recebidos:', data); // Debug
        console.log('Resposta da busca de hashtags:', data);
        
        if (data.success) {
            displayHashtagResults(data);
        } else {
            resultsDiv.innerHTML = `
                <div class="alert alert-danger">
                    <h6><i class="bi bi-exclamation-triangle"></i> Erro na Busca de Hashtags</h6>
                    <p class="mb-2">${data.error}</p>
                    <div class="mt-2">
                        <button class="btn btn-sm btn-primary" onclick="searchHashtag('${instagramId}', '${hashtag}')">
                            <i class="bi bi-arrow-clockwise"></i> Tentar Novamente
                        </button>
                    </div>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Erro na requisição de hashtags:', error);
        
        resultsDiv.innerHTML = `
            <div class="alert alert-danger">
                <h6><i class="bi bi-wifi-off"></i> Erro de Conexão</h6>
                <p class="mb-2">Não foi possível conectar com o servidor.</p>
                <small class="text-muted">Erro técnico: ${error.message}</small>
                <div class="mt-2">
                    <button class="btn btn-sm btn-primary" onclick="searchHashtag('${instagramId}', '${hashtag}')">
                        <i class="bi bi-arrow-clockwise"></i> Tentar Novamente
                    </button>
                </div>
            </div>
        `;
    });
}

function displayHashtagResults(data) {
    const resultsDiv = document.getElementById('hashtag-results');
    const posts = data.posts;
    
    if (posts.length === 0) {
        resultsDiv.innerHTML = `
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                Nenhum post encontrado para #${data.hashtag}
            </div>
        `;
        return;
    }
    
    let html = `
        <div class="alert alert-success">
            <div class="d-flex align-items-center">
                <i class="bi bi-instagram text-primary me-2 fs-5"></i>
                <div>
                    <strong>Instagram - Posts encontrados: ${data.total_posts}</strong><br>
                    <small class="text-muted">Hashtag: #${data.hashtag} | Fonte: Instagram Business API</small>
                </div>
            </div>
        </div>
        <div class="row">
    `;
    
    posts.forEach(post => {
        const postDate = new Date(post.timestamp).toLocaleString('pt-BR');
        const mediaType = post.media_type || 'IMAGE';
        const mediaTypeIcon = mediaType === 'VIDEO' ? 'bi-play-circle-fill' : 
                             mediaType === 'CAROUSEL_ALBUM' ? 'bi-images' : 'bi-image-fill';
        const mediaTypeText = mediaType === 'VIDEO' ? 'Vídeo' : 
                             mediaType === 'CAROUSEL_ALBUM' ? 'Álbum' : 'Imagem';
        
        html += `
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    ${post.media_url ? `
                        <div class="position-relative">
                            ${mediaType === 'VIDEO' ? `
                                <video class="card-img-top" style="height: 200px; object-fit: cover;" controls>
                                    <source src="${post.media_url}" type="video/mp4">
                                    Seu navegador não suporta vídeo.
                                </video>
                            ` : `
                                <img src="${post.media_url}" class="card-img-top" style="height: 200px; object-fit: cover;" alt="Post do Instagram">
                            `}
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-primary">
                                    <i class="${mediaTypeIcon} me-1"></i>${mediaTypeText}
                                </span>
                            </div>
                        </div>
                    ` : ''}
                    
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-instagram text-primary me-2"></i>
                                <small class="text-primary fw-bold">Instagram Post</small>
                            </div>
                        </div>
                        
                        <div class="mb-auto">
                            <p class="card-text small text-muted mb-2">
                                <i class="bi bi-calendar3 me-1"></i>
                                ${postDate}
                            </p>
                            <p class="card-text small">
                                <i class="bi bi-hash me-1"></i>
                                Post contém #${data.hashtag}
                            </p>
                        </div>
                        
                        <div class="mt-3 d-flex gap-2">
                            <a href="${post.permalink}" target="_blank" class="btn btn-primary btn-sm flex-fill">
                                <i class="bi bi-box-arrow-up-right me-1"></i>
                                Ver no Instagram
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    
    if (posts.length > 12) {
        html += `
            <div class="text-center mt-3">
                <p class="text-muted">Mostrando os primeiros 12 posts de ${posts.length} encontrados</p>
            </div>
        `;
    }
    
    resultsDiv.innerHTML = html;
}

// Carregar menções do Instagram
function loadInstagramMentions() {
    const instagramId = document.getElementById('instagram-account').value;
    
    if (!instagramId) {
        showAlert('Selecione uma conta do Instagram primeiro', 'warning');
        return;
    }
    
    loadMentions('instagram', instagramId);
}

// Carregar menções do Facebook
function loadFacebookMentions() {
    if (!platformData.accounts || !platformData.accounts.pages.length) {
        showAlert('Carregue as contas primeiro', 'warning');
        return;
    }
    
    // Usar a primeira página para exemplo
    const pageId = platformData.accounts.pages[0].id;
    loadMentions('facebook', pageId);
}

function loadMentions(type, accountId) {
    const resultsDiv = document.getElementById('mentions-results');
    resultsDiv.style.display = 'block';
    resultsDiv.innerHTML = `
        <div class="text-center py-3">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Carregando menções do ${type}...</p>
        </div>
    `;
    
    const endpoint = type === 'instagram' ? 'mentions' : 'facebook-mentions';
    const param = type === 'instagram' ? 'instagram_business_id' : 'page_id';
    
    // Obter CSRF token com proteção
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    const token = csrfToken ? csrfToken.getAttribute('content') : '';
    
    fetch(`/platforms/{{ $platform->id }}/hashtags/${endpoint}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({
            [param]: accountId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayMentionsResults(data, type);
        } else {
            resultsDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    ${data.error}
                </div>
            `;
        }
    })
    .catch(error => {
        resultsDiv.innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i>
                Erro: ${error}
            </div>
        `;
    });
}

function displayMentionsResults(data, type) {
    const resultsDiv = document.getElementById('mentions-results');
    const mentions = type === 'instagram' ? data.mentions : data.tagged_posts;
    const platformName = type === 'instagram' ? 'Instagram' : 'Facebook';
    const platformIcon = type === 'instagram' ? 'bi-instagram' : 'bi-facebook';
    const platformColor = type === 'instagram' ? 'text-primary' : 'text-primary';
    
    if (mentions.length === 0) {
        resultsDiv.innerHTML = `
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                Nenhuma menção encontrada no ${platformName}
            </div>
        `;
        return;
    }
    
    let html = `
        <div class="alert alert-success">
            <div class="d-flex align-items-center">
                <i class="bi ${platformIcon} ${platformColor} me-2 fs-5"></i>
                <div>
                    <strong>${platformName} - Menções encontradas: ${mentions.length}</strong><br>
                    <small class="text-muted">Fonte: ${platformName} API | Posts que mencionam sua conta</small>
                </div>
            </div>
        </div>
        <div class="row">
    `;
    
    mentions.forEach(mention => {
        const username = mention.username || mention.from?.name || 'Usuário desconhecido';
        const content = mention.caption || mention.message || 'Sem conteúdo';
        const date = new Date(mention.timestamp || mention.created_time).toLocaleString('pt-BR');
        const permalink = mention.permalink || mention.permalink_url || '#';
        
        html += `
            <div class="col-lg-6 col-md-12 mb-3">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi ${platformIcon} ${platformColor} me-2 fs-5"></i>
                            <div>
                                <h6 class="mb-0">${username}</h6>
                                <small class="text-muted">${platformName} Post</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <p class="card-text small text-dark">
                                ${content.length > 200 ? content.substring(0, 200) + '...' : content}
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <p class="card-text small text-muted">
                                <i class="bi bi-calendar3 me-1"></i>
                                ${date}
                            </p>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <a href="${permalink}" target="_blank" class="btn btn-primary btn-sm">
                                <i class="bi bi-box-arrow-up-right me-1"></i>
                                Ver no ${platformName}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    
    if (mentions.length > 8) {
        html += `
            <div class="text-center mt-3">
                <p class="text-muted">Mostrando as primeiras 8 menções de ${mentions.length} encontradas</p>
            </div>
        `;
    }
    
    resultsDiv.innerHTML = html;
}

// Testar se Laravel está funcionando
function testLaravelConnection() {
    showAlert('Testando conexão com Laravel...', 'info');
    
    fetch('/test-laravel')
        .then(response => {
            console.log('Test Laravel response status:', response.status);
            console.log('Test Laravel headers:', [...response.headers.entries()]);
            return response.json();
        })
        .then(data => {
            console.log('Test Laravel data:', data);
            showAlert(`Laravel OK! Env: ${data.env}, URL: ${data.url}`, 'success');
        })
        .catch(error => {
            console.error('Laravel test error:', error);
            showAlert('Laravel não está funcionando corretamente: ' + error.message, 'danger');
        });
}

// Testar rota de debug específica
function testAccountsDebug() {
    showAlert('Testando rota de debug...', 'info');
    
    fetch('/platforms/{{ $platform->id }}/hashtags/accounts-debug')
        .then(response => {
            console.log('Debug route response:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Debug route data:', data);
            showAlert('Rota de debug funcionando! O problema pode estar no controller.', 'success');
        })
        .catch(error => {
            console.error('Debug route error:', error);
            showAlert('Rota de debug falhou: ' + error.message, 'danger');
        });
}

// Testar model binding
function testModelBinding() {
    showAlert('Testando model binding...', 'info');
    
    fetch('/platforms/{{ $platform->id }}/test-binding')
        .then(response => {
            console.log('Model binding response:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Model binding data:', data);
            showAlert(`Model binding OK! Platform: ${data.platform_name}`, 'success');
        })
        .catch(error => {
            console.error('Model binding error:', error);
            showAlert('Model binding falhou: ' + error.message, 'danger');
        });
}

// Testar rotas de hashtags gerais
function testHashtagsRoute() {
    showAlert('Testando rotas de hashtags...', 'info');
    
    fetch('/test-hashtags-route')
        .then(response => {
            console.log('Hashtags route response:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Hashtags route data:', data);
            showAlert('Rotas de hashtags disponíveis! Problema específico na rota /accounts.', 'warning');
        })
        .catch(error => {
            console.error('Hashtags route error:', error);
            showAlert('Rotas de hashtags falharam: ' + error.message, 'danger');
        });
}

// Função para mostrar/ocultar debug info
function showDebugInfo() {
    const debugDiv = document.getElementById('debug-info');
    if (debugDiv) {
        debugDiv.style.display = debugDiv.style.display === 'none' ? 'block' : 'none';
    }
}

// Função para mostrar alertas
// Função para mostrar alertas
function showAlert(message, type = 'info') {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alert);
    
    // Auto-remover após 5 segundos
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}

// Popular selects de páginas (função que estava faltando)
function populatePageSelects(pages) {
    // Esta função pode ser usada para popular selects de páginas Facebook se necessário
    const pageSelects = document.querySelectorAll('.page-select');
    
    pageSelects.forEach(select => {
        select.innerHTML = '<option value="">Selecione uma página</option>';
        
        pages.forEach(page => {
            const option = document.createElement('option');
            option.value = page.id;
            option.textContent = page.name;
            select.appendChild(option);
        });
    });
}

// Popular selects do Instagram (função que estava faltando)
function populateInstagramSelects(pages) {
    const instagramSelect = document.getElementById('instagram-account');
    if (!instagramSelect) return;
    
    instagramSelect.innerHTML = '<option value="">Selecione uma conta do Instagram</option>';
    
    pages.forEach(page => {
        if (page.instagram_business_account) {
            const option = document.createElement('option');
            option.value = page.instagram_business_account.id;
            option.textContent = `${page.name} (Instagram)`;
            option.setAttribute('data-page-id', page.id);
            instagramSelect.appendChild(option);
        }
    });
    
    // Se não há contas Instagram, mostrar mensagem
    if (instagramSelect.options.length === 1) {
        const option = document.createElement('option');
        option.value = '';
        option.textContent = 'Nenhuma conta Instagram Business encontrada';
        option.disabled = true;
        instagramSelect.appendChild(option);
    }
}

// Verificar status do token Facebook
function checkToken() {
    showAlert('Verificando status do token...', 'info');
    
    fetch(`/platforms/{{ $platform->id }}/check-token`)
        .then(response => response.json())
        .then(data => {
            console.log('Token Status:', data);
            
            if (data.error) {
                showTokenStatusModal({
                    status: 'error',
                    title: '❌ Token não encontrado',
                    message: data.error,
                    solution: data.solution,
                    data: data
                });
            } else if (data.token_valid) {
                showTokenStatusModal({
                    status: 'success',
                    title: '✅ Token válido',
                    message: `Token funcionando para: ${data.me_test.data?.name || 'Usuário'}`,
                    data: data
                });
                showAlert('Token válido e funcionando!', 'success');
            } else {
                showTokenStatusModal({
                    status: 'error',
                    title: '❌ Token inválido',
                    message: 'Token expirado ou inválido',
                    solution: 'Reconecte a plataforma para renovar o token',
                    data: data
                });
            }
        })
        .catch(error => {
            console.error('Erro ao verificar token:', error);
            showAlert('❌ Erro ao verificar token: ' + error.message, 'danger');
        });
}

// Mostrar modal com status detalhado do token
function showTokenStatusModal(tokenStatus) {
    const modalId = 'tokenStatusModal';
    let existingModal = document.getElementById(modalId);
    if (existingModal) {
        existingModal.remove();
    }
    
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = modalId;
    
    const statusColor = tokenStatus.status === 'success' ? 'success' : 'danger';
    const data = tokenStatus.data;
    
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-${statusColor} text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-shield-check"></i> ${tokenStatus.title}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Informações do Token</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Plataforma:</strong></td>
                                    <td>${data.platform_name}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        ${data.token_valid ? 
                                            '<span class="badge bg-success">Válido</span>' : 
                                            '<span class="badge bg-danger">Inválido</span>'
                                        }
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Preview:</strong></td>
                                    <td><code>${data.token_preview || 'N/A'}</code></td>
                                </tr>
                                <tr>
                                    <td><strong>Tamanho:</strong></td>
                                    <td>${data.token_length || 0} caracteres</td>
                                </tr>
                                <tr>
                                    <td><strong>Última conexão:</strong></td>
                                    <td>${data.last_connected || 'N/A'}</td>
                                </tr>
                            </table>
                            
                            ${data.me_test && data.me_test.success ? `
                                <h6 class="text-success">✅ Teste da API</h6>
                                <p><strong>Usuário:</strong> ${data.me_test.data?.name || 'N/A'}</p>
                                <p><strong>ID:</strong> ${data.me_test.data?.id || 'N/A'}</p>
                            ` : data.me_test ? `
                                <h6 class="text-danger">❌ Erro na API</h6>
                                <p><strong>Status HTTP:</strong> ${data.me_test.status}</p>
                                <p><strong>Erro:</strong> ${data.me_test.error?.message || 'Erro desconhecido'}</p>
                            ` : ''}
                        </div>
                        <div class="col-md-6">
                            <h6>Recomendações</h6>
                            ${data.recommendations ? `
                                <ul class="list-group list-group-flush">
                                    ${data.recommendations.map(rec => `<li class="list-group-item py-1">${rec}</li>`).join('')}
                                </ul>
                            ` : ''}
                            
                            ${data.debug_token ? `
                                <h6 class="mt-3">Debug do Token</h6>
                                <pre class="bg-light p-2 rounded small" style="max-height: 200px; overflow-y: auto;">${JSON.stringify(data.debug_token, null, 2)}</pre>
                            ` : ''}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    ${!data.token_valid ? `
                        <a href="/platforms/${data.platform_id}" class="btn btn-primary">
                            <i class="bi bi-arrow-clockwise"></i> Reconectar Plataforma
                        </a>
                    ` : ''}
                    <button type="button" class="btn btn-info" onclick="copyTokenDebug()">
                        <i class="bi bi-clipboard"></i> Copiar Debug
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Salvar dados para cópia
    window.lastTokenDebug = data;
    
    // Mostrar modal
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    // Remover modal quando fechar
    modal.addEventListener('hidden.bs.modal', function () {
        modal.remove();
    });
}

// Copiar debug do token
function copyTokenDebug() {
    if (window.lastTokenDebug) {
        navigator.clipboard.writeText(JSON.stringify(window.lastTokenDebug, null, 2))
            .then(() => showAlert('Debug do token copiado!', 'success'))
            .catch(() => showAlert('Erro ao copiar debug', 'danger'));
    }
}

// Função de teste para verificar se as funções estão definidas
function testSDKFunction() {
    console.log('🔍 Testando funções JavaScript...');
    
    const functions = {
        'loadAccountsSDK': typeof loadAccountsSDK,
        'loadAccounts': typeof loadAccounts,
        'loadAccountsComplete': typeof loadAccountsComplete,
        'displayAccountsError': typeof displayAccountsError,
        'showFullDebugModal': typeof showFullDebugModal
    };
    
    console.log('Funções disponíveis:', functions);
    
    let message = 'Status das funções:\n';
    for (const [name, type] of Object.entries(functions)) {
        message += `${name}: ${type}\n`;
    }
    
    alert(message);
    
    if (typeof loadAccountsSDK === 'function') {
        console.log('✅ loadAccountsSDK está definida, chamando...');
        loadAccountsSDK();
    } else {
        console.error('❌ loadAccountsSDK não está definida!');
        showAlert('Erro: loadAccountsSDK não está definida!', 'danger');
    }
}

// =====================================================
// FUNÇÕES PARA GERENCIAMENTO DE CONTAS SALVAS
// =====================================================

// Salvar conta no banco de dados
function saveAccountToDatabase(account) {
    // Garantir que temos o instagram_business_account correto
    const instagramBusinessAccount = account.instagram_business_account || null;
    
    const data = {
        account_id: account.id,
        name: account.name,
        type: instagramBusinessAccount ? 'page_with_instagram' : 'page',
        category: account.category || null,
        additional_info: {
            instagram_business_account: instagramBusinessAccount,
            business_name: account.business_name || null,
            source: account.source || 'manual',
            page_access_token: account.access_token || null
        }
    };

    console.log('Salvando conta:', data); // Debug

    fetch(`/platforms/{{ $platform->id }}/hashtags/save-account`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        console.log('Resultado do salvamento:', result); // Debug
        if (result.success) {
            showAlert(`✅ Conta "${account.name}" salva no banco de dados!`, 'success');
            // Recarregar lista de contas salvas para atualizar o select
            loadSavedAccountsToSelect();
        } else {
            if (result.message.includes('já está salva')) {
                showAlert(`ℹ️ Conta "${account.name}" ${result.message}`, 'info');
                // Mesmo se já existe, recarregar para garantir que apareça no select
                loadSavedAccountsToSelect();
            } else {
                showAlert(`❌ Erro ao salvar conta: ${result.message}`, 'danger');
            }
        }
    })
    .catch(error => {
        console.error('Erro ao salvar conta:', error);
        showAlert('❌ Erro ao salvar conta no banco de dados', 'danger');
    });
}

// Carregar contas salvas e popular o select
function loadSavedAccountsToSelect() {
    fetch(`/platforms/{{ $platform->id }}/hashtags/saved-accounts`)
        .then(response => response.json())
        .then(result => {
            if (result.success && result.accounts.length > 0) {
                populateAccountSelect(result.accounts);
                showSavedAccountsInterface(result.accounts);
            } else {
                // Se não há contas salvas, mostrar interface padrão
                showDefaultInterface();
            }
        })
        .catch(error => {
            console.error('Erro ao carregar contas salvas:', error);
            showDefaultInterface();
        });
}

// Popular select com contas salvas
function populateAccountSelect(accounts) {
    const instagramSelect = document.getElementById('instagram-account');
    if (!instagramSelect) return;

    instagramSelect.innerHTML = '<option value="">Selecione uma conta salva</option>';
    
    accounts.forEach(account => {
        // Verificar se tem Instagram Business Account nos dados salvos
        const additionalInfo = typeof account.additional_info === 'string' ? 
            JSON.parse(account.additional_info) : account.additional_info;
        
        const instagramBusinessAccount = additionalInfo?.instagram_business_account;
        
        if (instagramBusinessAccount && instagramBusinessAccount.id) {
            const option = document.createElement('option');
            // Usar o ID do Instagram Business Account como valor
            option.value = instagramBusinessAccount.id;
            option.textContent = `${account.name} (Instagram)`;
            option.setAttribute('data-account', JSON.stringify(account));
            option.setAttribute('data-page-id', account.account_id);
            option.setAttribute('data-instagram-id', instagramBusinessAccount.id);
            instagramSelect.appendChild(option);
        } else {
            // Se não tem Instagram Business Account, não adicionar à lista
            console.warn(`Conta "${account.name}" não possui Instagram Business Account conectado`);
        }
    });
    
    if (instagramSelect.options.length === 1) {
        const option = document.createElement('option');
        option.value = '';
        option.textContent = 'Nenhuma conta com Instagram Business encontrada';
        option.disabled = true;
        instagramSelect.appendChild(option);
    }
}

// Mostrar interface com contas salvas
function showSavedAccountsInterface(accounts) {
    const container = document.getElementById('accounts-container');
    
    let html = `
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6><i class="bi bi-database-check text-success"></i> Contas salvas carregadas (${accounts.length})</h6>
            <button class="btn btn-sm btn-outline-primary" onclick="showDefaultInterface()">
                <i class="bi bi-plus-circle"></i> Buscar novas contas
            </button>
        </div>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            <strong>Suas contas estão salvas!</strong> Selecione uma conta no campo abaixo ou busque novas contas.
        </div>`;

    container.innerHTML = html;
    enableControls();
}

// Mostrar interface padrão (buscar contas)
function showDefaultInterface() {
    const container = document.getElementById('accounts-container');
    
    container.innerHTML = `
        <div class="text-center py-4">
            <i class="bi bi-info-circle text-muted fs-4"></i>
            <p class="text-muted mt-2">Busque suas contas e páginas do Facebook/Instagram:</p>
            <div class="d-flex gap-2 justify-content-center flex-wrap">
                <button class="btn btn-info" onclick="loadAccountsComplete()">
                    <i class="bi bi-building"></i> Buscar Páginas + Business
                </button>
            </div>
        </div>`;
}

// Remover conta salva
function removeSavedAccount(accountId) {
    if (!confirm('Tem certeza que deseja remover esta conta salva?')) {
        return;
    }

    fetch(`/platforms/{{ $platform->id }}/hashtags/saved-account`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ account_id: accountId })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showAlert('✅ Conta removida com sucesso!', 'success');
            // Recarregar lista
            loadSavedAccountsToSelect();
        } else {
            showAlert(`❌ Erro ao remover conta: ${result.message}`, 'danger');
        }
    })
    .catch(error => {
        console.error('Erro ao remover conta:', error);
        showAlert('❌ Erro ao remover conta', 'danger');
    });
}

// Inicializar interface ao carregar página
document.addEventListener('DOMContentLoaded', function() {
    // Carregar contas salvas ao inicializar
    loadSavedAccountsToSelect();
});
</script>
@endsection