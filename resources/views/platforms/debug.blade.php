@extends('layouts.app')

@section('title', 'Debug OAuth - ' . $platform->name)

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-bug"></i> Informações de Debug - {{ $platform->name }}</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Configurações Atuais:</h5>
                        <table class="table table-bordered">
                            <tr>
                                <td><strong>App ID:</strong></td>
                                <td><code>{{ $platform->app_id }}</code></td>
                            </tr>
                            <tr>
                                <td><strong>App Secret:</strong></td>
                                <td><code>{{ str_repeat('*', strlen($platform->app_secret ?? '')) }}</code></td>
                            </tr>
                            <tr>
                                <td><strong>Redirect URI:</strong></td>
                                <td><code>{{ $platform->redirect_uri }}</code></td>
                            </tr>
                            <tr>
                                <td><strong>OAuth URL:</strong></td>
                                <td class="text-break"><small>{{ $platform->getOAuthUrl() }}</small></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>Configurações Necessárias no Facebook:</h5>
                        <div class="alert alert-info">
                            <h6><i class="fas fa-cog"></i> Facebook Developers Console</h6>
                            <p><strong>1. Domínios do App:</strong></p>
                            <ul>
                                <li><code>localhost</code></li>
                                <li><code>127.0.0.1</code></li>
                            </ul>
                            
                            <p><strong>2. URLs de Política de Privacidade:</strong></p>
                            <ul>
                                <li><code>{{ url('/privacidade') }}</code></li>
                            </ul>
                            
                            <p><strong>3. URIs de Redirecionamento OAuth:</strong></p>
                            <ul>
                                <li><code>{{ $platform->redirect_uri }}</code></li>
                            </ul>
                            
                            <p><strong>4. URLs de Logout Válidos:</strong></p>
                            <ul>
                                <li><code>{{ url('/') }}</code></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h5>Passos para Resolver:</h5>
                        <div class="alert alert-warning">
                            <ol>
                                <li>Acesse <a href="https://developers.facebook.com/" target="_blank">Facebook Developers</a></li>
                                <li>Vá para seu App → <strong>Configurações → Básico</strong></li>
                                <li>Adicione os domínios listados acima em <strong>"Domínios do app"</strong></li>
                                <li>Adicione a URL de privacidade em <strong>"URLs de política de privacidade"</strong></li>
                                <li>Vá para <strong>Produtos → Facebook Login → Configurações</strong></li>
                                <li>Adicione a URI de redirecionamento em <strong>"URIs de redirecionamento válidos do OAuth"</strong></li>
                                <li>Salve as configurações e tente conectar novamente</li>
                            </ol>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mt-3">
                    <a href="{{ route('platforms.show', $platform) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                    <a href="{{ route('platforms.connect', $platform) }}" class="btn btn-primary">
                        <i class="fab fa-facebook"></i> Tentar Conectar Novamente
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection