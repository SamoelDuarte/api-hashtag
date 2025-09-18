@extends('layouts.app')

@section('title', 'Nova Plataforma')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4>Nova Plataforma de Rede Social</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('platforms.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome da Aplicação</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="type" class="form-label">Plataforma</label>
                        <select class="form-select @error('type') is-invalid @enderror" 
                                id="type" name="type" required>
                            <option value="">Selecione uma plataforma</option>
                            @foreach($types as $key => $label)
                                <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="app_id" class="form-label">App ID</label>
                        <input type="text" class="form-control @error('app_id') is-invalid @enderror" 
                               id="app_id" name="app_id" value="{{ old('app_id') }}" required>
                        @error('app_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">ID do aplicativo obtido na plataforma de desenvolvedores</div>
                    </div>

                    <div class="mb-3">
                        <label for="app_secret" class="form-label">App Secret</label>
                        <input type="password" class="form-control @error('app_secret') is-invalid @enderror" 
                               id="app_secret" name="app_secret" value="{{ old('app_secret') }}" required>
                        @error('app_secret')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Chave secreta do aplicativo</div>
                    </div>

                    <div class="mb-3">
                        <label for="redirect_uri" class="form-label">URL de Callback</label>
                        <input type="url" class="form-control @error('redirect_uri') is-invalid @enderror" 
                               id="redirect_uri" name="redirect_uri" 
                               value="{{ old('redirect_uri') }}" required readonly>
                        @error('redirect_uri')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">URL gerada automaticamente baseada na plataforma selecionada</div>
                    </div>

                    <div class="alert alert-info" id="platform-instructions" style="display: none;">
                        <h6><i class="fas fa-info-circle"></i> Instruções específicas:</h6>
                        <div id="facebook-instructions" style="display: none;">
                            <p><strong>Para Facebook/Instagram:</strong></p>
                            <ol>
                                <li>Acesse <a href="https://developers.facebook.com/" target="_blank">Facebook Developers</a></li>
                                <li>Crie um novo app ou use um existente</li>
                                <li>Adicione o produto "Facebook Login"</li>
                                <li>Configure a URL de redirecionamento abaixo</li>
                                <li>Copie o App ID e App Secret</li>
                            </ol>
                        </div>
                        <div id="youtube-instructions" style="display: none;">
                            <p><strong>Para YouTube:</strong></p>
                            <ol>
                                <li>Acesse <a href="https://console.developers.google.com/" target="_blank">Google Cloud Console</a></li>
                                <li>Crie um projeto ou selecione um existente</li>
                                <li>Ative a YouTube Data API v3</li>
                                <li>Configure OAuth 2.0 com a URL de callback abaixo</li>
                                <li>Copie o Client ID e Client Secret</li>
                            </ol>
                        </div>
                        <div id="tiktok-instructions" style="display: none;">
                            <p><strong>Para TikTok:</strong></p>
                            <ol>
                                <li>Acesse <a href="https://developers.tiktok.com/" target="_blank">TikTok for Developers</a></li>
                                <li>Crie uma aplicação Business</li>
                                <li>Configure as permissões necessárias</li>
                                <li>Adicione a URL de callback abaixo</li>
                                <li>Copie o App ID e App Secret</li>
                            </ol>
                        </div>
                    </div>

                    <div class="alert alert-warning" style="display: none;" id="callback-info">
                        <h6><i class="fas fa-link"></i> URL de Callback:</h6>
                        <p class="mb-0">
                            <strong>Configure esta URL</strong> na sua aplicação na plataforma:<br>
                            <code id="callback-url-display"></code>
                        </p>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('platforms.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Salvar Plataforma</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const redirectUriField = document.getElementById('redirect_uri');
    const platformInstructions = document.getElementById('platform-instructions');
    const callbackInfo = document.getElementById('callback-info');
    const callbackUrlDisplay = document.getElementById('callback-url-display');
    const baseUrl = '{{ url('/') }}';
    
    function updateRedirectUri() {
        const type = typeSelect.value;
        
        // Esconde todas as instruções primeiro
        document.querySelectorAll('[id$="-instructions"]').forEach(el => el.style.display = 'none');
        
        if (type) {
            // Gera URL específica para cada tipo de plataforma
            const callbackUrl = baseUrl + '/platforms/PLATFORM_ID/callback';
            redirectUriField.value = callbackUrl;
            
            // Mostra instruções específicas
            platformInstructions.style.display = 'block';
            callbackInfo.style.display = 'block';
            callbackUrlDisplay.textContent = callbackUrl;
            
            // Mostra instruções da plataforma específica
            const instructionsEl = document.getElementById(type + '-instructions');
            if (instructionsEl) {
                instructionsEl.style.display = 'block';
            }
        } else {
            redirectUriField.value = '';
            platformInstructions.style.display = 'none';
            callbackInfo.style.display = 'none';
        }
    }
    
    // Atualiza quando seleciona uma plataforma
    typeSelect.addEventListener('change', updateRedirectUri);
    
    // Atualiza na carga da página se já houver um tipo selecionado
    if (typeSelect.value) {
        updateRedirectUri();
    }
});
</script>
@endsection