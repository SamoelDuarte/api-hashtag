@extends('layouts.app')

@section('title', 'Configuração Facebook - ' . $platform->name)

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4><i class="fab fa-facebook"></i> Configuração do Facebook App - {{ $platform->name }}</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <h5><i class="fas fa-exclamation-triangle"></i> URI de Redirecionamento Inválida</h5>
                    <p>O Facebook está rejeitando sua URI de redirecionamento. Siga os passos abaixo para resolver:</p>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h5><i class="fas fa-clipboard-list"></i> Passo a Passo</h5>
                            </div>
                            <div class="card-body">
                                <ol class="mb-0">
                                    <li class="mb-3">
                                        <strong>Acesse o Facebook Developers:</strong><br>
                                        <a href="https://developers.facebook.com/apps" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-external-link-alt"></i> Abrir Facebook Developers
                                        </a>
                                    </li>
                                    
                                    <li class="mb-3">
                                        <strong>Encontre seu App:</strong><br>
                                        App ID: <code>{{ $platform->app_id }}</code>
                                        <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('{{ $platform->app_id }}')">
                                            <i class="fas fa-copy"></i> Copiar
                                        </button>
                                    </li>
                                    
                                    <li class="mb-3">
                                        <strong>Vá para:</strong> Produtos → Facebook Login → Configurações
                                    </li>
                                    
                                    <li class="mb-3">
                                        <strong>Procure por:</strong> "URIs de redirecionamento válidos do OAuth"
                                    </li>
                                    
                                    <li class="mb-3">
                                        <strong>Adicione as URLs abaixo</strong> (copie exatamente como estão)
                                    </li>
                                    
                                    <li class="mb-3">
                                        <strong>Salve as configurações</strong>
                                    </li>
                                    
                                    <li>
                                        <strong>Teste a conexão novamente</strong>
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h5><i class="fas fa-link"></i> URLs para Adicionar</h5>
                            </div>
                            <div class="card-body">
                                <h6>URIs de Redirecionamento OAuth:</h6>
                                <div class="mb-3">
                                    <div class="input-group">
                                        <input type="text" class="form-control" value="{{ $platform->redirect_uri }}" readonly id="callback-url">
                                        <button class="btn btn-outline-secondary" onclick="copyToClipboard('{{ $platform->redirect_uri }}')">
                                            <i class="fas fa-copy"></i> Copiar
                                        </button>
                                    </div>
                                    <small class="text-muted">URL atual da sua plataforma</small>
                                </div>

                                @if(strpos($platform->redirect_uri, 'http://') === 0)
                                <div class="mb-3">
                                    <div class="input-group">
                                        <input type="text" class="form-control" value="{{ str_replace('http://', 'https://', $platform->redirect_uri) }}" readonly>
                                        <button class="btn btn-outline-secondary" onclick="copyToClipboard('{{ str_replace('http://', 'https://', $platform->redirect_uri) }}')">
                                            <i class="fas fa-copy"></i> Copiar
                                        </button>
                                    </div>
                                    <small class="text-success">Versão HTTPS (recomendada)</small>
                                </div>
                                @endif

                                <hr>

                                <h6>Domínios do App:</h6>
                                <div class="mb-3">
                                    <div class="input-group">
                                        <input type="text" class="form-control" value="hashtag.betasolucao.com.br" readonly>
                                        <button class="btn btn-outline-secondary" onclick="copyToClipboard('hashtag.betasolucao.com.br')">
                                            <i class="fas fa-copy"></i> Copiar
                                        </button>
                                    </div>
                                </div>

                                <hr>

                                <h6>URL de Política de Privacidade:</h6>
                                <div class="mb-3">
                                    <div class="input-group">
                                        <input type="text" class="form-control" value="{{ str_replace('http://', 'https://', url('/privacidade')) }}" readonly>
                                        <button class="btn btn-outline-secondary" onclick="copyToClipboard('{{ str_replace('http://', 'https://', url('/privacidade')) }}')">
                                            <i class="fas fa-copy"></i> Copiar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h5><i class="fas fa-tools"></i> Ferramentas de Teste</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <h6>Testar Configuração:</h6>
                                        <a href="https://developers.facebook.com/tools/debug/sharing/" target="_blank" class="btn btn-outline-primary w-100 mb-2">
                                            <i class="fas fa-external-link-alt"></i> Debug do Facebook
                                        </a>
                                        <p><small>Cole sua URL de callback aqui para testar</small></p>
                                    </div>
                                    <div class="col-md-4">
                                        <h6>Validar URI:</h6>
                                        <button class="btn btn-outline-info w-100 mb-2" onclick="validateCallback()">
                                            <i class="fas fa-check-circle"></i> Validar Callback
                                        </button>
                                        <p><small>Testa se a URL está acessível</small></p>
                                    </div>
                                    <div class="col-md-4">
                                        <h6>Testar Endpoint:</h6>
                                        <a href="{{ route('platforms.test-callback', $platform) }}" target="_blank" class="btn btn-outline-warning w-100 mb-2">
                                            <i class="fas fa-vial"></i> Testar URL
                                        </a>
                                        <p><small>Abre a URL em nova aba</small></p>
                                    </div>
                                    <div class="col-md-4">
                                        <h6>Ver Logs:</h6>
                                        <a href="{{ route('platforms.logs', $platform) }}" class="btn btn-outline-secondary w-100 mb-2">
                                            <i class="fas fa-file-alt"></i> Ver Logs de Debug
                                        </a>
                                        <p><small>Veja o que está acontecendo</small></p>
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
                        <a href="{{ route('platforms.debug', $platform) }}" class="btn btn-info">
                            <i class="fas fa-bug"></i> Debug Completo
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

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Mostrar feedback visual
        const button = event.target.closest('button');
        const originalHtml = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i> Copiado!';
        button.classList.add('btn-success');
        button.classList.remove('btn-outline-secondary');
        
        setTimeout(function() {
            button.innerHTML = originalHtml;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-secondary');
        }, 2000);
    });
}

function validateCallback() {
    const url = '{{ $platform->redirect_uri }}';
    
    // Fazer uma requisição para testar se a URL está acessível
    fetch(url + '?test=1')
        .then(response => {
            alert('URL está acessível! Status: ' + response.status);
        })
        .catch(error => {
            alert('Erro ao acessar URL: ' + error.message);
        });
}
</script>
@endsection