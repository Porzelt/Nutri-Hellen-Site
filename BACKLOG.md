# Backlog

Pendências do site, em ordem de execução. Marcar `[x]` ao concluir. Repositório público: nada de IPs, senhas ou detalhes de infraestrutura sensíveis aqui.

## Infraestrutura (Coolify / VPS)
- [x] Backup diário do banco no Coolify, retenção de 7 dias (alinhado à retenção do contrato)
- [x] Ligar OPcache (`PHP_OPCACHE_ENABLE=1`) e redeploy
- [x] Ligar `AUTORUN_ENABLED=true` (optimize + migrate a cada deploy) e redeploy
- [x] Firewall da VPS na DigitalOcean: liberar só SSH, HTTP e HTTPS (Henrique)
- [x] Aplicar atualizações do sistema e reiniciar a VPS (Docker 29, MariaDB 11.8.9; DB_HOST passou a usar o nome interno do banco em vez de IP fixo)
- [x] Verificar e aplicar as "Changes pending" do MariaDB no Coolify
- [x] Healthcheck da aplicação apontando para `/up` (porta 8080, deploy só troca o container se o novo estiver saudável)
- [x] Redirecionar `www` para o domínio principal (301 permanente, preserva caminho e parâmetros)

## Desempenho do site
- [x] Converter imagens para WebP (1,7 MB → 130 KB; `home-hellen.png` não era usada e foi removida)

## Anamnese (contrato `INTEGRACAO-CONSULTORIO.md` v1.0, plano aprovado)
- [ ] Registrar em "Mudanças propostas": `validade_dias` de 1 a 30
- [ ] Etapa 1: modelo de convite e criação pelo painel
- [ ] Etapa 2: página do paciente com consentimento e formulário de primeira consulta
- [ ] Etapa 3: uploads privados (exige armazenamento persistente no Coolify)
- [ ] Etapa 4: formulário de retorno
- [ ] Etapa 5: API v1 com token
- [ ] Etapa 6: limpeza agendada (exige tarefa agendada no Coolify) e política de privacidade
- [ ] Teste de ponta a ponta com o app local antes de publicar

## Depois
- [ ] Avaliar upgrade para Laravel 13 / Livewire 4 (remove `'unsafe-eval'` da CSP)
