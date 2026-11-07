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
- [x] Registrar em "Mudanças propostas": `validade_dias` de 1 a 30
- [x] Etapa 1: modelo de convite e criação pelo painel
- [x] Etapa 2: página do paciente com consentimento e formulário de primeira consulta (texto do consentimento ainda RASCUNHO: aprovar com a Hellen antes de publicar)
- [x] Etapa 3: uploads privados (arquivos criptografados em storage/app/private/anamnese)
- [ ] Antes de publicar a anamnese: armazenamento persistente no Coolify para `/var/www/html/storage/app/private` (sem isso, cada deploy apaga anexos ainda não importados)
- [x] Etapa 4: formulário de retorno (5 etapas, JSON da seção 6.3)
- [ ] Etapa 5: API v1 com token
- [ ] Etapa 6: limpeza agendada (exige tarefa agendada no Coolify) e política de privacidade
- [ ] Teste de ponta a ponta com o app local antes de publicar

## Site público e divulgação (sugestões da sessão do Instagram, 2026-11-07)

Ordem: primeiro o que é obrigatório (CFN e LGPD), depois o que faz ser encontrada, depois o que converte e mede. Antes de mostrar preço, promoção, depoimento ou "antes e depois", conferir o Código de Ética do CFN.

### Obrigatório
- [ ] Mostrar o registro profissional (CRN-3) no topo/rodapé da landing e no cabeçalho do formulário de anamnese. Já existe `MARCA_REGISTRO`: preencher no Coolify e exibir na landing
- [ ] Política de privacidade (página `/privacidade`) cobrindo a landing, o formulário de agendamento e a anamnese. Junta com a etapa 6 da anamnese
- [ ] Formulário de agendamento: linha de consentimento ("Ao enviar, você concorda com a política de privacidade") com link
- [ ] Analytics e Pixel só com aviso/consentimento de cookies (LGPD). Nunca nas páginas da anamnese (CSP já bloqueia scripts de terceiros lá)

### Ser encontrada
- [ ] Dizer onde atende: "São Paulo · presencial e online" (hero e rodapé), com o bairro (configurável por ambiente)
- [ ] Link do Instagram no rodapé (configurável: `MARCA_INSTAGRAM`)
- [ ] SEO básico: `<title>` e `meta description` com "nutricionista em São Paulo · emagrecimento · online", `alt` descritivo nas imagens
- [ ] Dados estruturados (schema.org `Dietitian`/`LocalBusiness`) com nome, CRN, cidade/bairro, área de atendimento online, Instagram e WhatsApp
- [ ] Imagem de compartilhamento (Open Graph/Twitter Card) para a prévia no WhatsApp: imagem 1200x630 com logo e chamada
- [ ] Perfil da Empresa no Google (Henrique/Hellen, fora do site): linkar o site; o site cita o mesmo bairro

### Converter
- [ ] Seção de perguntas frequentes (duração, retorno, como funciona o online, pagamento, convênio), com dados estruturados `FAQPage`. Textos aprovados pela Hellen
- [ ] Fotos reais do consultório e da Hellen atendendo (fornecidas por ela, convertidas para WebP)
- [ ] Depoimentos: só com autorização por escrito das pacientes e sem "antes e depois" (conferir o Código de Ética do CFN)
- [ ] Isca digital ("Guia de 7 trocas inteligentes"): página de captura com nome e WhatsApp, consentimento e entrega do PDF. Mesma regra de LGPD do agendamento

### Medir e reaproveitar
- [ ] Analytics com respeito à privacidade (preferir Plausible/Umami, sem cookies; GA4 exige banner de consentimento). Registrar o evento "agendamento enviado"
- [ ] Guardar a origem (UTM) no lead do agendamento e mostrar no painel, para saber quantos agendamentos vêm do Instagram. Funciona mesmo sem analytics. Link da bio: `?utm_source=instagram&utm_medium=bio`
- [ ] Pixel da Meta (opcional, só depois do banner de cookies)
- [ ] Blog simples: cada carrossel vira um artigo curto (avaliar depois do básico acima)

## Depois
- [ ] Avaliar upgrade para Laravel 13 / Livewire 4 (remove `'unsafe-eval'` da CSP)
