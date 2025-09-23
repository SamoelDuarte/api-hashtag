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
                                    <p class="text-muted mt-2">Clique em "Carregar Contas" para sincronizar suas páginas e contas do Instagram</p>
                                    <button class="btn btn-primary" onclick="loadAccounts()">
                                        <i class="bi bi-download"></i> Carregar Contas
                                    </button>
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
    
    const instagramId = document.getElementById('instagram-account').value;
    const hashtag = document.getElementById('hashtag-input').value;
    
    if (!instagramId || !hashtag) {
        showAlert('Selecione uma conta e digite uma hashtag', 'warning');
        return;
    }
    
    searchHashtag(instagramId, hashtag);
});

function searchHashtag(instagramId, hashtag) {
    const resultsDiv = document.getElementById('hashtag-results');
    resultsDiv.style.display = 'block';
    resultsDiv.innerHTML = `
        <div class="text-center py-3">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Buscando posts com #${hashtag}...</p>
        </div>
    `;
    
    fetch(`/platforms/{{ $platform->id }}/hashtags/search`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            hashtag: hashtag,
            instagram_business_id: instagramId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayHashtagResults(data);
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
            <i class="bi bi-check-circle"></i>
            Encontrados ${data.total_posts} posts com #${data.hashtag}
        </div>
        <div class="row">
    `;
    
    posts.slice(0, 6).forEach(post => {
        html += `
            <div class="col-md-6 mb-3">
                <div class="card border-0 bg-light">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-start">
                            <div class="me-2">
                                <i class="bi bi-instagram text-primary"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">@${post.username}</h6>
                                <p class="small text-muted mb-2">${post.caption ? post.caption.substring(0, 100) + '...' : 'Sem legenda'}</p>
                                <small class="text-muted">${new Date(post.timestamp).toLocaleDateString('pt-BR')}</small>
                            </div>
                        </div>
                        <div class="mt-2">
                            <a href="${post.permalink}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-box-arrow-up-right"></i> Ver Post
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    
    if (posts.length > 6) {
        html += `<p class="text-muted text-center">Mostrando 6 de ${posts.length} posts encontrados.</p>`;
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
    
    fetch(`/platforms/{{ $platform->id }}/hashtags/${endpoint}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
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
    
    if (mentions.length === 0) {
        resultsDiv.innerHTML = `
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                Nenhuma menção encontrada no ${type}
            </div>
        `;
        return;
    }
    
    let html = `
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i>
            ${mentions.length} menções encontradas no ${type}
        </div>
        <div class="list-group">
    `;
    
    mentions.slice(0, 5).forEach(mention => {
        const icon = type === 'instagram' ? 'bi-instagram' : 'bi-facebook';
        const username = mention.username || mention.from?.name || 'Usuário desconhecido';
        const content = mention.caption || mention.message || 'Sem conteúdo';
        const date = mention.timestamp || mention.created_time;
        
        html += `
            <div class="list-group-item">
                <div class="d-flex align-items-start">
                    <div class="me-2">
                        <i class="bi ${icon} text-primary"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">${username}</h6>
                        <p class="mb-1">${content.substring(0, 150)}${content.length > 150 ? '...' : ''}</p>
                        <small class="text-muted">${new Date(date).toLocaleDateString('pt-BR')}</small>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    
    if (mentions.length > 5) {
        html += `<p class="text-muted text-center mt-2">Mostrando 5 de ${mentions.length} menções.</p>`;
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
</script>
@endsection