@extends('layouts.app')

@section('title', 'Resolver Erro de Domínio - ' . $platform->name)

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h4><i class="fas fa-exclamation-triangle"></i> Erro de Domínio do Facebook - Código 1349048</h4>
            </div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-times-circle"></i> Erro Detectado:</h5>
                        <p>{{ session('error') }}</p>
                    </div>
                @endif

                <div class="alert alert-warning">
                    <h5><i class="fas fa-info-circle"></i> O que está acontecendo?</h5>
                    <p>O Facebook está rejeitando sua aplicação porque o domínio <strong>hashtag.betasolucao.com.br</strong> não está configurado corretamente no Facebook App.</p>
                    <p><strong>Mesmo que você tenha adicionado o domínio</strong>, há algumas configurações específicas que precisam ser verificadas.</p>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h5><i class="fas fa-cogs"></i> Checklist de Configuração</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="check1">
                                    <label class="form-check-label" for="check1">
                                        <strong>1. Domínios do App</strong><br>
                                        <small>Configurações → Básico → Domínios do app</small>
                                    </label>
                                    <div class="mt-2">
                                        <code>hashtag.betasolucao.com.br</code>
                                        <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('hashtag.betasolucao.com.br')">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="check2">
                                    <label class="form-check-label" for="check2">
                                        <strong>2. URL de Política de Privacidade</strong><br>
                                        <small>Configurações → Básico → URL da política de privacidade</small>
                                    </label>
                                    <div class="mt-2">
                                        <code>https://hashtag.betasolucao.com.br/privacidade</code>
                                        <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('https://hashtag.betasolucao.com.br/privacidade')">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="check3">
                                    <label class="form-check-label" for="check3">
                                        <strong>3. URI de Redirecionamento OAuth</strong><br>
                                        <small>Facebook Login → Configurações → URIs de redirecionamento válidos</small>
                                    </label>
                                    <div class="mt-2">
                                        <code>{{ $platform->redirect_uri }}</code>
                                        <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('{{ $platform->redirect_uri }}')">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="check4">
                                    <label class="form-check-label" for="check4">
                                        <strong>4. Status do App</strong><br>
                                        <small>O app deve estar em modo "Live" ou "Development"</small>
                                    </label>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="check5">
                                    <label class="form-check-label" for="check5">
                                        <strong>5. Salvar Alterações</strong><br>
                                        <small>Clique em "Salvar alterações" após cada configuração</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h5><i class="fas fa-tools"></i> Soluções Alternativas</h5>
                            </div>
                            <div class="card-body">
                                <h6>Opção 1: Configuração Detalhada</h6>
                                <ol>
                                    <li>Acesse <a href="https://developers.facebook.com/apps" target="_blank">Facebook Developers</a></li>
                                    <li>Selecione seu app (ID: <code>{{ $platform->app_id }}</code>)</li>
                                    <li>Vá em <strong>Configurações → Básico</strong></li>
                                    <li>Role até <strong>"Domínios do app"</strong></li>
                                    <li>Adicione: <code>hashtag.betasolucao.com.br</code></li>
                                    <li>Salve as alterações</li>
                                    <li>Aguarde 5-10 minutos para propagação</li>
                                </ol>

                                <hr>

                                <h6>Opção 2: Modo Desenvolvimento</h6>
                                <div class="alert alert-info">
                                    <p>Se o problema persistir, teste primeiro em modo desenvolvimento:</p>
                                    <ol>
                                        <li>Mude o app para modo "Development"</li>
                                        <li>Adicione seu usuário como desenvolvedor</li>
                                        <li>Teste a conexão</li>
                                        <li>Depois mude para "Live"</li>
                                    </ol>
                                </div>

                                <hr>

                                <h6>Opção 3: Recriar App</h6>
                                <div class="alert alert-warning">
                                    <p>Se nada funcionar, considere criar um novo Facebook App:</p>
                                    <button class="btn btn-outline-primary btn-sm" onclick="showNewAppInstructions()">
                                        <i class="fas fa-plus"></i> Instruções para Novo App
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h5><i class="fas fa-test-tube"></i> Testes e Validações</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <a href="https://developers.facebook.com/tools/debug/sharing/" target="_blank" class="btn btn-outline-primary w-100 mb-2">
                                            <i class="fas fa-external-link-alt"></i> Debug Tool
                                        </a>
                                        <small>Teste sua URL aqui</small>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="{{ route('platforms.test-callback', $platform) }}" target="_blank" class="btn btn-outline-success w-100 mb-2">
                                            <i class="fas fa-check"></i> Testar Callback
                                        </a>
                                        <small>Verifica se está acessível</small>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="{{ route('platforms.logs', $platform) }}" class="btn btn-outline-info w-100 mb-2">
                                            <i class="fas fa-file-alt"></i> Ver Logs
                                        </a>
                                        <small>Monitora tentativas</small>
                                    </div>
                                    <div class="col-md-3">
                                        <button class="btn btn-outline-warning w-100 mb-2" onclick="checkFacebookConfig()">
                                            <i class="fas fa-cogs"></i> Verificar Config
                                        </button>
                                        <small>Testa configurações automaticamente</small>
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
                        <button class="btn btn-warning" onclick="waitAndTryAgain()">
                            <i class="fas fa-clock"></i> Aguardar e Tentar (5 min)
                        </button>
                        <a href="{{ route('platforms.connect', $platform) }}" class="btn btn-success">
                            <i class="fab fa-facebook"></i> Tentar Conectar Agora
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para instruções de novo app -->
<div class="modal fade" id="newAppModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Criar Novo Facebook App</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ol>
                    <li>Acesse <a href="https://developers.facebook.com/apps" target="_blank">Facebook Developers</a></li>
                    <li>Clique em <strong>"Criar App"</strong></li>
                    <li>Escolha <strong>"Nenhum"</strong> ou <strong>"Outro"</strong></li>
                    <li>Digite um nome para o app</li>
                    <li>Após criar, vá em <strong>Configurações → Básico</strong></li>
                    <li>Configure:</li>
                    <ul>
                        <li><strong>Domínios do app:</strong> <code>hashtag.betasolucao.com.br</code></li>
                        <li><strong>URL da política de privacidade:</strong> <code>https://hashtag.betasolucao.com.br/privacidade</code></li>
                    </ul>
                    <li>Adicione o produto <strong>"Facebook Login"</strong></li>
                    <li>Configure a URI de redirecionamento: <code>{{ $platform->redirect_uri }}</code></li>
                    <li>Copie o novo App ID e App Secret</li>
                    <li>Atualize sua plataforma aqui no sistema com os novos dados</li>
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
        button.classList.remove('btn-outline-secondary');
        
        setTimeout(function() {
            button.innerHTML = originalHtml;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-secondary');
        }, 2000);
    });
}

function showNewAppInstructions() {
    new bootstrap.Modal(document.getElementById('newAppModal')).show();
}

function testDomainConnection() {
    fetch('https://hashtag.betasolucao.com.br')
        .then(response => {
            alert('Domínio está acessível! Status: ' + response.status);
        })
        .catch(error => {
            alert('Erro ao acessar domínio: ' + error.message);
        });
}

function checkFacebookConfig() {
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
            
            message += '\nRecomendações:\n';
            data.recommendations.forEach(rec => {
                message += `• ${rec}\n`;
            });
            
            alert(message);
        })
        .catch(error => {
            alert('Erro ao verificar configuração: ' + error.message);
        })
        .finally(() => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        });
}

function waitAndTryAgain() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    let countdown = 300; // 5 minutos em segundos
    
    btn.disabled = true;
    
    const interval = setInterval(function() {
        const minutes = Math.floor(countdown / 60);
        const seconds = countdown % 60;
        btn.innerHTML = `<i class="fas fa-clock"></i> Aguardando... ${minutes}:${seconds.toString().padStart(2, '0')}`;
        
        countdown--;
        
        if (countdown < 0) {
            clearInterval(interval);
            btn.innerHTML = originalText;
            btn.disabled = false;
            window.location.href = '{{ route('platforms.connect', $platform) }}';
        }
    }, 1000);
}
</script>
@endsection