@extends('layouts.app')

@section('title', 'Dashboard de Monitoramento - ' . $platform->name)

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="bi bi-speedometer2"></i>
                        Dashboard de Monitoramento
                    </h1>
                    <p class="text-muted mb-0">{{ $platform->name }} - Visão geral das hashtags e menções</p>
                </div>
                <div>
                    <a href="{{ route('hashtags.index', $platform) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                    <button class="btn btn-primary" onclick="refreshDashboard()">
                        <i class="bi bi-arrow-clockwise"></i> Atualizar
                    </button>
                </div>
            </div>

            <!-- Métricas Principais -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card border-0 bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="bi bi-instagram fs-2"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0" id="instagram-posts-count">0</h3>
                                    <small>Posts Monitorados</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="bi bi-at fs-2"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0" id="mentions-count">0</h3>
                                    <small>Menções Encontradas</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="bi bi-hash fs-2"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0" id="hashtags-monitored">0</h3>
                                    <small>Hashtags Monitoradas</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="bi bi-clock fs-2"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0" id="last-update">-</h3>
                                    <small>Última Atualização</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monitoramento Rápido -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-lightning"></i>
                                Monitoramento Rápido
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($accountData && isset($accountData['pages']))
                                <div class="row">
                                    @foreach($accountData['pages'] as $page)
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-0 bg-light">
                                                <div class="card-body">
                                                    <h6 class="mb-2">{{ $page['name'] }}</h6>
                                                    
                                                    @if(isset($page['instagram_business_account']))
                                                        <div class="d-grid gap-2">
                                                            <button class="btn btn-sm btn-outline-primary" 
                                                                    onclick="quickHashtagSearch('{{ $page['instagram_business_account']['id'] }}', '{{ $page['name'] }}')">
                                                                <i class="bi bi-search"></i> Buscar Hashtag
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-success" 
                                                                    onclick="quickMentions('{{ $page['instagram_business_account']['id'] }}', '{{ $page['name'] }}', 'instagram')">
                                                                <i class="bi bi-at"></i> Ver Menções Instagram
                                                            </button>
                                                        </div>
                                                    @endif
                                                    
                                                    <button class="btn btn-sm btn-outline-info mt-2" 
                                                            onclick="quickMentions('{{ $page['id'] }}', '{{ $page['name'] }}', 'facebook')">
                                                        <i class="bi bi-facebook"></i> Ver Menções Facebook
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="bi bi-info-circle text-muted fs-2"></i>
                                    <p class="text-muted mt-2">Nenhuma conta configurada</p>
                                    <a href="{{ route('hashtags.index', $platform) }}" class="btn btn-primary">
                                        <i class="bi bi-gear"></i> Configurar Contas
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resultados em Tempo Real -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-activity"></i>
                                Resultados em Tempo Real
                            </h5>
                            <div>
                                <button class="btn btn-sm btn-outline-secondary" onclick="clearResults()">
                                    <i class="bi bi-trash"></i> Limpar
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="real-time-results">
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-graph-up fs-1"></i>
                                    <p class="mt-3">Os resultados das buscas aparecerão aqui em tempo real</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Busca Rápida de Hashtag -->
<div class="modal fade" id="quickHashtagModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Busca Rápida de Hashtag</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quick-hashtag-form">
                    <div class="mb-3">
                        <label class="form-label">Conta</label>
                        <input type="text" class="form-control" id="quick-account-name" readonly>
                        <input type="hidden" id="quick-instagram-id">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hashtag</label>
                        <div class="input-group">
                            <span class="input-group-text">#</span>
                            <input type="text" class="form-control" id="quick-hashtag" placeholder="minhahashtag" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="executeQuickSearch()">
                    <i class="bi bi-search"></i> Buscar
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
let dashboardData = {
    platform: {{ $platform->id }},
    metrics: {
        posts: 0,
        mentions: 0,
        hashtags: 0,
        lastUpdate: null
    }
};

// Inicializar dashboard
document.addEventListener('DOMContentLoaded', function() {
    updateMetrics();
    startAutoRefresh();
});

// Atualizar métricas
function updateMetrics() {
    // Simular dados (em um app real, viria da API)
    document.getElementById('instagram-posts-count').textContent = dashboardData.metrics.posts;
    document.getElementById('mentions-count').textContent = dashboardData.metrics.mentions;
    document.getElementById('hashtags-monitored').textContent = dashboardData.metrics.hashtags;
    
    const lastUpdate = dashboardData.metrics.lastUpdate || new Date();
    document.getElementById('last-update').textContent = formatTime(lastUpdate);
}

// Busca rápida de hashtag
function quickHashtagSearch(instagramId, accountName) {
    document.getElementById('quick-account-name').value = accountName;
    document.getElementById('quick-instagram-id').value = instagramId;
    document.getElementById('quick-hashtag').value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('quickHashtagModal'));
    modal.show();
}

// Executar busca rápida
function executeQuickSearch() {
    const instagramId = document.getElementById('quick-instagram-id').value;
    const hashtag = document.getElementById('quick-hashtag').value;
    
    if (!hashtag) {
        showAlert('Digite uma hashtag', 'warning');
        return;
    }
    
    // Fechar modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('quickHashtagModal'));
    modal.hide();
    
    // Executar busca
    addLoadingResult('hashtag', hashtag);
    
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
        removeLoadingResult();
        if (data.success) {
            addHashtagResult(data);
            updateMetricsFromResult('hashtag', data);
        } else {
            addErrorResult('hashtag', hashtag, data.error);
        }
    })
    .catch(error => {
        removeLoadingResult();
        addErrorResult('hashtag', hashtag, error);
    });
}

// Menções rápidas
function quickMentions(accountId, accountName, type) {
    addLoadingResult('mentions', `${type} - ${accountName}`);
    
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
        removeLoadingResult();
        if (data.success) {
            addMentionsResult(data, type, accountName);
            updateMetricsFromResult('mentions', data);
        } else {
            addErrorResult('mentions', `${type} - ${accountName}`, data.error);
        }
    })
    .catch(error => {
        removeLoadingResult();
        addErrorResult('mentions', `${type} - ${accountName}`, error);
    });
}

// Adicionar resultado de loading
function addLoadingResult(type, query) {
    const container = document.getElementById('real-time-results');
    
    // Remover mensagem de placeholder se existir
    if (container.innerHTML.includes('Os resultados das buscas')) {
        container.innerHTML = '';
    }
    
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'loading-result mb-3';
    loadingDiv.innerHTML = `
        <div class="card border-primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="spinner-border spinner-border-sm text-primary me-3" role="status"></div>
                    <div>
                        <h6 class="mb-0">Buscando ${type}: ${query}</h6>
                        <small class="text-muted">Aguarde...</small>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.insertBefore(loadingDiv, container.firstChild);
}

// Remover resultado de loading
function removeLoadingResult() {
    const loading = document.querySelector('.loading-result');
    if (loading) {
        loading.remove();
    }
}

// Adicionar resultado de hashtag
function addHashtagResult(data) {
    const container = document.getElementById('real-time-results');
    
    const resultDiv = document.createElement('div');
    resultDiv.className = 'result-item mb-3';
    resultDiv.innerHTML = `
        <div class="card border-success">
            <div class="card-header bg-success text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-hash"></i>
                        Hashtag: #${data.hashtag}
                    </div>
                    <small>${formatTime(new Date())}</small>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h6 class="text-success">${data.total_posts} posts encontrados</h6>
                        ${data.posts.slice(0, 3).map(post => `
                            <div class="border-start border-2 border-success ps-3 mb-2">
                                <strong>@${post.username}</strong>
                                <p class="small mb-1">${post.caption ? post.caption.substring(0, 100) + '...' : 'Sem legenda'}</p>
                                <a href="${post.permalink}" target="_blank" class="btn btn-sm btn-outline-primary">Ver Post</a>
                            </div>
                        `).join('')}
                        ${data.posts.length > 3 ? `<small class="text-muted">E mais ${data.posts.length - 3} posts...</small>` : ''}
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="bg-light rounded p-3">
                                <h2 class="text-success mb-0">${data.total_posts}</h2>
                                <small class="text-muted">Posts Totais</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.insertBefore(resultDiv, container.firstChild);
    
    // Limitar a 10 resultados
    const results = container.querySelectorAll('.result-item');
    if (results.length > 10) {
        results[results.length - 1].remove();
    }
}

// Adicionar resultado de menções
function addMentionsResult(data, type, accountName) {
    const container = document.getElementById('real-time-results');
    const mentions = type === 'instagram' ? data.mentions : data.tagged_posts;
    const icon = type === 'instagram' ? 'bi-instagram' : 'bi-facebook';
    
    const resultDiv = document.createElement('div');
    resultDiv.className = 'result-item mb-3';
    resultDiv.innerHTML = `
        <div class="card border-info">
            <div class="card-header bg-info text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi ${icon}"></i>
                        Menções: ${accountName} (${type})
                    </div>
                    <small>${formatTime(new Date())}</small>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h6 class="text-info">${mentions.length} menções encontradas</h6>
                        ${mentions.slice(0, 3).map(mention => {
                            const username = mention.username || mention.from?.name || 'Usuário desconhecido';
                            const content = mention.caption || mention.message || 'Sem conteúdo';
                            return `
                                <div class="border-start border-2 border-info ps-3 mb-2">
                                    <strong>${username}</strong>
                                    <p class="small mb-1">${content.substring(0, 100)}${content.length > 100 ? '...' : ''}</p>
                                    <small class="text-muted">${new Date(mention.timestamp || mention.created_time).toLocaleDateString('pt-BR')}</small>
                                </div>
                            `;
                        }).join('')}
                        ${mentions.length > 3 ? `<small class="text-muted">E mais ${mentions.length - 3} menções...</small>` : ''}
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="bg-light rounded p-3">
                                <h2 class="text-info mb-0">${mentions.length}</h2>
                                <small class="text-muted">Menções Totais</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.insertBefore(resultDiv, container.firstChild);
    
    // Limitar a 10 resultados
    const results = container.querySelectorAll('.result-item');
    if (results.length > 10) {
        results[results.length - 1].remove();
    }
}

// Adicionar resultado de erro
function addErrorResult(type, query, error) {
    const container = document.getElementById('real-time-results');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'result-item mb-3';
    errorDiv.innerHTML = `
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-exclamation-triangle"></i>
                        Erro: ${type} - ${query}
                    </div>
                    <small>${formatTime(new Date())}</small>
                </div>
            </div>
            <div class="card-body">
                <p class="text-danger mb-0">${error}</p>
            </div>
        </div>
    `;
    
    container.insertBefore(errorDiv, container.firstChild);
}

// Atualizar métricas a partir do resultado
function updateMetricsFromResult(type, data) {
    if (type === 'hashtag') {
        dashboardData.metrics.posts += data.total_posts;
        dashboardData.metrics.hashtags += 1;
    } else if (type === 'mentions') {
        const mentions = data.mentions || data.tagged_posts;
        dashboardData.metrics.mentions += mentions.length;
    }
    
    dashboardData.metrics.lastUpdate = new Date();
    updateMetrics();
}

// Limpar resultados
function clearResults() {
    const container = document.getElementById('real-time-results');
    container.innerHTML = `
        <div class="text-center py-5 text-muted">
            <i class="bi bi-graph-up fs-1"></i>
            <p class="mt-3">Os resultados das buscas aparecerão aqui em tempo real</p>
        </div>
    `;
    
    // Reset das métricas
    dashboardData.metrics = {
        posts: 0,
        mentions: 0,
        hashtags: 0,
        lastUpdate: null
    };
    updateMetrics();
}

// Atualizar dashboard
function refreshDashboard() {
    showAlert('Dashboard atualizado!', 'success');
    updateMetrics();
}

// Auto-refresh a cada 5 minutos
function startAutoRefresh() {
    setInterval(() => {
        updateMetrics();
    }, 5 * 60 * 1000);
}

// Formatar tempo
function formatTime(date) {
    return date.toLocaleTimeString('pt-BR', { 
        hour: '2-digit', 
        minute: '2-digit' 
    });
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
    
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}
</script>
@endsection