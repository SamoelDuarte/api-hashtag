@extends('layouts.app')

@section('title', 'Resolver URI Inválida - ' . $platform->name)

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h4><i class="fas fa-exclamation-triangle"></i> URI de Redirecionamento Inválida no Facebook</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-danger">
                    <h5><i class="fas fa-times-circle"></i> Problema Detectado:</h5>
                    <p>O Facebook está rejeitando sua URI mesmo no validador. Isso significa que ela não está configurada corretamente na lista de URIs válidas.</p>
                    <p><strong>URI Rejeitada:</strong> <code>{{ $platform->redirect_uri }}</code></p>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h5><i class="fas fa-tools"></i> Solução Passo-a-Passo</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle"></i> IMPORTANTE:</h6>
                                    <p>O Facebook tem regras específicas para URIs de redirecionamento. Vamos verificar e corrigir cada ponto:</p>
                                </div>

                                <div class="accordion" id="solutionAccordion">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#step1">
                                                <strong>Passo 1: Verificar Localização Correta no Facebook</strong>
                                            </button>
                                        </h2>
                                        <div id="step1" class="accordion-collapse collapse show">
                                            <div class="accordion-body">
                                                <ol>
                                                    <li>Acesse <a href="https://developers.facebook.com/apps" target="_blank"><strong>Facebook Developers</strong></a></li>
                                                    <li>Clique no seu app (ID: <code>{{ $platform->app_id }}</code>)</li>
                                                    <li>Na barra lateral esquerda, clique em <strong>"Produtos"</strong></li>
                                                    <li>Procure por <strong>"Facebook Login"</strong> e clique nele</li>
                                                    <li>No submenu que aparece, clique em <strong>"Configurações"</strong></li>
                                                    <li>Role a página até encontrar <strong>"URIs de redirecionamento válidos do OAuth"</strong></li>
                                                </ol>
                                                
                                                <div class="alert alert-success">
                                                    <strong>🎯 Local correto encontrado?</strong> Continue para o próximo passo.
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step2">
                                                <strong>Passo 2: Adicionar a URI Corretamente</strong>
                                            </button>
                                        </h2>
                                        <div id="step2" class="accordion-collapse collapse">
                                            <div class="accordion-body">
                                                <p><strong>Na seção "URIs de redirecionamento válidos do OAuth":</strong></p>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label"><strong>URI Principal (copie exatamente):</strong></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" value="{{ $platform->redirect_uri }}" readonly id="main-uri">
                                                        <button class="btn btn-outline-primary" onclick="copyToClipboard('{{ $platform->redirect_uri }}')">
                                                            <i class="fas fa-copy"></i> Copiar
                                                        </button>
                                                    </div>
                                                </div>

                                                @if(strpos($platform->redirect_uri, 'http://') === 0)
                                                <div class="mb-3">
                                                    <label class="form-label"><strong>URI HTTPS (recomendada):</strong></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" value="{{ str_replace('http://', 'https://', $platform->redirect_uri) }}" readonly>
                                                        <button class="btn btn-outline-primary" onclick="copyToClipboard('{{ str_replace('http://', 'https://', $platform->redirect_uri) }}')">
                                                            <i class="fas fa-copy"></i> Copiar
                                                        </button>
                                                    </div>
                                                </div>
                                                @endif

                                                <div class="alert alert-warning">
                                                    <h6><i class="fas fa-exclamation-triangle"></i> ATENÇÃO:</h6>
                                                    <ul>
                                                        <li>Cole a URI no campo sem adicionar espaços antes ou depois</li>
                                                        <li>Certifique-se de que não há quebras de linha</li>
                                                        <li>Pressione Enter após colar para adicionar à lista</li>
                                                        <li><strong>Clique em "Salvar alterações"</strong> no final da página</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step3">
                                                <strong>Passo 3: Configurações Obrigatórias do App</strong>
                                            </button>
                                        </h2>
                                        <div id="step3" class="accordion-collapse collapse">
                                            <div class="accordion-body">
                                                <p><strong>Antes de testar a URI, configure estas informações obrigatórias:</strong></p>
                                                
                                                <h6>1. Configurações → Básico:</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <tr>
                                                            <td><strong>Domínios do app</strong></td>
                                                            <td>
                                                                <code>hashtag.betasolucao.com.br</code>
                                                                <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('hashtag.betasolucao.com.br')">
                                                                    <i class="fas fa-copy"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>URL da política de privacidade</strong></td>
                                                            <td>
                                                                <code>https://hashtag.betasolucao.com.br/privacidade</code>
                                                                <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('https://hashtag.betasolucao.com.br/privacidade')">
                                                                    <i class="fas fa-copy"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Categoria do app</strong></td>
                                                            <td>Selecione uma categoria (ex: Business)</td>
                                                        </tr>
                                                    </table>
                                                </div>

                                                <div class="alert alert-danger">
                                                    <strong>🚨 CRÍTICO:</strong> O Facebook só aceita URIs de redirecionamento se as configurações básicas estiverem completas!
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step4">
                                                <strong>Passo 4: Verificação e Teste</strong>
                                            </button>
                                        </h2>
                                        <div id="step4" class="accordion-collapse collapse">
                                            <div class="accordion-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h6>Verificar no Facebook:</h6>
                                                        <ol>
                                                            <li>Após salvar tudo, volte ao validador do Facebook</li>
                                                            <li>Cole a URI novamente: <code class="text-break">{{ $platform->redirect_uri }}</code></li>
                                                            <li>Clique em "Verificar URI"</li>
                                                            <li>Deve aparecer ✅ em verde</li>
                                                        </ol>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6>Testar Aqui:</h6>
                                                        <button class="btn btn-outline-primary w-100 mb-2" onclick="testFacebookValidator()">
                                                            <i class="fas fa-external-link-alt"></i> Abrir Validador do Facebook
                                                        </button>
                                                        <button class="btn btn-outline-success w-100 mb-2" onclick="testCallback()">
                                                            <i class="fas fa-check"></i> Testar Callback Local
                                                        </button>
                                                        <button class="btn btn-outline-info w-100 mb-2" onclick="checkAllConfigs()">
                                                            <i class="fas fa-cogs"></i> Verificar Todas Configs
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h5><i class="fas fa-lightbulb"></i> Alternativas se Não Funcionar</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="card border-warning h-100">
                                            <div class="card-header bg-warning text-dark">
                                                <strong>Opção 1: App Development</strong>
                                            </div>
                                            <div class="card-body">
                                                <p>Mude o app para modo desenvolvimento:</p>
                                                <ol>
                                                    <li>Configurações → Básico</li>
                                                    <li>Status do app → Development</li>
                                                    <li>Adicione-se como desenvolvedor</li>
                                                    <li>Teste a conexão</li>
                                                </ol>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border-primary h-100">
                                            <div class="card-header bg-primary text-white">
                                                <strong>Opção 2: Novo App</strong>
                                            </div>
                                            <div class="card-body">
                                                <p>Criar app do zero:</p>
                                                <button class="btn btn-outline-primary w-100" onclick="showNewAppGuide()">
                                                    <i class="fas fa-plus"></i> Guia Novo App
                                                </button>
                                                <p><small>Às vezes é mais rápido criar um novo</small></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border-success h-100">
                                            <div class="card-header bg-success text-white">
                                                <strong>Opção 3: Suporte Facebook</strong>
                                            </div>
                                            <div class="card-body">
                                                <p>Contatar suporte:</p>
                                                <a href="https://developers.facebook.com/support/" target="_blank" class="btn btn-outline-success w-100">
                                                    <i class="fas fa-external-link-alt"></i> Suporte Facebook
                                                </a>
                                                <p><small>Para casos complexos</small></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('platforms.show', $platform) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                    <div class="d-flex gap-2">
                        <a href="{{ route('platforms.logs', $platform) }}" class="btn btn-outline-info">
                            <i class="fas fa-file-alt"></i> Ver Logs
                        </a>
                        <a href="{{ route('platforms.connect', $platform) }}" class="btn btn-success">
                            <i class="fab fa-facebook"></i> Tentar Conectar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para guia de novo app -->
<div class="modal fade" id="newAppGuideModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Criar Novo Facebook App</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <p><strong>Por que criar um novo app?</strong></p>
                    <p>Às vezes o Facebook "trava" configurações antigas. Um app novo resolve isso.</p>
                </div>
                
                <h6>Passos para criar novo app:</h6>
                <ol>
                    <li>Acesse <a href="https://developers.facebook.com/apps" target="_blank">Facebook Developers</a></li>
                    <li>Clique em <strong>"Criar App"</strong></li>
                    <li>Escolha <strong>"Outro"</strong></li>
                    <li>Tipo: <strong>"Business"</strong></li>
                    <li>Nome: <strong>"API Hashtag"</strong> (ou similar)</li>
                    <li>Depois de criar, configure imediatamente:</li>
                </ol>
                
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <tr><td><strong>Domínios do app</strong></td><td><code>hashtag.betasolucao.com.br</code></td></tr>
                        <tr><td><strong>Política de privacidade</strong></td><td><code>https://hashtag.betasolucao.com.br/privacidade</code></td></tr>
                        <tr><td><strong>Categoria</strong></td><td>Business</td></tr>
                    </table>
                </div>
                
                <ol start="7">
                    <li>Adicione produto <strong>"Facebook Login"</strong></li>
                    <li>Configure URI: <code class="text-break">{{ $platform->redirect_uri }}</code></li>
                    <li>Copie o novo App ID e Secret</li>
                    <li>Atualize sua plataforma aqui no sistema</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        const button = event.target.closest('button');
        const originalHtml = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i> OK';
        button.classList.add('btn-success');
        button.classList.remove('btn-outline-secondary', 'btn-outline-primary');
        
        setTimeout(function() {
            button.innerHTML = originalHtml;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-secondary');
        }, 2000);
    });
}

function testFacebookValidator() {
    const url = 'https://developers.facebook.com/tools/debug/sharing/';
    window.open(url, '_blank');
    
    setTimeout(() => {
        if (confirm('Cole esta URI no validador do Facebook:\n\n{{ $platform->redirect_uri }}\n\nCopiar URI para área de transferência?')) {
            copyToClipboard('{{ $platform->redirect_uri }}');
        }
    }, 2000);
}

function testCallback() {
    window.open('{{ route('platforms.test-callback', $platform) }}', '_blank');
}

function checkAllConfigs() {
    const btn = event.target;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';
    btn.disabled = true;
    
    fetch('{{ route('platforms.check-facebook-config', $platform) }}')
        .then(response => response.json())
        .then(data => {
            let message = 'Resultados da Verificação:\n\n';
            
            Object.keys(data.messages).forEach(key => {
                const status = data.checks[key] ? '✅' : '❌';
                message += `${status} ${data.messages[key]}\n`;
            });
            
            message += '\n📋 Status Geral: ' + (data.overall_status === 'success' ? '✅ OK' : '⚠️ Problemas encontrados');
            message += '\n\n💡 Recomendações:\n';
            data.recommendations.forEach(rec => {
                message += `• ${rec}\n`;
            });
            
            alert(message);
        })
        .catch(error => {
            alert('Erro ao verificar: ' + error.message);
        })
        .finally(() => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        });
}

function showNewAppGuide() {
    new bootstrap.Modal(document.getElementById('newAppGuideModal')).show();
}
</script>
@endsection