# Integração Site x Consultório Nutri (contrato v1.0)

Este arquivo é o contrato entre duas partes que se desenvolvem em sessões separadas:

- SITE (Laravel, repositório Nutri-Hellen-Site): coleta as anamneses dos pacientes. É uma caixa de correio. Não gera plano alimentar
- APP LOCAL (Consultório Nutri, repositório nutricao-hellen, pasta app/): busca as respostas, o profissional revisa e salva, e o plano é feito localmente

Fonte da verdade: este arquivo. A cópia canônica fica na raiz do repositório do site (INTEGRACAO-CONSULTORIO.md). Quem quiser mudar algo registra em "Mudanças propostas" no fim e avisa o Henrique. Não alterar campos existentes sem subir a versão do esquema.

O repositório do site é PÚBLICO: este arquivo não pode conter token, segredo, endereço interno nem dado de paciente.

## 1. Princípios

- O site só coleta. Plano, PDF e pôster são feitos localmente
- Dados de saúde são sensíveis (LGPD). O servidor guarda o mínimo e pelo menor tempo possível: depois de importadas (ou em 7 dias), as respostas e os arquivos são APAGADOS do servidor
- Proibido pedir CPF, RG ou endereço
- O paciente responde pelo celular, em cerca de 10 minutos
- A marca (nome, logo, cores) é configurável por ambiente, para o mesmo site servir outras nutricionistas no futuro

## 2. Fluxo

1. O profissional cria um CONVITE: pelo painel do site ou pela API. O convite gera um link único para um paciente, com validade (padrão 7 dias)
2. O profissional envia o link ao paciente pelo WhatsApp
3. O paciente abre o link, aceita o consentimento (LGPD), responde e anexa os arquivos (exames, bioimpedância, outros)
4. O app local lista as respostas pendentes, baixa a resposta e os arquivos, e abre o formulário local já preenchido para revisão
5. Ao salvar com sucesso no computador local, o app chama "importado". O site apaga a resposta e os arquivos
6. Respostas não importadas são apagadas 7 dias depois do envio. Convites não respondidos expiram na validade e são apagados

## 3. Tipos de convite

- `primeira`: formulário completo (anamnese)
- `retorno`: versão curta

Campo opcional `paciente_ref` (texto): identificador da pasta local do paciente (ex.: `raniele-barreto-oliveira`). O site apenas o guarda e devolve; não interpreta.

Campo opcional `nome_paciente`: só para o profissional identificar o convite no painel.

## 4. Autenticação e segurança da API

- HTTPS obrigatório. Todas as rotas `/api/v1/*` exigem `Authorization: Bearer <token>`
- O token fica no ambiente do servidor (guardado como hash) e no app local (arquivo fora do git). Nunca no repositório
- Limite de requisições por IP e por token. Respostas de erro sem detalhes internos
- Link do paciente: token aleatório de pelo menos 40 caracteres, validade, e depois do envio o link deixa de funcionar
- Páginas do paciente: `noindex`, sem scripts de terceiros nem analytics, cabeçalhos de segurança (CSP, HSTS, X-Content-Type-Options)
- Campos das respostas criptografados no banco (cast criptografado do Laravel) e arquivos em disco PRIVADO (nunca em pasta pública), com nome gerado ao acaso e o nome original só como metadado
- Logs e mensagens de erro sem dado de saúde
- Atenção aos backups do banco e do servidor: eles não devem reter respostas além da retenção definida aqui

## 5. Endpoints da API (v1)

Todos respondem JSON, exceto o download de arquivo.

- `GET /api/v1/saude`
  - Resposta: `{"ok": true, "versao_esquema": "1.0"}`

- `POST /api/v1/convites`
  - Corpo: `{"tipo": "primeira" | "retorno", "nome_paciente": "...", "paciente_ref": "...", "validade_dias": 7}`
  - Resposta 201: `{"id": "<uuid>", "url": "https://.../anamnese/<token>", "expira_em": "2026-10-30T23:59:59-03:00"}`

- `DELETE /api/v1/convites/{id}`: cancela e apaga o convite (e qualquer resposta dele)

- `GET /api/v1/respostas?status=pendente`
  - Lista as respostas já enviadas e ainda não importadas
  - Resposta: `{"dados": [{"id": "<uuid>", "convite_id": "<uuid>", "tipo": "primeira", "nome_paciente": "...", "paciente_ref": null, "enviado_em": "...", "qtd_arquivos": 3}]}`

- `GET /api/v1/respostas/{id}`: devolve a resposta completa (esquema da seção 6), incluindo a lista de arquivos com `id`, `categoria`, `nome_original`, `mime` e `tamanho_bytes`

- `GET /api/v1/respostas/{id}/arquivos/{arquivo_id}`: baixa o arquivo (stream) com o `Content-Type` correto

- `POST /api/v1/respostas/{id}/importado`
  - Marca como importada e APAGA a resposta e os arquivos do servidor. Idempotente: se já foi apagada, responde 204
  - O app local só chama depois de salvar tudo localmente com sucesso

Erros: `401` token inválido, `404` não encontrado, `422` validação, `429` limite. Formato: `{"erro": "mensagem curta"}`.

## 6. Esquema da resposta (versao_esquema 1.0)

Convenções: datas em ISO (`AAAA-MM-DD`), números como número JSON, campos opcionais podem faltar ou vir `null`. Os textos livres vêm exatamente como o paciente digitou.

### 6.1 Resposta de primeira consulta

```json
{
  "id": "uuid",
  "versao_esquema": "1.0",
  "tipo": "primeira",
  "convite_id": "uuid",
  "paciente_ref": null,
  "enviado_em": "2026-10-27T14:03:00-03:00",
  "consentimento": {"aceito": true, "texto_versao": "2026-10-01", "aceito_em": "2026-10-27T14:01:00-03:00"},
  "identificacao": {
    "nome": "Maria da Silva",
    "nascimento": "1990-05-20",
    "idade_informada": null,
    "sexo": "Feminino",
    "altura_cm": 164,
    "peso_kg": 82.0,
    "gestante": true,
    "semanas_gestacao": 14,
    "peso_pre_gestacional_kg": 84.0
  },
  "rotina": {
    "profissao": "Auxiliar administrativo",
    "horario_trabalho": "Segunda a sexta, das 8h às 18h",
    "objetivo": "Emagrecimento",
    "metas_secundarias": {"nao_tem": false, "texto": "melhorar a disposição"}
  },
  "atividade_fisica": {"nao_pratica": false, "lista": [{"tipo": "caminhada", "frequencia": "3x/semana", "duracao": "30 min", "horario": "10h às 11h20"}]},
  "sono_habitos": {
    "sono_horas": 7.0, "sono_horario": "00h às 07h", "sono_reparador": "Às vezes",
    "estresse": "Muito alto", "hidratacao": "1,5 L por dia", "intestino": "1 vez ao dia"
  },
  "alimentacao": {
    "alergias": {"resposta": "Não", "texto": null},
    "padrao_alimentar": "Onívoro",
    "aversoes": {"resposta": "Sim", "texto": "jiló e melão"},
    "compulsao": {"nao_tem": false, "texto": "doce à noite"},
    "orcamento": "Baixo",
    "culinaria": "Básica"
  },
  "saude": {
    "condicoes": ["Dislipidemia (colesterol / triglicerídeos)"],
    "condicoes_outras": "",
    "medicamentos": {"nao_toma": false, "lista": [{"nome": "levotiroxina", "dose": null, "horario": null, "nao_informado": true}]},
    "suplementos": {"nao_usa": false, "lista": [{"nome": "ácido fólico", "dose": "5 mg", "horario": "manhã", "nao_informado": false}]},
    "historico_familiar": ["Diabetes", "Hipertensão"],
    "historico_familiar_obs": "mãe"
  },
  "observacoes": {"sem_observacoes": false, "texto": "..."},
  "arquivos": {
    "sem_exames": false, "sem_bio": true, "sem_outros": true,
    "lista": [{"id": "uuid", "categoria": "exames", "nome_original": "hemograma.pdf", "mime": "application/pdf", "tamanho_bytes": 220114}]
  }
}
```

### 6.2 Regras e valores permitidos

- `identificacao.nome`: obrigatório
- `nascimento` OU `idade_informada` (inteiro de 0 a 120): pelo menos um é obrigatório. Se o nascimento for informado, a idade não é enviada
- `sexo`: `Feminino` | `Masculino`
- `altura_cm`: de 40 a 230. `peso_kg`: de 2 a 350 (uma casa decimal)
- `gestante`: booleano. Se verdadeiro, `semanas_gestacao` (1 a 42) e `peso_pre_gestacional_kg` (30 a 250) são pedidos
- `objetivo`: `Emagrecimento` | `Ganho de massa muscular` | `Reeducação alimentar` | `Controle de condição de saúde` | `Saúde na gestação` | texto livre quando o paciente escolhe "Outro"
- `metas_secundarias`, `compulsao`, `observacoes`: cada um tem um indicador "não tem" (`nao_tem` / `sem_observacoes`) e o texto. Com o indicador verdadeiro, o texto não é pedido
- `atividade_fisica.nao_pratica`: se verdadeiro, `lista` vem vazia. Cada item: `tipo`, `frequencia`, `duracao`, `horario` (texto; `horario` a partir da 1.2, pode vir `null`)
- `sono_horas`: de 0 a 16 (passo 0,5). `sono_reparador`: `Sim` | `Não` | `Às vezes`
- `estresse`: `Baixo` | `Moderado` | `Alto` | `Muito alto`
- `alergias` e `aversoes`: `resposta` `Não` | `Sim`. Com `Sim`, o `texto` descreve
- `padrao_alimentar`: `Onívoro` | `Vegetariano` | `Vegano`
- `orcamento`: `Baixo` | `Médio` | `Alto`
- `culinaria`: `Nenhuma` | `Básica` | `Boa` | `Ótima`
- `condicoes` (lista de marcação múltipla): `SOMP (antiga SOP)`, `Hipotireoidismo`, `Hipertireoidismo`, `Nódulo de tireoide / bócio`, `Diabetes tipo 2`, `Pré-diabetes / resistência à insulina`, `Hipertensão`, `Dislipidemia (colesterol / triglicerídeos)`, `Esteatose hepática`, `Gastrite / refluxo`, `Intestino irritável / constipação crônica`, `Anemia / carência de ferro`, `Endometriose`, `Ansiedade / depressão`, `Doença celíaca`, `Doença renal crônica`. Mais `condicoes_outras` em texto livre
- `medicamentos` e `suplementos`: o indicador (`nao_toma` / `nao_usa`) com lista vazia, ou uma lista de itens com `nome` (obrigatório), `dose`, `horario` e `nao_informado`. Se `nao_informado` for verdadeiro, dose e horário vêm `null` e significam "o paciente não sabe"
- `historico_familiar`: `Diabetes` | `Colesterol` | `Hipertensão` | `Doença cardíaca precoce` | `Câncer` (marcação múltipla), mais `historico_familiar_obs` em texto
- NÃO fazem parte do formulário do paciente: `jejum_12h` (decisão da nutricionista) e CPF, RG, endereço

### 6.3 Resposta de retorno

```json
{
  "id": "uuid",
  "versao_esquema": "1.0",
  "tipo": "retorno",
  "convite_id": "uuid",
  "paciente_ref": "raniele-barreto-oliveira",
  "enviado_em": "2026-11-20T09:00:00-03:00",
  "consentimento": {"aceito": true, "texto_versao": "2026-10-01", "aceito_em": "2026-11-20T08:58:00-03:00"},
  "identificacao": {"nome": "Raniele Barreto Oliveira", "peso_kg": 85.0},
  "retorno": {
    "adesao": "Boa",
    "dificuldades": "...",
    "mudancas": "...",
    "atividade": "caminhada 3x por semana",
    "sono": "7 horas",
    "intestino": "normal",
    "hidratacao": "2 L",
    "condicoes_novas": ""
  },
  "saude": {
    "medicamentos": {"nao_toma": true, "lista": []},
    "suplementos": {"nao_usa": false, "lista": []}
  },
  "observacoes": {"sem_observacoes": true, "texto": null},
  "arquivos": {"sem_exames": false, "sem_bio": true, "sem_outros": true, "lista": []}
}
```

- `adesao`: `Baixa` | `Parcial` | `Boa` | `Total`
- `peso_kg` obrigatório. Os demais campos de texto são opcionais
- `medicamentos` e `suplementos` seguem as mesmas regras da primeira consulta
- O retorno pode vir de convite com `paciente_ref`. O site nunca confere se o paciente existe localmente

## 7. Arquivos

- Categorias: `exames` | `bio` | `outros`
- Tipos aceitos: PDF, PNG, JPG, JPEG, HEIC, WEBP. Conferir o tipo real do arquivo (não só a extensão)
- Máximo de 15 MB por arquivo, 15 arquivos e 60 MB por resposta
- Para cada categoria, o paciente anexa arquivos OU marca "não tenho" (`sem_exames`, `sem_bio`, `sem_outros`). Esses indicadores precisam chegar ao app local, que registra "paciente não tem" e não "esqueceu de enviar"
- O app local renomeia os arquivos ao salvar (AAAA-MM-DD-nome)

## 8. Página do paciente

- Mobile first. Formulário em etapas curtas, com barra de progresso, na identidade visual do site
- Mesma lógica do formulário do app local (Sim ou Não que abre campo, N/I, "não tem", gestante, nascimento ou idade, linhas de atividade, medicamentos e suplementos com adicionar e apagar)
- Consentimento no início, obrigatório. O texto vem de um arquivo de configuração versionado (`texto_versao`) e é aprovado pela nutricionista antes de entrar no ar. Registrar versão e data e hora do aceite
- Rascunho: o paciente pode voltar ao link antes de enviar. Rascunho também é apagado na validade
- Depois do envio: tela de agradecimento e o link deixa de funcionar
- Link inválido, expirado ou já usado: mensagem genérica, sem revelar detalhes
- Página de política de privacidade com o tratamento descrito neste contrato

## 9. Retenção e limpeza

- Tarefa agendada diária que apaga: convites expirados, rascunhos expirados e respostas não importadas com mais de 7 dias do envio, junto dos arquivos
- Apagar de verdade (banco e disco). Verificar se os backups do banco não guardam essas linhas além do prazo
- A nutricionista pode apagar qualquer convite ou resposta manualmente no painel

## 10. Painel administrativo do site

- Já existe painel com login próprio. Acrescentar: criar convite (tipo, nome, validade) e copiar o link, listar convites e respostas por status (criado, respondido, expirado) e cancelar convite
- O painel NÃO mostra o conteúdo clínico das respostas por padrão (a revisão acontece no app local). Se mostrar, só para a nutricionista logada

## 11. Marca configurável

Pelo ambiente ou por arquivo de configuração: nome da nutricionista, registro profissional, logo, cores, texto de consentimento, e-mail ou WhatsApp de contato.

## 12. Versionamento

- `versao_esquema` em toda resposta e em `/api/v1/saude`
- Mudança compatível (campo novo opcional): sobe a versão menor (1.1). Mudança que quebra: sobe a maior (2.0) e a rota vira `/api/v2`
- O app local rejeita versão maior desconhecida e avisa para atualizar

## 13. Responsabilidades

SITE (sessão nutri-hellen-site):
- Páginas do paciente, convites, armazenamento privado, API, limpeza, painel, testes automatizados

APP LOCAL (sessão das consultas):
- Importador: listar, baixar, mapear para o formulário local e revisar, salvar (pasta, anamnese, prontuário, dados.json, INDICE) e só então chamar `importado`
- Botão "Enviar link ao paciente" (cria o convite pela API e mostra o link para copiar)
- Guardar o token da API fora do git

## 14. Critérios de aceite

- Convite criado pela API aparece no painel e o link funciona uma única vez
- Resposta completa (com e sem gestação, com e sem nascimento, com e sem arquivos) chega pela API exatamente no esquema da seção 6
- `importado` apaga resposta e arquivos (conferir no banco e no disco)
- Resposta não importada some após 7 dias
- Nenhum dado de paciente aparece em log, em página pública ou no repositório
- Testes automatizados cobrindo: autenticação da API, validações, limpeza e o fluxo de convite

## Mudanças propostas

### 1. Limites de `validade_dias` e expiração no fim do dia (site, 2026-11-06, aprovada pelo Henrique)

- `validade_dias` aceita inteiro de 1 a 30. Padrão 7. Fora disso: `422`
- `expira_em` é o fim do dia (23:59:59, horário de Brasília) do último dia de validade, como no exemplo da seção 5
- O link continua utilizável até o envio do formulário ou até `expira_em`, o que vier primeiro (rascunho incluído)
- Compatível: não muda campos nem a versão do esquema

### 2. "Não tenho" em saúde e histórico familiar; água em faixas (site, 2026-11-06, pedida pelo Henrique)

Mudança compatível: só acrescenta campos opcionais. `versao_esquema` passa a `1.1`.

- `saude.sem_condicoes` (booleano, novo): o paciente marcou "Não tenho problemas de saúde". Com `true`, `condicoes` vem vazia e `condicoes_outras` vazio. Com `false`, há pelo menos uma condição marcada ou `condicoes_outras` preenchido
- `saude.historico_familiar_nao_tem` (booleano, novo): "Não há doenças na família". Com `true`, `historico_familiar` vem vazia e `historico_familiar_obs` vazio
- `saude.historico_familiar_nao_sabe` (booleano, novo): "Não sei". Mesma regra de lista vazia
- Os dois indicadores de histórico nunca vêm `true` juntos. Com ambos `false`, o paciente marcou pelo menos uma doença ou escreveu em `historico_familiar_obs`
- `sono_habitos.hidratacao` continua texto, mas passa a vir sempre de uma destas opções: `Menos de 1 litro` | `1 a 2 litros` | `2 a 3 litros` | `Mais de 3 litros`
- Para o app local: tratar a ausência dos campos novos (resposta 1.0) como `false`

### 3. Detalhes da API v1 (site, 2026-11-07)

Esclarecimentos de comportamento, sem mudar campos:

- `POST /api/v1/respostas/{id}/importado` apaga também o convite (nome e `paciente_ref`), não só a resposta e os arquivos: depois da importação o site não guarda nada do paciente
- `DELETE /api/v1/convites/{id}` responde `204`; convite inexistente responde `404`
- `validade_dias` é opcional no `POST /api/v1/convites` (padrão 7)
- Rascunhos nunca aparecem na API: só respostas enviadas
- Download de arquivo: `Content-Disposition: attachment` com o nome original em `filename*` (UTF-8)
- Limite padrão: 60 requisições por minuto, por IP e por token
- O token é gerado no servidor com `php artisan anamnese:token`: o comando mostra o token (vai só para o app local) e o hash (vai para `ANAMNESE_API_TOKEN_HASH` no ambiente do servidor)

### 4. Horário da atividade física (site, 2026-11-14, pedida pelo Henrique)

Mudança compatível: só acrescenta um campo opcional. `versao_esquema` passa a `1.2`.

- `atividade_fisica.lista[].horario` (texto livre, até 80 caracteres, ou `null`): quando o paciente costuma praticar, ex.: `10h às 11h20`. Serve para montar os horários das refeições
- Para o app local: tratar a ausência do campo (respostas 1.0 e 1.1) como `null`
