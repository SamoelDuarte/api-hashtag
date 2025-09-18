@extends('layouts.app')

@section('title', 'Plataformas de Redes Sociais')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Plataformas de Redes Sociais</h1>
    <a href="{{ route('platforms.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nova Plataforma
    </a>
</div>

@if($platforms->count() > 0)
    <div class="row">
        @foreach($platforms as $platform)
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title">{{ $platform->name }}</h5>
                            <span class="badge bg-{{ $platform->is_connected ? 'success' : 'secondary' }}">
                                {{ $platform->is_connected ? 'Conectado' : 'Desconectado' }}
                            </span>
                        </div>
                        
                        <p class="card-text">
                            <strong>Tipo:</strong> {{ ucfirst($platform->type) }}<br>
                            <strong>App ID:</strong> {{ substr($platform->app_id, 0, 10) }}...
                        </p>

                        @if($platform->is_connected && $platform->token_expires_at)
                            <p class="card-text">
                                <small class="text-muted">
                                    Token expira em: {{ $platform->token_expires_at->format('d/m/Y H:i') }}
                                </small>
                            </p>
                        @endif

                        <div class="btn-group w-100" role="group">
                            <a href="{{ route('platforms.show', $platform) }}" class="btn btn-outline-primary btn-sm">
                                Ver
                            </a>
                            <a href="{{ route('platforms.edit', $platform) }}" class="btn btn-outline-secondary btn-sm">
                                Editar
                            </a>
                            @if($platform->is_connected)
                                <form action="{{ route('platforms.disconnect', $platform) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-warning btn-sm">
                                        Desconectar
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('platforms.connect', $platform) }}" class="btn btn-outline-success btn-sm">
                                    Conectar
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="text-center">
        <h4>Nenhuma plataforma configurada</h4>
        <p class="text-muted">Adicione uma plataforma para começar a monitorar redes sociais.</p>
        <a href="{{ route('platforms.create') }}" class="btn btn-primary">
            Adicionar primeira plataforma
        </a>
    </div>
@endif
@endsection