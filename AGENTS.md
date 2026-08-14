# AGENTS.md

Este arquivo orienta agentes de IA que trabalham neste repositório. Leia-o antes de propor ou implementar alterações.

## Objetivo do produto

O Daily Task Tracker é uma aplicação pessoal, sem autenticação ativa, voltada ao planejamento e acompanhamento diário. A home é um Kanban por data e concentra tarefas, lembretes, resumo diário e gestão de tempo. Há telas separadas para tags, analytics, lembretes, categorias de tempo, histórico de tarefas, conquistas e anotações globais.

## Stack e execução

- Backend: PHP 8.2+, Laravel 12 e Eloquent.
- Banco: SQLite em `database/database.sqlite`.
- Frontend: Blade, Tailwind CSS 4, jQuery e jQuery UI.
- Build: Vite 6 com `@tailwindcss/vite`.
- Docker: Nginx + PHP 8.3 FPM, definidos em `docker-compose.yml` e `docker/`.
- Fuso padrão: `America/Sao_Paulo`.

Comandos locais usuais:

```bash
composer install
npm install
php artisan migrate
npm run build
php artisan serve
```

Não suba containers, servidores ou serviços externos a menos que o usuário peça explicitamente.

## Mapa técnico

### Domínio e backend

- `app/Constants/Lanes.php`: fonte dos status do Kanban.
- `app/Models/Task.php`: tarefa por data; relaciona-se com várias `Tag`.
- `app/Http/Controllers/TaskController.php`: home, CRUD AJAX, drag and drop, cópia entre dias, dia anterior e linhagem.
- `TaskAnalyticsController`: considera apenas tarefas originais do mês e substitui seu status pelo membro mais recente da linhagem.
- `ReminderController`: separa lembretes `recurring` e `sporadic`; `last_completed_at` indica conclusão.
- `DaySummaryController`: mantém um único resumo por data com `updateOrCreate`.
- `NoteController`: CRUD de anotações globais; renderiza o conteúdo Markdown com HTML bruto removido e links inseguros bloqueados.
- `TimeManagementController`: sincroniza todas as entradas de uma data por substituição (delete + create).
- `AchievementController`: agrupa conquistas pelo campo textual `period` (`MM/AAAA`).
- `routes/web.php`: todas as rotas web e endpoints AJAX; não há `routes/api.php` em uso.

### Frontend

- `resources/views/layout.blade.php`: layout global, `@vite`, jQuery, `public/js/script.js`, dark mode e notificações.
- `resources/views/home.blade.php`: Kanban e seções expansíveis da home.
- `resources/views/partials/header.blade.php`: navegação temporal.
- `resources/views/partials/nav.blade.php`: navegação entre módulos.
- `public/js/script.js`: JavaScript principal da home e operações AJAX. Este arquivo é servido diretamente, não é empacotado pelo Vite.
- `public/js/analytics.js`: lógica da página mensal de analytics.
- `resources/js/app.js`: entrada Vite; atualmente inicializa apenas `bootstrap.js`/Axios.
- `resources/css/app.css`: Tailwind 4 e variante manual de dark mode baseada na classe `.dark` do elemento `<html>`.
- `public/css/style.css`: estilos complementares e específicos do projeto.

## Regras de domínio que não podem ser inferidas pelo nome

1. `Lanes::WAITING` possui o valor persistido **`waiting`**. A migration `2026_08_13_010000_rename_wating_lane_to_waiting` converte `wating` para `waiting` e o antigo `next` para `todo`; não reintroduza esses valores em código ou dados novos.
2. As raias atuais são `todo`, `waiting`, `done` e `extra`. Não reintroduza `next` sem alterar domínio, banco e UI.
3. Uma tarefa copiada recebe `id_original` igual ao ID da primeira tarefa da família. A linhagem é usada na tela de detalhes e nos analytics.
4. A cópia entre dias inclui apenas `todo` e `waiting`, preserva tags e cria novas tarefas para a data de destino.
5. `time-management/sync` substitui todas as entradas da data. Alterar esse fluxo para updates parciais muda a semântica atual.
6. Tags de tarefas (`tags`) e tags de tempo (`time_management_tags`) são entidades diferentes.
7. O resumo diário é único por data.
8. O endpoint `GET /csrf-token` renova sessão/token antes de salvar o resumo após longos períodos com a aba aberta.

## JavaScript e CSRF

- jQuery é carregado antes de `public/js/script.js` no final do layout.
- O token inicial vem de `<meta name="csrf-token">`.
- A variável `csrfToken` em `public/js/script.js` é mutável porque o resumo pode renová-la por `/csrf-token`.
- Antes de adicionar listeners, pesquise handlers já existentes. Dois listeners que executem `classList.toggle()` no mesmo clique anulam visualmente um ao outro.
- Para estados visuais Tailwind controlados por JS, prefira atributos como `data-active` com variantes declaradas literalmente no Blade. Classes presentes apenas em `public/js/script.js` podem não ser encontradas pelo scanner configurado em `resources/css/app.css`.

## Dark mode

- A classe `dark` pertence ao elemento `<html>`.
- A preferência é persistida em `localStorage.theme` como `dark` ou `light`.
- O único listener do botão `#themeToggle` deve permanecer em `public/js/script.js`.
- O script inline no `<head>` apenas aplica o tema inicial cedo para evitar flash de cor; não deve registrar outro listener.
- O Tailwind usa `@custom-variant dark (&:where(.dark, .dark *));`.

## Banco e migrations

- Trate `database/database.sqlite` como dado real do usuário. Não apague, recrie, zere ou substitua esse arquivo.
- Mudanças de schema devem ser feitas por novas migrations; nunca edite uma migration já aplicada para corrigir produção.
- Preserve dados ao criar migrations e forneça `down()` seguro quando possível.
- No Docker, `./database` é bind mount e `storage` usa volume nomeado.

Principais tabelas de produto:

- `tasks`, `tags`, `tag_task`
- `reminders`
- `day_summaries`
- `time_management_entries`, `time_management_tags`
- `achievements`
- `notes`

## Docker

- `docker/Dockerfile` é multi-stage: Composer, frontend, alvo `app` (PHP-FPM) e alvo `nginx`.
- `docker/nginx.conf` serve somente `public/`, entrega assets diretamente e encaminha apenas `index.php` ao serviço `app:9000`.
- `docker/entrypoint.sh` cria diretórios, garante o SQLite, ajusta permissões, executa migrations e inicia o comando do container.
- `docker-compose.yml` expõe somente o Nginx em `${APP_PORT:-8000}`.
- O Compose exige um `.env` local porque usa `env_file: .env`.

## Segurança

- Não há middleware de autenticação protegendo as rotas atuais. Considere a aplicação single-user/local até que autenticação seja implementada.
- Não documente nem exponha valores do `.env`, especialmente `APP_KEY`.
- Preserve CSRF em todas as mutações web.
- Não renderize strings de usuário em templates JavaScript/HTML sem escape. Ao tocar nos templates montados em `public/js/script.js`, avalie risco de XSS.

## Convenções para alterações

- Preserve o padrão Blade + jQuery existente, salvo pedido explícito de migração tecnológica.
- Controllers retornam views para páginas e JSON para operações AJAX.
- Reutilize `showNotification()` para feedback na home.
- Mantenha suporte a dark mode em novos componentes com variantes `dark:`.
- Use nomes de classe PHP e imports com a capitalização exata dos arquivos, especialmente porque containers Linux diferenciam maiúsculas e minúsculas.
- Não altere dados ou arquivos não relacionados; o worktree pode conter trabalho do usuário.
- Atualize este arquivo e o README quando uma mudança alterar arquitetura, execução, rotas principais ou regras de domínio.

## Validação esperada

Não existe atualmente uma suíte funcional em `tests/`. Valide proporcionalmente com:

```bash
node --check public/js/script.js
npm run build
php artisan view:cache
php artisan route:list --except-vendor
docker compose config --quiet
```

Use `php artisan test` somente depois que o diretório `tests/` existir. Para mudanças no banco, teste a migration em uma cópia descartável do SQLite; nunca use o banco real como alvo de testes destrutivos.

## Pontos de atenção conhecidos

- A coluna `tasks.status` usa uma enumeração SQLite com `todo`, `waiting`, `done` e `extra`. A migration de renomeação aceita o valor legado apenas durante sua execução.
- Não há autenticação nem testes automatizados cobrindo os fluxos críticos.
