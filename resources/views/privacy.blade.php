@extends('layouts.app')

@section('title', 'Política de Privacidade')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-shield-alt"></i> Política de Privacidade</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Última atualização: {{ date('d/m/Y') }}</p>
                    
                    <h4>1. Informações Coletadas</h4>
                    <p>Nossa aplicação coleta apenas as informações necessárias para funcionar:</p>
                    <ul>
                        <li><strong>Dados de Autenticação:</strong> Tokens de acesso das redes sociais que você autoriza</li>
                        <li><strong>Configurações:</strong> IDs e configurações dos aplicativos que você cria</li>
                        <li><strong>Logs:</strong> Informações básicas de uso para melhorar o serviço</li>
                    </ul>
                    
                    <h4>2. Como Usamos Suas Informações</h4>
                    <p>Utilizamos suas informações exclusivamente para:</p>
                    <ul>
                        <li>Conectar sua conta às redes sociais autorizadas</li>
                        <li>Monitorar hashtags e conteúdo conforme configurado</li>
                        <li>Exibir dados e relatórios de engajamento</li>
                        <li>Manter a segurança e funcionalidade da aplicação</li>
                    </ul>
                    
                    <h4>3. Compartilhamento de Dados</h4>
                    <p><strong>Não compartilhamos</strong> suas informações pessoais com terceiros. Seus dados permanecem seguros em nossos servidores.</p>
                    
                    <h4>4. Segurança</h4>
                    <p>Implementamos medidas de segurança apropriadas para proteger suas informações contra acesso não autorizado, alteração, divulgação ou destruição.</p>
                    
                    <h4>5. Seus Direitos</h4>
                    <p>Você tem o direito de:</p>
                    <ul>
                        <li>Desconectar suas contas a qualquer momento</li>
                        <li>Solicitar a exclusão de seus dados</li>
                        <li>Acessar informações sobre dados coletados</li>
                        <li>Corrigir informações incorretas</li>
                    </ul>
                    
                    <h4>6. Contato</h4>
                    <p>Se você tiver dúvidas sobre esta política de privacidade, entre em contato através do suporte da aplicação.</p>
                    
                    <div class="mt-4">
                        <a href="{{ url('/') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Voltar à Página Inicial
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection