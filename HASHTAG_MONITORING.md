# Sistema de Monitoramento de Hashtags e Menções

## 📋 Visão Geral

O sistema de monitoramento de hashtags permite acompanhar posts e menções nas redes sociais conectadas (Facebook/Instagram) através da Graph API.

## 🚀 Funcionalidades

### 1. **Busca de Hashtags (Instagram)**
- Busca posts que contêm hashtags específicas
- Mostra dados do autor, legenda, data e link para o post
- Suporte a múltiplas contas Instagram Business conectadas

### 2. **Monitoramento de Menções**
- **Instagram**: Menções diretas à conta Business
- **Facebook**: Posts que marcam/mencionam páginas

### 3. **Dashboard em Tempo Real**
- Métricas visuais (posts encontrados, menções, hashtags monitoradas)
- Resultados em tempo real com atualização automática
- Interface moderna e responsiva

### 4. **Gestão de Múltiplas Contas**
- Suporte a várias páginas do Facebook
- Detecção automática de contas Instagram Business vinculadas
- Sincronização de dados das contas

## 🔧 Como Usar

### Passo 1: Conectar Plataforma
1. Acesse a plataforma Facebook na lista
2. Clique em "Conectar com Facebook"
3. Autorize as permissões necessárias

### Passo 2: Configurar Contas
1. Após conectar, clique em "Monitorar Hashtags"
2. Clique em "Carregar Contas" para sincronizar páginas e Instagram
3. Selecione a conta desejada nos formulários

### Passo 3: Monitorar
- **Hashtags**: Digite a hashtag (sem #) e clique em "Buscar Posts"
- **Menções**: Clique nos botões de menções Instagram/Facebook
- **Dashboard**: Use o dashboard para visão geral e busca rápida

## 📊 Recursos do Dashboard

### Métricas Principais
- 📈 **Posts Monitorados**: Total de posts encontrados
- 💬 **Menções Encontradas**: Total de menções
- 🏷️ **Hashtags Monitoradas**: Contagem de hashtags buscadas
- ⏰ **Última Atualização**: Timestamp da última busca

### Monitoramento Rápido
- Botões diretos para cada conta conectada
- Busca expressa de hashtags via modal
- Visualização imediata de menções

### Resultados em Tempo Real
- Feed em tempo real dos resultados
- Máximo de 10 resultados mantidos na tela
- Auto-limpeza para performance

## 🔍 APIs Utilizadas

### Instagram Business Graph API
```
# Buscar hashtag
GET /ig_hashtag_search?user_id={business_id}&q={hashtag}

# Posts da hashtag
GET /{hashtag_id}/recent_media?user_id={business_id}

# Menções
GET /{business_id}/mentions
```

### Facebook Pages API
```
# Páginas da conta
GET /me/accounts

# Menções na página
GET /{page_id}/tagged
```

## ⚙️ Configuração Técnica

### Permissões Necessárias
- `pages_show_list`: Listar páginas
- `instagram_basic`: Acesso básico ao Instagram
- `instagram_manage_comments`: Gerenciar comentários
- `pages_read_engagement`: Ler engajamento das páginas

### Estrutura de Arquivos
```
app/Http/Controllers/HashtagController.php  # Controller principal
resources/views/hashtags/
├── index.blade.php                         # Página principal
└── dashboard.blade.php                     # Dashboard

routes/web.php                              # Rotas do sistema
```

### Endpoints Disponíveis
```
GET  /platforms/{id}/hashtags              # Página principal
GET  /platforms/{id}/hashtags/dashboard    # Dashboard
GET  /platforms/{id}/hashtags/accounts     # Obter contas
POST /platforms/{id}/hashtags/search       # Buscar hashtags
POST /platforms/{id}/hashtags/mentions     # Menções Instagram
POST /platforms/{id}/hashtags/facebook-mentions # Menções Facebook
GET  /platforms/{id}/hashtags/test-api     # Testar API
```

## 🎨 Interface

### Design Responsivo
- Bootstrap 5 para layout
- Ícones Bootstrap Icons
- Animações CSS suaves
- Cards organizados em grid

### Componentes Principais
- **Formulários**: Busca de hashtags com validação
- **Cards de Resultado**: Exibição organizada dos posts
- **Métricas Visuais**: Cards coloridos com contadores
- **Modais**: Busca rápida e detalhes

## 🔐 Segurança

### Validação de Dados
- Sanitização de hashtags
- Validação de IDs de conta
- Proteção CSRF em formulários

### Rate Limiting
- Respeitando limites da Facebook Graph API
- Controle de requisições por minuto
- Cache de resultados quando possível

## 📱 Recursos Mobile

### Interface Adaptada
- Cards empilhados em telas pequenas
- Botões de ação otimizados para touch
- Alertas flutuantes responsivos

### Performance
- Carregamento assíncrono
- Resultados paginados
- Cache local de dados

## 🐛 Debugging

### Logs Disponíveis
- Requisições à API
- Erros de autenticação
- Resultados de busca
- Performance de queries

### Ferramentas de Debug
- Teste de conexão API
- Validação de tokens
- Verificação de permissões

## 🔄 Atualização de Dados

### Sincronização
- Manual: Botão "Carregar Contas"
- Automática: A cada nova busca
- Cache: 5 minutos para dados de conta

### Refresh do Dashboard
- Auto-refresh a cada 5 minutos
- Botão manual de atualização
- Métricas em tempo real

## 💡 Dicas de Uso

1. **Conecte Todas as Contas**: Para máximo aproveitamento, conecte todas as páginas e contas Instagram Business
2. **Use o Dashboard**: Para monitoramento contínuo, mantenha o dashboard aberto
3. **Hashtags Populares**: Teste com hashtags populares primeiro para verificar funcionamento
4. **Permissões**: Certifique-se de que todas as permissões foram concedidas durante a conexão

## 🆘 Troubleshooting

### Problema: "Hashtag não encontrada"
- Verifique se a hashtag existe no Instagram
- Teste com hashtags populares primeiro
- Confirme que a conta Instagram Business está ativa

### Problema: "Erro ao carregar contas"
- Reconecte a plataforma Facebook
- Verifique se há páginas associadas à conta
- Teste a conexão da API

### Problema: "Sem menções encontradas"
- Menções podem demorar para aparecer
- Verifique se a conta tem menções recentes
- Confirme as permissões de acesso

## 📞 Suporte

Para problemas técnicos, consulte:
1. Logs da plataforma em `/platforms/{id}/logs`
2. Debug OAuth em `/platforms/{id}/debug`
3. Teste da API em `/platforms/{id}/hashtags/test-api`