# 📋 Daily Task Tracker

![Daily Task Tracker](docs/daily-task-tracker-home.png)

Uma aplicação robusta e elegante para gerenciamento de tarefas diárias, focada em produtividade operacional, rastreabilidade e análise de performance.

## 🚀 Principais Funcionalidades

### 🧩 Kanban & Workflow

- **Raia Dinâmica**: Gerencie suas tarefas entre `TODO`, `WAITING`, `DONE` e `EXTRA`.
- **Drag & Drop**: Interface intuitiva para mover tarefas entre estados com persistência automática.
- **Continuidade de Fluxo**: Recupere tarefas pendentes (Next/Waiting) de dias anteriores para o dia atual com um clique.

### 📜 Rastreabilidade & Linhagem

- **Linhagem de Tarefas**: Sistema inteligente que rastreia a origem de cada tarefa copiada, criando um histórico evolutivo.
- **Performance Metrics**: Acompanhe o ciclo de vida de cada tarefa com data de início, conclusão e duração total.
- **Histórico Completo**: Visualize instâncias passadas e futuras de uma mesma tarefa através de sua linhagem.

### 🏷️ Tags & Organização

- **Multi-Tags**: Atribua múltiplas categorias a uma única tarefa.
- **Sistema de Cores**: Identificação visual rápida através de tags coloridas customizáveis.
- **Filtragem Avançada**: Organize seu dia por prioridades ou tipos de atividade.

### 📊 Analytics & Relatórios

- **Dashboard Mensal**: Visão macro das suas atividades iniciadas e concluídas no mês.
- **Indicadores de Eficiência**: Veja quantas tarefas originais foram criadas vs. quantas foram concluídas.
- **Navegação Temporal**: Explore o histórico de qualquer dia passado através de um calendário integrado.

## 🛠️ Tech Stack

- **Core**: PHP 8.2+ & Laravel 12
- **Database**: SQLite (Leve, portátil e eficiente)
- **Frontend**: Blade, Tailwind CSS, JQuery & JQuery UI
- **AI Accelerated**: Desenvolvimento otimizado com **AntiGravity AI**

## 📦 Instalação e Configuração

### 1. Preparar o Ambiente

```bash
composer install
npm install
```

### 2. Configurar Banco de Dados (SQLite)

1. Crie o arquivo do banco:

   ```bash
   touch database/database.sqlite
   ```

2. Certifique-se de que as extensões `pdo_sqlite` e `sqlite3` estão habilitadas no seu `php.ini`.

### 3. Migrações e Chaves

```bash
php artisan key:generate
php artisan migrate
```

### 4. Compilação de Assets

```bash
npm run build
# Ou para desenvolvimento:
npm run dev
```

### 5. Executar

```bash
php artisan serve
```

## 💡 Como Usar

1. **Planeje seu dia**: Comece adicionando as tarefas que pretende realizar.
2. **Execute e Movimente**: Conforme avança, arraste as tarefas para as raias correspondentes.
3. **Revise o Passado**: Use a navegabilidade para ver o que foi feito ontem e copie tarefas "Next" para hoje.
4. **Analise Gráficos**: Use a aba de Analytics para entender sua produtividade mensal.

---
Desenvolvido com foco em simplicidade e poder de análise. 🚀
