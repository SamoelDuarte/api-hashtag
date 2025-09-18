@extends('layouts.app')

@section('title', 'Editar ' . $platform->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4>Editar Plataforma: {{ $platform->name }}</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('platforms.update', $platform) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome da Aplicação</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $platform->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="type" class="form-label">Plataforma</label>
                        <select class="form-select @error('type') is-invalid @enderror" 
                                id="type" name="type" required>
                            @foreach($types as $key => $label)
                                <option value="{{ $key }}" {{ old('type', $platform->type) == $key ? 'selected' : '' }}>
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
                               id="app_id" name="app_id" value="{{ old('app_id', $platform->app_id) }}" required>
                        @error('app_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="app_secret" class="form-label">App Secret</label>
                        <input type="password" class="form-control @error('app_secret') is-invalid @enderror" 
                               id="app_secret" name="app_secret" placeholder="Deixe em branco para manter o atual">
                        @error('app_secret')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Deixe em branco para manter o secret atual</div>
                    </div>

                    <div class="mb-3">
                        <label for="redirect_uri" class="form-label">URL de Callback</label>
                        <input type="url" class="form-control @error('redirect_uri') is-invalid @enderror" 
                               id="redirect_uri" name="redirect_uri" 
                               value="{{ old('redirect_uri', $platform->redirect_uri) }}" required readonly>
                        @error('redirect_uri')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <strong>Use esta URL</strong> nas configurações do seu app na plataforma selecionada
                        </div>
                    </div>

                    @if($platform->is_connected)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Atenção:</strong> Alterar as configurações pode desconectar a plataforma. 
                            Você precisará reconectar após salvar as alterações.
                        </div>
                    @endif

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('platforms.show', $platform) }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Atualizar Plataforma</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection