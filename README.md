# Order Management System

## 📋 Prerequisites

- **Docker** (20.x or higher)
- **Docker Compose** (2.x or higher)
- **Make**

## 🚀 Getting Started

1. **Clone and start the application:**
   ```bash
   git clone https://github.com/Derane/order-management-system
   cd order-management-system
   make start
   make run-composer
   ```

2. **Setup database:**
   ```bash
   make migrate-api
   ```

3. **Run tests:**
   ```bash
   make test-setup
   make codecept
   ```

4. **Access the API:**
   - API: http://localhost:8080/api
   - MailCatcher: http://localhost:1080

## 📖 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/orders` | List orders with pagination/filtering |
| GET | `/api/orders/{id}` | Get order by ID |
| POST | `/api/orders` | Create new order |
| PUT | `/api/orders/{id}` | Update order |
| PATCH | `/api/orders/{id}/status` | Update order status |
| DELETE | `/api/orders/{id}` | Delete order |

### Example Request
```bash
# Create order
curl -X POST http://localhost:8080/api/orders \
  -H "Content-Type: application/json" \
  -d '{
    "customerName": "John Doe",
    "customerEmail": "john@example.com",
    "items": [
      {
        "productName": "Laptop",
        "quantity": 1,
        "price": 999.99
      }
    ]
  }'
```

## 🔧 Development Commands

```bash
make start          # Start all services
make down           # Stop all services
make migrate-api    # Run database migrations
make codecept       # Run functional tests
make run-consumer   # Start email worker
```
