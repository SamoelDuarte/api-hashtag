<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste SDK Facebook</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="text-center mb-5">
                    <h1 class="display-4">
                        <i class="bi bi-facebook text-primary"></i>
                        Teste SDK Facebook
                    </h1>
                    <p class="lead text-muted">Teste os diferentes métodos de descoberta de páginas</p>
                </div>

                <div class="row g-4">
                    <!-- Método Básico -->
                    <div class="col-md-4">
                        <div class="card h-100 border-primary">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-person-fill"></i> Método Básico
                                </h5>
                            </div>
                            <div class="card-body">
                                <p>Busca apenas páginas conectadas diretamente ao usuário.</p>
                                <div class="d-grid">
                                    <button class="btn btn-primary" onclick="testMethod('basic')">
                                        Testar Método Básico
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Método Completo -->
                    <div class="col-md-4">
                        <div class="card h-100 border-info">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-building"></i> Método Completo
                                </h5>
                            </div>
                            <div class="card-body">
                                <p>Busca páginas pessoais + Business Manager.</p>
                                <div class="d-grid">
                                    <button class="btn btn-info" onclick="testMethod('complete')">
                                        Testar Método Completo
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SDK Oficial -->
                    <div class="col-md-4">
                        <div class="card h-100 border-success">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-gear-wide-connected"></i> SDK Oficial
                                </h5>
                            </div>
                            <div class="card-body">
                                <p>Usa o SDK oficial do Facebook PHP v5.1.4 (Recomendado).</p>
                                <div class="d-grid">
                                    <button class="btn btn-success" onclick="testMethod('sdk')">
                                        <strong>Testar SDK</strong>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Área de Resultados -->
                <div class="row mt-5">
                    <div class="col-12">
                        <div id="results-area" style="display: none;">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-list-check"></i> Resultados
                                    </h5>
                                </div>
                                <div class="card-body" id="results-content">
                                    <!-- Resultados aparecerão aqui -->
                                </div>
                            </div>
                        </div>

                        <div id="loading-area" style="display: none;">
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Testando...</span>
                                </div>
                                <p class="mt-3">Executando teste...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- URLs Disponíveis -->
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-link-45deg"></i> URLs de Teste Disponíveis
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Interface Principal:</h6>
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item">
                                                <code>http://localhost/platforms/1/hashtags</code>
                                                <br><small class="text-muted">Interface completa de hashtags</small>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>APIs Diretas:</h6>
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item">
                                                <code>http://localhost/platforms/1/hashtags/accounts</code>
                                                <br><small class="text-muted">Método básico</small>
                                            </li>
                                            <li class="list-group-item">
                                                <code>http://localhost/platforms/1/hashtags/accounts-complete</code>
                                                <br><small class="text-muted">Método completo</small>
                                            </li>
                                            <li class="list-group-item">
                                                <code>http://localhost/platforms/1/hashtags/accounts-sdk</code>
                                                <br><small class="text-muted"><strong>SDK Oficial (Novo!)</strong></small>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function testMethod(method) {
            const loadingArea = document.getElementById('loading-area');
            const resultsArea = document.getElementById('results-area');
            const resultsContent = document.getElementById('results-content');
            
            // Mostrar loading
            resultsArea.style.display = 'none';
            loadingArea.style.display = 'block';
            
            // Definir URL baseado no método
            let url;
            let methodName;
            
            switch(method) {
                case 'basic':
                    url = '/platforms/1/hashtags/accounts';
                    methodName = 'Método Básico';
                    break;
                case 'complete':
                    url = '/platforms/1/hashtags/accounts-complete';
                    methodName = 'Método Completo';
                    break;
                case 'sdk':
                    url = '/platforms/1/hashtags/accounts-sdk';
                    methodName = 'SDK Oficial Facebook';
                    break;
            }
            
            // Fazer requisição
            fetch(url)
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    displayResults(data, methodName, url);
                })
                .catch(error => {
                    console.error('Error:', error);
                    displayError(error, methodName, url);
                })
                .finally(() => {
                    loadingArea.style.display = 'none';
                    resultsArea.style.display = 'block';
                });
        }
        
        function displayResults(data, methodName, url) {
            const resultsContent = document.getElementById('results-content');
            
            if (data.success) {
                const pages = data.data.pages || [];
                let html = `
                    <div class="alert alert-success">
                        <h6><i class="bi bi-check-circle"></i> ${methodName} - Sucesso!</h6>
                        <p>URL: <code>${url}</code></p>
                        <p>${data.message || 'Páginas carregadas com sucesso'}</p>
                    </div>
                `;
                
                if (pages.length > 0) {
                    html += `<h6>Páginas Encontradas (${pages.length}):</h6><div class="row">`;
                    
                    pages.forEach(page => {
                        const hasInstagram = page.instagram_business_account;
                        const sourceIcon = page.source === 'business' ? 'bi-building' : 'bi-person-fill';
                        const sourceText = page.source === 'business' ? 'Business Manager' : 'Página Pessoal';
                        
                        html += `
                            <div class="col-md-6 mb-3">
                                <div class="card ${hasInstagram ? 'border-success' : 'border-warning'}">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title mb-0">${page.name}</h6>
                                            <small class="text-muted">
                                                <i class="bi ${sourceIcon}"></i> ${sourceText}
                                            </small>
                                        </div>
                                        <p class="text-muted small mb-1">ID: ${page.id}</p>
                                        ${page.business_name ? `<p class="text-info small mb-2"><i class="bi bi-building"></i> ${page.business_name}</p>` : ''}
                                        
                                        <div class="mt-2">
                                            ${hasInstagram ? 
                                                `<span class="badge bg-success">
                                                    <i class="bi bi-instagram"></i> Instagram: ${page.instagram_business_account.id}
                                                </span>` : 
                                                `<span class="badge bg-warning">
                                                    <i class="bi bi-exclamation-triangle"></i> Sem Instagram Business
                                                </span>`
                                            }
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                    });
                    
                    html += '</div>';
                } else {
                    html += `<div class="alert alert-warning">Nenhuma página encontrada.</div>`;
                }
                
                // Mostrar debug se disponível
                if (data.debug) {
                    html += `
                        <div class="mt-3">
                            <button class="btn btn-outline-info btn-sm" onclick="toggleDebug()">
                                <i class="bi bi-code-slash"></i> Mostrar/Ocultar Debug
                            </button>
                            <div id="debug-info" style="display: none;" class="mt-2">
                                <h6>Informações de Debug:</h6>
                                <pre class="bg-light p-3 rounded small">${JSON.stringify(data.debug, null, 2)}</pre>
                            </div>
                        </div>
                    `;
                }
                
                resultsContent.innerHTML = html;
            } else {
                displayError({ message: data.error }, methodName, url);
            }
        }
        
        function displayError(error, methodName, url) {
            const resultsContent = document.getElementById('results-content');
            
            resultsContent.innerHTML = `
                <div class="alert alert-danger">
                    <h6><i class="bi bi-exclamation-triangle"></i> ${methodName} - Erro!</h6>
                    <p>URL: <code>${url}</code></p>
                    <p><strong>Erro:</strong> ${error.message}</p>
                    <button class="btn btn-sm btn-outline-primary" onclick="testMethod('${methodName.toLowerCase().includes('sdk') ? 'sdk' : methodName.toLowerCase().includes('completo') ? 'complete' : 'basic'}')">
                        Tentar Novamente
                    </button>
                </div>
            `;
        }
        
        function toggleDebug() {
            const debugInfo = document.getElementById('debug-info');
            debugInfo.style.display = debugInfo.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>