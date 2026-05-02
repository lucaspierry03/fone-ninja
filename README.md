# ERP Estoque

Sistema de gestão de estoque com cadastro de produtos, compras (entrada), vendas (saída), controle de estoque e cálculo de lucro.

## Stack

- **Backend**: PHP 8.5, Laravel 12
- **Frontend**: Vue 3 (Composition API), Vite, Tailwind CSS
- **Banco**: MySQL 8
- **Storage**: MinIO (S3 compatível) — imagens de produtos
- **Infra**: Docker + Docker Compose

## Setup

```bash
cp backend/.env.example backend/.env
docker-compose up --build
```

| Serviço | URL |
|---------|-----|
| Frontend | http://localhost:5173 |
| Backend API | http://localhost:8000/api |
| MinIO Console | http://localhost:9001 (minio / minio123) |

## Arquitetura

```
Controller → Service → Repository → Model
```

- **Controller**: recebe request, delega pro Service/Repository, retorna JSON via API Resource
- **Service**: regras de negócio (custo médio, validação de estoque, lucro)
- **Repository**: queries e persistência no banco
- **Form Requests**: validação isolada
- **API Resources**: controle da serialização das respostas

## Estrutura do projeto

```
├── docker-compose.yml
├── backend/
│   ├── Dockerfile
│   ├── docker-entrypoint.sh
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/    # Controllers da API
│   │   │   ├── Requests/           # Form Requests (validação)
│   │   │   └── Resources/          # API Resources (serialização)
│   │   ├── Services/               # Regras de negócio
│   │   ├── Repositories/           # Acesso ao banco
│   │   ├── Models/                 # Eloquent Models
│   │   └── Enums/                  # VendaStatus
│   └── tests/Feature/              # Testes automatizados
└── frontend/
    ├── Dockerfile
    ├── nginx.conf
    └── src/
        ├── views/                  # Páginas (Produtos, Compras, Vendas)
        ├── components/             # Componentes reutilizáveis
        ├── services/               # Axios API client
        ├── utils/                  # Utilitários (formatação BRL)
        └── router/                 # Vue Router
```

## Endpoints

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | /api/produtos | Listar produtos (paginado) |
| GET | /api/produtos/all | Listar todos os produtos (sem paginação, para selects) |
| POST | /api/produtos | Cadastrar produto (suporta upload de imagem) |
| PUT | /api/produtos/{id} | Atualizar produto |
| DELETE | /api/produtos/{id} | Excluir produto |
| GET | /api/compras | Listar compras (paginado) |
| POST | /api/compras | Registrar compra |
| GET | /api/vendas | Listar vendas (paginado) |
| POST | /api/vendas | Registrar venda |
| PATCH | /api/vendas/{id}/cancelar | Cancelar venda |
| GET | /api/audit-logs | Listar logs de auditoria |

## Decisões técnicas

- **Custo médio ponderado**: recalculado a cada compra com a fórmula `(custo_atual * estoque_atual + preco_novo * qtd_nova) / novo_estoque`
- **lockForUpdate()**: previne race conditions no estoque em cenários concorrentes
- **DB::transaction**: garante atomicidade — se um item falhar, toda a operação é revertida
- **custo_unitario no item da venda**: congela o custo médio no momento da venda para cálculo preciso de lucro histórico
- **Cancelamento**: reverte estoque sem recalcular custo médio (decisão contábil — o custo médio reflete apenas entradas)
- **API Resources**: controlam exatamente quais campos são expostos na API, evitando vazamento de dados internos
- **Validação `distinct`**: impede o mesmo produto duplicado nos itens de uma compra/venda
- **MinIO (S3)**: upload de imagens de produtos com URLs públicas
- **Audit Logs**: registram operações sensíveis com IP, valores antigos e novos (morph relation)
- **Paginação server-side**: 20 itens por página em todas as listagens

## Testes

```bash
docker-compose exec backend composer install
docker-compose exec backend php artisan test
```

| Teste | Cenário |
|-------|---------|
| Produto | Cadastro, listagem, validação de nome mínimo |
| Compra | Incremento de estoque, cálculo de custo médio ponderado com múltiplas compras |
| Venda | Desconto de estoque, cálculo de lucro, rejeição por estoque insuficiente, cancelamento com reversão |
