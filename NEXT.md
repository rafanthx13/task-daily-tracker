# NEXT — Ideias e melhorias

Este documento reúne sugestões para evolução do Daily Task Tracker. A ordem considera primeiro integridade dos dados, segurança e confiabilidade; depois experiência do usuário e novas funcionalidades.

## Prioridade 1 — Confiabilidade e segurança

### 1. Criar testes automatizados

O projeto ainda não possui uma suíte funcional em `tests/`. Os primeiros testes devem proteger os fluxos que podem causar perda ou alteração incorreta de dados:

- criação, edição, exclusão e movimentação de tarefas;
- cópia de tarefas entre dias e preservação da linhagem;
- sincronização das entradas de gestão de tempo;
- conclusão de lembretes recorrentes e esporádicos;
- resumo único por data;
- analytics usando o último estado da família de tarefas;
- renovação do token CSRF após expiração da sessão.

**Resultado esperado:** executar `php artisan test` com cobertura dos principais fluxos de negócio.

### 2. Remover `dd()` do fluxo de criação de tarefas

`TaskController::store()` captura exceções e chama `dd()`. Em produção isso interrompe a resposta e pode exibir informações internas.

**Sugestão:** registrar a exceção no log e retornar uma mensagem amigável, preservando os dados enviados com `withInput()`.

### 3. Validar mudanças de raia no backend

`updateLane()` aceita qualquer string como status, enquanto a criação valida os valores de `Lanes`.

**Sugestão:** usar `Rule::in(Lanes::getAllAsArray())` também na movimentação via drag and drop.

**Resultado esperado:** valores inválidos nunca entram na coluna `tasks.status`.

### 4. Corrigir a capitalização de `TaskController`

Existem referências a `taskController` enquanto o arquivo e a classe usam `TaskController`. Isso pode funcionar no Windows, mas causar problemas em ambientes Linux e Docker.

**Sugestão:** padronizar imports, referências de rota e nomes conforme PSR-4 e PSR-12.

### 5. Tratar conteúdo inserido no HTML pelo JavaScript

A visualização do dia anterior monta HTML com título, notas e tags retornados pela API. Se esses valores contiverem marcação maliciosa, podem gerar XSS.

**Sugestão:** criar elementos com APIs do DOM/jQuery usando `.text()`, ou aplicar uma função de escape antes da interpolação.

### 6. Adicionar autenticação

Atualmente todas as rotas ficam públicas. Antes de disponibilizar o sistema na internet:

- implementar login;
- proteger rotas com middleware `auth`;
- limitar cadastro de usuários conforme o uso pessoal do projeto;
- adicionar logout e recuperação de acesso;
- configurar HTTPS no ambiente publicado.

## Prioridade 2 — Integridade e manutenção

### 7. Corrigir o valor legado `wating`

O status `WAITING` está persistido como `wating`. A correção deve ser coordenada para não quebrar dados existentes.

Etapas sugeridas:

1. criar migration que atualize `wating` para `waiting`;
2. ajustar a definição/constraint da coluna no SQLite;
3. atualizar `Lanes`, validações, JavaScript e textos relacionados;
4. testar cópia entre dias, analytics e drag and drop;
5. manter compatibilidade temporária se houver clientes antigos.

### 8. Criar Form Requests

As validações estão espalhadas nos controllers.

**Sugestão:** criar requests específicos, como `StoreTaskRequest`, `UpdateTaskRequest`, `SyncTimeEntriesRequest` e `StoreReminderRequest`.

**Benefícios:** controllers menores, regras reutilizáveis e mensagens de validação consistentes.

### 9. Adicionar transações em operações compostas

A sincronização de gestão de tempo primeiro apaga os registros e depois cria os novos. Uma falha intermediária pode deixar o dia sem dados.

**Sugestão:** envolver `delete + create` em `DB::transaction()`. Avaliar o mesmo para outros fluxos com múltiplas gravações.

### 10. Criar índices para consultas frequentes

Avaliar índices para:

- `tasks.date`;
- combinação `tasks.date + tasks.status`;
- `tasks.id_original`;
- `reminders.type + reminders.last_completed_at`;
- `time_management_entries.date`;
- `achievements.period`.

Antes de criar, comparar planos de consulta e volume real do SQLite.

### 11. Padronizar respostas AJAX

Alguns endpoints retornam `success`, outros apenas `message`, e os erros não seguem uma estrutura única.

**Sugestão:** padronizar respostas com `success`, `message`, `data` e erros de validação previsíveis.

### 12. Separar o JavaScript principal por módulos

`public/js/script.js` concentra tarefas, tags, lembretes, resumo, gestão de tempo, notificações e dark mode.

**Sugestão de módulos:**

- `notifications.js`;
- `theme.js`;
- `tasks.js`;
- `reminders.js`;
- `day-summary.js`;
- `time-management.js`.

Depois, importar esses módulos por `resources/js/app.js` e Vite. Fazer a migração gradualmente para evitar listeners duplicados.

## Prioridade 3 — Experiência do usuário

### 13. Exibir estados de carregamento consistentes

Adicionar indicadores durante:

- carregamento do dia anterior;
- cópia de tarefas;
- salvamento de resumo;
- sincronização de gestão de tempo;
- carregamento dos analytics.

Os botões devem ficar desabilitados enquanto a operação estiver em andamento, evitando envios duplicados.

### 14. Melhorar mensagens de erro

Em vez de mensagens genéricas, diferenciar:

- falha de conexão;
- sessão expirada;
- erro de validação;
- registro removido em outra aba;
- erro interno do servidor.

Também oferecer uma ação clara, como “Tentar novamente” ou “Recarregar página”.

### 15. Salvamento automático do resumo diário

Salvar o resumo após alguns segundos sem digitação (debounce), mantendo o botão para salvamento manual.

**Cuidados:** mostrar estados “Salvando…” e “Salvo”, renovar CSRF e não disparar requisições a cada tecla.

### 16. Filtros de tarefas

Permitir filtrar o Kanban por:

- texto do título/notas;
- uma ou mais tags;
- status;
- tarefas originais ou copiadas.

### 17. Melhorar a visualização do dia anterior

- mostrar claramente qual data está sendo exibida;
- deixar a coluna somente leitura, removendo ações que não fazem sentido;
- oferecer botão de copiar por tarefa, além da cópia em lote;
- manter o estado ativo do botão acessível com `aria-expanded` e `aria-controls`.

### 18. Reordenar tarefas dentro da mesma raia

O campo `ordering` existe, mas deve haver persistência explícita da ordem após drag and drop.

**Resultado esperado:** ao recarregar a página, as tarefas permanecem exatamente na ordem definida pelo usuário.

### 19. Atalhos de teclado

Sugestões:

- `N`: nova tarefa;
- `/`: focar busca;
- setas: navegar entre dias;
- `Ctrl/Cmd + Enter`: salvar modal ou resumo;
- `Esc`: fechar modais.

Evitar atalhos enquanto o usuário estiver digitando em inputs ou textareas.

### 20. Melhorar responsividade e acessibilidade

- testar Kanban e modais em telas pequenas;
- garantir navegação completa por teclado;
- adicionar foco visível;
- associar labels aos campos;
- revisar contraste nos modos claro e escuro;
- adicionar `aria-live` às notificações;
- respeitar `prefers-reduced-motion`.

## Prioridade 4 — Analytics e produtividade

### 21. Dashboard por período personalizado

Além do mês, permitir semana, trimestre e intervalo definido pelo usuário.

Possíveis métricas:

- tarefas criadas e concluídas;
- taxa de conclusão;
- tempo médio até conclusão;
- tarefas carregadas por vários dias;
- distribuição por tag;
- distribuição por raia;
- horas registradas por categoria.

### 22. Relacionar gestão de tempo com tarefas

Hoje a gestão de tempo armazena `task_name` como texto independente.

**Sugestão:** adicionar `task_id` opcional para relacionar um registro a uma tarefa, preservando `task_name` como snapshot histórico.

### 23. Metas e acompanhamento

Permitir metas como:

- concluir determinada quantidade de tarefas por semana;
- registrar um número mínimo de horas por categoria;
- evitar tarefas pendentes por mais de certo número de dias.

As conquistas podem ser vinculadas a essas metas no futuro.

### 24. Exportação e backup

- exportar analytics e gestão de tempo em CSV;
- exportar um dia em JSON ou Markdown;
- criar backup do SQLite pela interface ou comando Artisan;
- permitir restauração apenas com confirmação e validação do arquivo.

## Prioridade 5 — Operação e observabilidade

### 25. Healthcheck no Docker Compose

Adicionar healthchecks para PHP-FPM/aplicação e Nginx, fazendo o Nginx depender da saúde do serviço `app`, não apenas de sua inicialização.

### 26. Estratégia de backup do SQLite

Definir:

- frequência do backup;
- quantidade de versões mantidas;
- local de armazenamento;
- teste periódico de restauração;
- comportamento durante gravações.

Não copiar o arquivo SQLite de forma ingênua durante transações; utilizar o mecanismo de backup do SQLite ou uma janela controlada.

### 27. Logs estruturados

Adicionar contexto aos logs de falhas de AJAX e operações críticas, sem registrar conteúdo sensível. Em produção, considerar rotação e retenção.

### 28. Pipeline de integração contínua

Criar uma automação que execute:

```bash
composer install --no-interaction
npm ci
php artisan test
npm run build
```

Também pode validar estilo PHP com Laravel Pint e construir as imagens Docker.

## Funcionalidades futuras opcionais

- tarefas recorrentes independentes dos lembretes;
- subtarefas e checklists;
- prioridades e datas-limite;
- anexos e links por tarefa;
- calendário semanal/mensal;
- modo offline/PWA;
- importação e exportação iCalendar;
- notificações do navegador;
- busca global;
- arquivamento de tarefas antigas;
- tema seguindo automaticamente o sistema;
- API versionada para integrações futuras.

## Sequência recomendada

Uma sequência segura para as próximas etapas seria:

1. testes dos fluxos críticos;
2. remoção de `dd()` e validação das raias;
3. correção de capitalização para Linux/Docker;
4. proteção contra XSS;
5. transação na gestão de tempo;
6. autenticação antes de qualquer publicação;
7. persistência da ordenação e melhorias de UX;
8. refatoração gradual do JavaScript;
9. novos analytics, exportação e backups;
10. correção migrada de `wating` para `waiting`.

Este roadmap deve ser atualizado quando uma melhoria for implementada, removida ou substituída por outra decisão arquitetural.
