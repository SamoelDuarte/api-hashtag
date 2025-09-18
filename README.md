# Social Media Monitor

Aplicação Laravel para monitoramento de plataformas de redes sociais com integração OAuth.

## Funcionalidades

- ✅ CRUD completo de plataformas de redes sociais
- ✅ Integração OAuth com Facebook/Instagram
- ✅ Interface web simples e responsiva
- ✅ Gerenciamento de tokens de acesso
- 🔄 Preparado para YouTube e TikTok (em desenvolvimento)

## Como usar

### 1. Configuração inicial
```bash
# O projeto já está instalado e configurado
php artisan serve
```

### 2. Acessar a aplicação
Abra seu navegador e acesse: `http://localhost:8000`

### 3. Adicionar uma nova plataforma
1. Clique em "Nova Plataforma"
2. Escolha o tipo (Facebook/Instagram, YouTube, TikTok)
3. Preencha os dados da sua aplicação:
   - **Nome**: Nome da sua aplicação
   - **App ID**: ID do app obtido na plataforma
   - **App Secret**: Chave secreta do app
   - **URL de Callback**: URL de retorno OAuth

### 4. Conectar com Facebook/Instagram

Para Facebook/Instagram, você precisará:

1. **Criar app no Facebook Developers**:
   - Acesse https://developers.facebook.com/
   - Crie um novo app
   - Adicione o produto "Facebook Login"
   - Configure a URL de redirecionamento: `http://localhost:8000/platforms/{id}/callback`

2. **Permissões necessárias**:
   - `pages_show_list` - Listar páginas
   - `instagram_basic` - Acesso básico ao Instagram
   - `instagram_manage_comments` - Gerenciar comentários
   - `pages_read_engagement` - Ler engajamento das páginas

3. **Fluxo de conexão**:
   - Após salvar a plataforma, clique em "Conectar"
   - Será redirecionado para o Facebook
   - Autorize as permissões
   - Retornará conectado automaticamente

## Estrutura do banco

A aplicação utiliza SQLite (arquivo local) com a tabela `platforms`:
- Dados da aplicação (nome, tipo, credenciais)
- Tokens OAuth (access_token, refresh_token)
- Status da conexão
- Metadados extras

## Próximos passos

Após conectar as plataformas, você pode:
- Implementar busca de hashtags
- Adicionar monitoramento de menções
- Criar relatórios de engajamento
- Configurar notificações

## Tecnologias utilizadas

- Laravel 12
- Bootstrap 5
- SQLite
- APIs das redes sociais
