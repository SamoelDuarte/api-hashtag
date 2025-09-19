@extends('layouts.app')

@section('title', 'Logs OAuth - ' . $platform->name)

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-file-alt"></i> Logs OAuth - {{ $platform->name }}</h4>
                <div>
                    <a href="{{ route('platforms.logs', $platform) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-sync"></i> Atualizar
                    </a>
                    <a href="{{ route('platforms.show', $platform) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(empty($logs))
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Nenhum log encontrado para esta plataforma ainda.
                        <br><br>
                        <strong>Para gerar logs:</strong>
                        <ol>
                            <li>Tente fazer uma conexão OAuth</li>
                            <li>Acesse o callback diretamente</li>
                            <li>Volte aqui para ver os logs gerados</li>
                        </ol>
                    </div>
                @else
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        Encontrados {{ count($logs) }} registros de log para esta plataforma.
                    </div>
                    
                    <h5>Últimos Logs (mais recentes primeiro):</h5>
                    <div class="border rounded p-3" style="background-color: #f8f9fa; max-height: 500px; overflow-y: auto;">
                        @foreach($logs as $log)
                            <div class="mb-2 p-2 border-bottom" style="font-family: monospace; font-size: 0.85em;">
                                {!! nl2br(e($log)) !!}
                            </div>
                        @endforeach
                    </div>
                @endif
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6">
                        <h5>Teste Manual do Callback:</h5>
                        <div class="alert alert-warning">
                            <p><strong>Para testar manualmente:</strong></p>
                            <ol>
                                <li>Copie a URL abaixo:</li>
                                <li>Cole no navegador</li>
                                <li>Veja o que acontece e volte aqui para ver os logs</li>
                            </ol>
                            <code class="text-break">{{ route('platforms.callback', $platform) }}?code=TEST_CODE_123</code>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5>Configuração Atual:</h5>
                        <table class="table table-sm table-bordered">
                            <tr>
                                <td><strong>ID da Plataforma:</strong></td>
                                <td>{{ $platform->id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tipo:</strong></td>
                                <td>{{ $platform->type }}</td>
                            </tr>
                            <tr>
                                <td><strong>URL de Callback:</strong></td>
                                <td class="text-break"><small>{{ $platform->redirect_uri }}</small></td>
                            </tr>
                            <tr>
                                <td><strong>App ID:</strong></td>
                                <td>{{ $platform->app_id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Conectado:</strong></td>
                                <td>
                                    <span class="badge bg-{{ $platform->is_connected ? 'success' : 'secondary' }}">
                                        {{ $platform->is_connected ? 'Sim' : 'Não' }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="mt-3 d-flex gap-2">
                    <a href="{{ route('platforms.callback', $platform) }}?code=TEST_CODE_123" class="btn btn-warning" target="_blank">
                        <i class="fas fa-test-tube"></i> Testar Callback (Nova Aba)
                    </a>
                    <a href="{{ route('platforms.connect', $platform) }}" class="btn btn-success">
                        <i class="fab fa-facebook"></i> Tentar Conectar Novamente
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection