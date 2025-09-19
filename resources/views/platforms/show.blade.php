@extends('layouts.app')

@section('title', $platform->name)

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $platform->name }}</h4>
                <span class="badge bg-{{ $platform->is_connected ? 'success' : 'secondary' }} fs-6">
                    {{ $platform->is_connected ? 'Conectado' : 'Desconectado' }}
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Tipo:</strong> {{ ucfirst($platform->type) }}</p>
                        <p><strong>App ID:</strong> {{ $platform->app_id }}</p>
                        <p><strong>URL de Callback:</strong></p>
                        <p class="text-break"><small>{{ $platform->redirect_uri }}</small></p>
                    </div>
                    <div class="col-md-6">
                        @if($platform->is_connected)
                            <p><strong>Status do Token:</strong> 
                                <span class="badge bg-{{ $platform->isTokenValid() ? 'success' : 'warning' }}">
                                    {{ $platform->isTokenValid() ? 'Válido' : 'Expirado/Inválido' }}
                                </span>
                            </p>
                            @if($platform->token_expires_at)
                                <p><strong>Token expira em:</strong><br>
                                {{ $platform->token_expires_at->format('d/m/Y H:i:s') }}</p>
                            @endif
                        @endif

                        @if($platform->access_token)
                            <p><strong>Access Token:</strong><br>
                            <small class="text-muted">{{ substr($platform->access_token, 0, 20) }}...</small></p>
                        @endif
                    </div>
                </div>

                @if($platform->extra_data)
                    <hr>
                    <h6>Dados Extras:</h6>
                    <pre class="bg-light p-2 rounded"><small>{{ json_encode($platform->extra_data, JSON_PRETTY_PRINT) }}</small></pre>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Ações</h5>
            </div>
            <div class="card-body">
                @if(!$platform->is_connected)
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Esta plataforma não está conectada. Clique em "Conectar" para autorizar o acesso.
                    </div>
                    
                    @if($platform->type === 'facebook')
                        <a href="{{ route('platforms.connect', $platform) }}" class="btn btn-success w-100 mb-2">
                            <i class="fab fa-facebook"></i> Conectar com Facebook
                        </a>
                        
                        <a href="{{ route('platforms.debug', $platform) }}" class="btn btn-info w-100 mb-2">
                            <i class="fas fa-bug"></i> Debug OAuth
                        </a>
                        
                        <a href="{{ route('platforms.logs', $platform) }}" class="btn btn-outline-info w-100 mb-2">
                            <i class="fas fa-file-alt"></i> Ver Logs
                        </a>
                        
                        <div class="alert alert-info">
                            <small>
                                <strong>Permissões solicitadas:</strong><br>
                                • pages_show_list<br>
                                • instagram_basic<br>
                                • instagram_manage_comments<br>
                                • pages_read_engagement
                            </small>
                        </div>
                    @else
                        <div class="alert alert-info">
                            OAuth para {{ ucfirst($platform->type) }} ainda não implementado.
                        </div>
                    @endif
                @else
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        Plataforma conectada com sucesso!
                    </div>
                    
                    <form action="{{ route('platforms.disconnect', $platform) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-warning w-100" 
                                onclick="return confirm('Tem certeza que deseja desconectar?')">
                            <i class="fas fa-unlink"></i> Desconectar
                        </button>
                    </form>
                @endif

                <a href="{{ route('platforms.edit', $platform) }}" class="btn btn-outline-primary w-100 mb-2">
                    <i class="fas fa-edit"></i> Editar
                </a>

                <form action="{{ route('platforms.destroy', $platform) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100" 
                            onclick="return confirm('Tem certeza que deseja excluir esta plataforma?')">
                        <i class="fas fa-trash"></i> Excluir
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('platforms.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</div>
@endsection