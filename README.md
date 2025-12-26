# Nutri Hellen - Plataforma Institucional e de Agendamento

Bem-vindo ao repositório oficial do projeto **Nutri Hellen**. Este sistema está sendo desenvolvido como peça central de portfólio, aplicando práticas modernas de Engenharia de Software com Laravel 11.

O objetivo é resolver um problema real de negócio: criar uma presença digital profissional e automatizar o agendamento de consultas nutricionais.

---

## 🛠 Tech Stack & Arquitetura

- **Backend:** Laravel 12.42.0 (PHP 8.5.0)
- **Frontend:** Livewire 3 + Tailwind CSS
- **Banco de Dados:** MySQL 8.0
- **Infraestrutura:** Docker (via Laravel Sail) sobre WSL2
- **Versionamento:** Gitflow (Main/Develop)

---

## 📖 Diário de Bordo: Decisões Arquiteturais

Este projeto serve como um laboratório vivo. Abaixo, documento as decisões técnicas tomadas e os desafios superados.

### 01. Infraestrutura e Isolamento de Ambiente (Docker)
**O Desafio:**
Desenvolver múltiplos projetos no mesmo ambiente (WSL2) frequentemente causa conflitos de dependências (versões do PHP) e colisão de portas.

**A Solução:**
Adotei a containerização completa utilizando **Docker** e **Laravel Sail**. Isso isola o ambiente da aplicação, garantindo que ela funcione identicamente em qualquer máquina.
- **Estratégia de Portas Customizadas:** Como já possuía microsserviços rodando nas portas padrão (80/3306), configurei o `docker-compose` (via `.env`) para expor este projeto em portas dedicadas, evitando conflitos de *binding*:
  - Aplicação Web: `:8001`
  - MySQL: `:3307`
  - Vite (HMR): `:5174`

### 02. Fluxo de Versionamento (Gitflow Adaptado)
**A Decisão:**
Para simular um ambiente profissional, aboli commits diretos na branch de produção.
- **`main`**: Código estável, pronto para deploy. Representa a "verdade" do produto.
- **`develop`**: Branch de integração onde as funcionalidades são testadas antes do merge.
Isso garante uma esteira de desenvolvimento segura e organizada.

### 03. Stack de Frontend e Compilação de Assets 
**A Decisão:**
Para manter a agilidade no desenvolvimento da interface, optei pela "TALL Stack" (Tailwind, Alpine - implícito no Livewire, Laravel, Livewire).

Configuração Técnica: Configurei o Vite para processar os arquivos do Tailwind CSS em tempo real (HMR).

Desafio: O utilitário npx apresentou falhas de resolução de binário dentro do container Docker.

Solução: Executei a inicialização do Tailwind invocando diretamente o binário local (./node_modules/.bin/tailwindcss), contornando a falha de path do ambiente virtualizado.

### 04. Identidade Visual (Tailwind v4) e Captura de Leads
**O Desafio:**
Implementar a identidade visual "Outono" (Terracota e Verde Musgo) utilizando a versão mais recente do framework (**Tailwind CSS v4**) e criar um fluxo de agendamento sem fricção (sem login).

**Decisões Técnicas:**
* **Configuração CSS-Native:** Adotei a nova arquitetura do Tailwind v4, migrando a configuração do antigo arquivo JS (`tailwind.config.js`) para variáveis de tema diretamente no CSS (`@theme`). Isso eliminou arquivos de configuração legados e simplificou a pipeline de build.
* **Design System Semântico:** Defini tokens como `--color-brand-primary` e `--color-brand-secondary`. Isso desacopla a lógica de cores do HTML, permitindo mudanças globais de marca editando apenas o CSS.
* **Estratégia "Zero-Login":** Para maximizar a conversão de pacientes, optei por um formulário stateless que:
    1.  Valida e persiste o lead no MySQL (Tabela `leads`) para controle de métricas.
    2.  Redireciona imediatamente para o WhatsApp da nutricionista com mensagem contextualizada (`redirect()->away()`), transferindo a negociação para um canal direto.

### 05. Autenticação Customizada e Área Administrativa 
**O Desafio:**
 Criar uma área restrita segura para gestão dos leads sem utilizar "Starter Kits" pesados (como Breeze ou Jetstream) que poderiam sobrescrever a customização CSS (Tailwind v4) já realizada.

**A Solução:**
**A Solução:**
Implementei um fluxo de autenticação manual utilizando **Livewire** e os recursos nativos do Laravel (`Auth::attempt`, `Middleware`).
* **Segurança:** Proteção das rotas administrativas (`/dashboard`) via middleware `auth`. Uso de *Seeders* para criação controlada do usuário admin, evitando páginas públicas de registro.
* **Dashboard Interativo:** Construção de um painel SPA (Single Page Application) com Livewire, permitindo que a nutricionista marque leads como "Contatados" em tempo real, sem recarregamento de página.
* **UX Discreta:** Implementação de um ponto de acesso administrativo oculto no rodapé, visível apenas como um ícone de cadeado para visitantes, mas que se transforma em um botão de acesso rápido quando o usuário está autenticado.

**Segurança:**
Proteção das rotas administrativas (/dashboard) via middleware auth. Uso de Seeders para criação controlada do usuário admin, evitando páginas públicas de registro.

**Dashboard Interativo:**
Construção de um painel SPA (Single Page Application) com Livewire, permitindo que a nutricionista marque leads como "Contatados" em tempo real, sem recarregamento de página (AJAX/Fetch implícito).

### 06. Qualidade de Software e Testes Automatizados
**O Desafio:**
Garantir a confiabilidade do fluxo crítico de negócios (Agendamento) e da segurança (Proteção do Dashboard) em um ambiente de desenvolvimento instável (Laravel 12 Alpha + PHP 8.5).

**A Solução:**
Implementei testes automatizados de integração (Feature Tests) cobrindo os cenários de sucesso e falha.
* **Pivot Estratégico (Tooling):** Inicialmente optei pelo *Pest PHP*, mas devido a conflitos de dependência com as versões *bleeding edge* do framework, migrei para o **PHPUnit** nativo. Isso garantiu a execução dos testes sem bloquear o avanço do projeto.
* **Cobertura de Testes:**
    1.  **Smoke Test:** Validação de carregamento da Landing Page (Status 200).
    2.  **Segurança:** Tentativa de acesso não autorizado ao `/dashboard` (deve redirecionar para login).
    3.  **Fluxo de Negócio:** Simulação completa de um paciente preenchendo o componente Livewire, verificando a persistência correta na tabela `leads` do MySQL.

### 07. Refinamento Visual, SEO e Validação (Release v1.5)
**O Desafio:**
Elevar o nível do MVP para um produto final de mercado, validado pela cliente real (Nutricionista), focando em conversão e identidade visual.

**A Solução:**
* **Copywriting & UX:** Reescrita total dos textos aplicando gatilhos mentais (autoridade e empatia) e criação da seção "Como Funciona" para reduzir a fricção de entrada.
* **UI Design & Assets:**
    * Integração das fotos profissionais com ajustes finos de CSS (`object-top` no Tailwind) para garantir enquadramento perfeito do rosto em qualquer tela.
    * Implementação de botão flutuante (Floating Action Button) do WhatsApp com ícone SVG limpo para conversão direta.
* **SEO Técnico:** Correção da renderização da tag `<title>`, sobrepondo as configurações padrão do Laravel (`app.name`) para garantir indexação correta no Google ("Nutricionista Hellen...").
* **Validação:** Aprovação final da stakeholder (cliente).

**Status:** Projeto PRONTO para Deploy (Go Live).

### 08. Deploy em Produção e Orquestração (Coolify & Docker)
* **O Desafio:** Levar a aplicação do ambiente local (Sail/WSL2) para um servidor de produção (VPS) mantendo o baixo custo, mas com autonomia de CI/CD (Integração e Entrega Contínuas). A Escolha: Utilizei a DigitalOcean como provedor de infraestrutura e o Coolify como orquestrador (PaaS self-hosted), evitando a complexidade de configurar servidores Linux manualmente do zero.

* **Obstáculos e Soluções de Engenharia:**

**Gerenciamento de Recursos (Swap):**

* Problema: O servidor de 2GB de RAM sofria crashes silenciosos durante o processo de build (compilação do NPM/Vite), que é intensivo em memória.

* Solução: Implementação de uma Swap Memory de 2GB via terminal Linux, dobrando a capacidade "virtual" do servidor e permitindo que o processo de build finalizasse sem estourar a memória (OOM Kill).

* **Estratégia de Build (Nixpacks vs. Dockerfile):**

* Problema: O construtor automático do Coolify (Nixpacks) gerou conflitos na configuração do Nginx (duplicate location "/") e inconsistências entre a versão do PHP instalada (8.3) e a exigida pelo composer.lock (8.4).

* Decisão Arquitetural: Abandonei a "mágica" automática e assumi o controle total criando um Dockerfile de Produção.

Stack: Baseado na imagem profissional serversideup/php:8.4-fpm-nginx, que já traz otimizações de segurança, PHP 8.4 e Nginx pré-configurado para Laravel. Isso eliminou a ambiguidade do ambiente.

Banco de Dados e Networking:

* Problema 1: A versão mais recente do MySQL (8.4) removeu o plugin mysql_native_password, quebrando a autenticação do driver padrão do Laravel.

* Problema 2: O container da aplicação não conseguia resolver o nome do host do banco de dados devido a latências na propagação do DNS interno do Docker (php_network_getaddresses).

* Solução 1: Migração estratégica para MariaDB, garantindo compatibilidade nativa e simplificada com o ecossistema Laravel.

* Solução 2: Conexão via IP Interno Estático. Ao invés de depender do nome do container, configurei o DB_HOST diretamente com o IP da rede interna do Docker, eliminando falhas de resolução de nomes e garantindo conexão imediata.

---

## 🚀 Como rodar o projeto localmente

1. **Clone o repositório:**
   ```bash
   git clone https://github.com/Porzelt/Nutri-Hellen-Site.git
   cd Nutri-Hellen-Site

2. **Configure o ambiente:**
Copie o arquivo de exemplo e configure as variáveis de ambiente.
    ```bash
    cp .env.example .env
    Nota: Certifique-se de configurar APP_PORT=8001 e FORWARD_DB_PORT=3307 no .env se houver conflitos de porta.

3. **Inicie os Containers**
    ```bash
    ./vendor/bin/sail up -d

4. **Instale as dependências e rode as migrações**
    ```bash
    ./vendor/bin/sail composer install
    ./vendor/bin/sail npm install
    ./vendor/bin/sail artisan migrate

5. **Acesse**
O projeto estará rodando em: http://localhost:8001

