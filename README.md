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

