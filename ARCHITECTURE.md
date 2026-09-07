# VestCasa — Mapa da Arquitetura

## Infraestrutura

| | |
|---|---|
| **App server** | `192.168.10.248` — Apache + PHP (SSH: user=asa) |
| **DB server** | `192.168.10.249` — MySQL |
| **Banco** | `vestcasa` (secundário: `atacadao`) |
| **Credenciais DB** | user=asa / pass=palmas@2026 |
| **Pasta remota** | `/var/www/html/vestcasa` |
| **Timezone** | America/Araguaina |

---

## Estrutura de Diretórios

```
/var/www/html/vestcasa/
├── index.php                  → redireciona para view/index.php
├── view/                      → telas HTML+PHP (frontend)
│   ├── headers.php
│   ├── footer.php
│   ├── index.php              → dashboard
│   ├── fcx_fechamento.php
│   ├── fcx_deposito.php
│   ├── rme_relatorios.php
│   ├── avarias_relatorios.php
│   ├── ope_produtos_consulta.php
│   ├── ope_produtos_consulta_new.php
│   ├── ope_precos_alterados.php
│   ├── mercadoria_ajuste.php
│   ├── fila_execucao.php
│   ├── com_vendas.php
│   └── vencimento.php
├── controller/                → ~80 endpoints back_*.php (AJAX)
├── assets/
│   ├── init.php               → define BD_SERVIDOR, BD_USUARIO, BD_SENHA, BD_BANCO
│   ├── _db/
│   │   ├── db.php             → mysqli + PDO
│   │   ├── migrations/        → DDL das tabelas vest_*
│   │   └── functions/         → exportData.php, syncTable.php
│   ├── _php/libs/             → PhpSpreadsheet, RedBeanPHP, Propel, Symfony, CKFinder, ZipStream
│   ├── python/                → workers e automações Python
│   │   └── .env               → credenciais ERP legado + DB
│   ├── js/                    → JS por módulo (graficos, fila, rme, avarias...)
│   ├── css/
│   ├── dist/                  → AdminLTE (css/js/img)
│   └── plugins/               → jQuery, DataTables, ApexCharts, Chart.js, Select2, SweetAlert2...
└── uploads/
```

**Stack:** PHP (mysqli + PDO) · AdminLTE 3 · Bootstrap 4 · jQuery · MySQL

---

## Módulos

| Módulo | View | Controllers |
|--------|------|-------------|
| Dashboard | `view/index.php` | `back_select_dashboard`, `back_grafico_venda`, `back_status_db` |
| Fechamento Caixa | `view/fcx_fechamento.php` | `back_fcx_fechamento_*`, `back_fcx_colaborador_*`, `back_fcx_funcao_*`, `back_fcx_horario_*` |
| Depósitos | `view/fcx_deposito.php` | `back_fcx_deposito_*` |
| RME (Recebimento) | `view/rme_relatorios.php` | `back_rme_relatorio_*` |
| Avarias | `view/avarias_relatorios.php` | `back_avarias_relatorio_*`, `back_avaria_*` |
| Consulta Produtos | `view/ope_produtos_consulta.php` | `back_ope_consulta_produtos_*` |
| Preços Alterados | `view/ope_precos_alterados.php` | `back_ope_precos_alterados_*` |
| Ajuste Mercadoria | `view/mercadoria_ajuste.php` | `back_mercadoria_ajuste_*` |
| Fila Execução | `view/fila_execucao.php` | `back_fila_*`, `back_fila_execucao_sse` |
| Vendas | `view/com_vendas.php` | `back_com_vendas`, `back_venda_dia`, `back_venda_meta` |
| ISV | — | `back_dash_isv`, `back_select_detalhe_itens_isv_*` |
| Boletos | — | `back_boleto_*` |
| Terceiros | — | `back_dash_terceiros_*` |
| Quebras | — | `back_quebra_*` |
| Água | — | `back_agua_*` |
| Cafeteria | — | `back_select_cafeteria_*` |

---

## Tabelas do Banco (vest_*)

### Cadastro
- `vest_produtos` · `vest_produto_cadastro` · `vest_produto_codigo_barras`
- `vest_produto_precos` · `vest_produto_precos_historico` · `vest_fornecedor`

### Estoque
- `vest_produto_estoque` · `vest_produto_extrato` · `vest_produto_movimentacao_log`
- `vest_mercadoria_ajuste`

### RME
- `vest_rme_recebimento` · `vest_rme_recebimento_itens` · `vest_rme_recebimento_divergencias`

### Fechamento de Caixa
- `vest_fechamento` · `vest_deposito` · `vest_deposito_itens`
- `vest_colaborador` · `vest_colab_funcao` · `vest_colab_horario`

### Avarias
- `vest_avarias_relatorio` · `vest_avarias_relatorio_itens`
- `vest_avarias_relatorio_fotos` · `vest_avaria_tipos`

### Vendas / Relatórios
- `vest_relatorio_vendas` · `vest_relatorio_vendas_itens`
- `vest_relatorio_vendas_nota` · `vest_relatorio_vendas_pagamentos` · `vest_relatorio_vendas_meta`

### Infra
- `vest_relatorio_fila_execucao` (status: 0=aguardando, 1=executando, 2=concluído)
- `vest_instagram` · `vest_produtos_codigos`

---

## Workers Python (/assets/python/)

| Script | Função |
|--------|--------|
| `worker_fila_execucao.py` | Worker FIFO — processa fila `vest_relatorio_fila_execucao` |
| `atualizaVenda.py` | Sincroniza vendas do ERP → MySQL |
| `atualizaAvarias.py` | Atualiza registros de avarias |
| `atualizaAvariaRelatorio.py` | Relatório de avarias via automação |
| `consultaPreco.py` | Consulta preços no sistema legado (ERP) |
| `atualizaInstagram.py` | Integração/automação Instagram |
| `consultaInstaVestCasa.py` | Consulta dados do Instagram |
| `criarAvaria.py` | Cria avarias automaticamente |
| `gerarCookiesInstagram.py` | Renova cookies de sessão do Instagram |

**Credenciais ERP legado** (em `assets/python/.env`):
- `VEST_USER=ANDRE.ALVES` / `VEST_PASS=006755`
- `VEST_USER_PRECO=ETIQUETAS.PALMAS` / `VEST_PASS_PRECO=784512`
- `VEST_USER_AVARIA=mega.palmas@vestcasa.com.br`

---

## Fila de Execução (SSE)

PHP cria registro em `vest_relatorio_fila_execucao` com status=0.
Python worker (`worker_fila_execucao.py`) roda em loop, pega próximo FIFO (status=0),
executa o script, atualiza status para 1 (executando) → 2 (concluído).
Frontend escuta via Server-Sent Events (`back_fila_execucao_sse.php`).
