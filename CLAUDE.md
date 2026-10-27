# CLAUDE.md

Site institucional da nutricionista Hellen Fátima (nutrihellenfatima.com.br): landing page, captura de leads com redirecionamento para WhatsApp e painel admin (`/dashboard`). Responda em português.

**Repositório PÚBLICO.** Nunca commitar `.env`, senhas, tokens, dados de pacientes ou endereços internos.

## Stack
- Laravel 12, Livewire 3 (componentes em `app/Livewire`, views em `resources/views/livewire`), PHPUnit.
- Tailwind v4 configurado no CSS (`@theme` em `resources/css/app.css`, tokens `--color-brand-*`). `tailwind.config.js` é legado, não usar.
- Local: Laravel Sail (runtime PHP 8.5, MySQL 8.4). Produção: PHP 8.4 (`serversideup/php:8.4-fpm-nginx`) + MariaDB. Não usar recursos exclusivos do PHP 8.5.

## Rodar local (sempre dentro do WSL)
O projeto fica em `~/projects/Nutri-Hellen-Site` no WSL Ubuntu. Pelo Windows, prefixe com `wsl -e bash -lc "cd ~/projects/Nutri-Hellen-Site && ..."`. Git também deve rodar no WSL (pelo caminho `\\wsl.localhost` dá "dubious ownership").

```bash
./vendor/bin/sail up -d
./vendor/bin/sail composer install
./vendor/bin/sail npm ci && ./vendor/bin/sail npm run build   # ou npm run dev (HMR na 5174)
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan test
```
Acesso: http://localhost:8001. Portas no `.env`: `APP_PORT=8001`, `VITE_PORT=5174`, `FORWARD_DB_PORT=3307`. Os testes usam o banco `testing` do MySQL do Sail (ver `phpunit.xml`).

## Git e deploy
- Gitflow: trabalhar em `develop`; `main` = produção.
- **Push na `main` dispara deploy automático** (webhook GitHub → Coolify v4 numa VPS, atrás de Traefik com SSL). Só fazer merge/push na `main` com aprovação explícita do Henrique.
- Antes de commitar: `git fetch` e alinhar `develop` com `origin/main` (já houve commits direto na main, ex.: README).
- Build de produção é o `Dockerfile` da raiz (Node 20 + `npm run build` dentro da imagem). A VPS tem só 2 GB de RAM + swap: evitar dependências pesadas de build.
- Acompanhar deploys/logs pelo painel do Coolify no navegador (Projeto → aplicação → Deployments / Logs). Não há CLI/token configurado localmente; se for preciso usar a API, o token fica fora do repositório (ex.: variável de ambiente no `~/.bashrc` do WSL), nunca no chat.

## Armadilhas
- Produção: `ASSET_URL` precisa ser HTTPS (senão Mixed Content atrás do Traefik).
- Produção usa MariaDB (MySQL 8.4 removeu `mysql_native_password`). Evitar SQL específico de MySQL.
- `npx` falhava dentro do container; usar `./node_modules/.bin/<bin>` se precisar.
- Arquivos `*:Zone.Identifier` aparecem ao copiar do Windows para o WSL: não commitar.
- Teste de smoke (`tests/Feature/PacienteFlowTest.php`) verifica um texto do hero; atualizar se a copy mudar.
- `database/seeders/AdminUserSeeder.php` tem credencial fixa no código (histórico público). Ver pendência abaixo.

## Pendências conhecidas
- Mover a credencial do `AdminUserSeeder` para variáveis de ambiente e trocar a senha do admin em produção.

## Próxima funcionalidade (aguardando contrato)
Formulário de anamnese com link único por paciente + API autenticada para o app local da Hellen. **Não implementar antes de existir `INTEGRACAO-CONSULTORIO.md` na raiz.** Dados de pacientes ficam no servidor só até a importação (ou 7 dias).
