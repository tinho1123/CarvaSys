# PRD: Client Portal API

**Module:** Client REST API  
**Version:** 1.0  
**Last Updated:** February 4, 2026  
**Status:** Implemented (Phase 1)

---

## 1. Module Overview

The Client Portal API provides RESTful endpoints for the client-facing application. It enables clients to browse products, create orders, manage credit balances, view notifications, and process payments.

### Key Features
- Product catalog browsing with search and filters
- Order creation and history
- Credit balance and transaction tracking
- Notification management
- Payment processing via Stripe
- Sanctum token-based authentication

---

## 2. Authentication

### Authentication Method
**Laravel Sanctum** - Token-based authentication

### Token Generation
```php
$client = Client::find(1);
$token = $client->createToken('carvaSys')->plainTextToken;
```

### Request Headers
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
X-Tenant-ID: {company_uuid} (optional, overrides session)
```

---

## 3. API Endpoints

### 3.1 Product Endpoints

#### GET /api/client/companies/{company}/products

**Description:** List products with pagination, search, and filters

**Parameters:**
- `search` (string, optional) - Search by name or description
- `category_id` (integer, optional) - Filter by category
- `featured` (boolean, optional) - Show only featured products
- `per_page` (integer, optional, default: 15) - Items per page

**Response (200 OK):**
```json
{
  "data": [
    {
      "uuid": "abc-123",
      "name": "Product Name",
      "description": "Product description",
      "price": "99.99",
      "image": "/storage/products/image.jpg",
      "category": {
        "uuid": "cat-456",
        "name": "Category Name"
      },
      "featured": true,
      "active": true
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 45,
    "last_page": 3
  }
}
```

---

#### GET /api/client/companies/{company}/products/{product}

**Description:** Get single product details

**Response (200 OK):**
```json
{
  "uuid": "abc-123",
  "name": "Product Name",
  "description": "Detailed product description",
  "price": "99.99",
  "image": "/storage/products/image.jpg",
  "category": {
    "uuid": "cat-456",
    "name": "Category Name",
    "description": "Category description"
  },
  "featured": true,
  "active": true,
  "created_at": "2026-01-15T10:30:00Z"
}
```

**Response (404 Not Found):**
```json
{
  "message": "Product not found"
}
```

---

#### GET /api/client/companies/{company}/categories

**Description:** List categories with featured products

**Response (200 OK):**
```json
{
  "data": [
    {
      "uuid": "cat-123",
      "name": "Electronics",
      "description": "Electronic products",
      "featured_products": [
        {
          "uuid": "prod-456",
          "name": "Smartphone",
          "price": "1299.99",
          "image": "/storage/products/phone.jpg"
        }
      ]
    }
  ]
}
```

---

### 3.2 Order Endpoints

#### GET /api/client/companies/{company}/orders

**Description:** List client's order history

**Parameters:**
- `status` (string, optional) - Filter by status
- `per_page` (integer, optional, default: 15)

**Response (200 OK):**
```json
{
  "data": [
    {
      "uuid": "order-123",
      "status": "delivered",
      "subtotal": "150.00",
      "discount_amount": "10.00",
      "fee_amount": "5.00",
      "total_amount": "145.00",
      "items_count": 3,
      "created_at": "2026-02-01T14:20:00Z",
      "delivered_at": "2026-02-03T10:15:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 12
  }
}
```

---

#### GET /api/client/companies/{company}/orders/{order}

**Description:** Get order details with line items

**Response (200 OK):**
```json
{
  "uuid": "order-123",
  "status": "delivered",
  "subtotal": "150.00",
  "discount_amount": "10.00",
  "fee_amount": "5.00",
  "total_amount": "145.00",
  "notes": "Deliver to back door",
  "items": [
    {
      "uuid": "item-456",
      "product_name": "Product A",
      "quantity": 2,
      "unit_price": "50.00",
      "discount_amount": "5.00",
      "total_amount": "95.00"
    }
  ],
  "created_at": "2026-02-01T14:20:00Z",
  "confirmed_at": "2026-02-01T15:00:00Z",
  "shipped_at": "2026-02-02T09:00:00Z",
  "delivered_at": "2026-02-03T10:15:00Z"
}
```

---

#### POST /api/client/companies/{company}/orders

**Description:** Create new order

**Request Body:**
```json
{
  "items": [
    {
      "product_uuid": "prod-123",
      "quantity": 2
    },
    {
      "product_uuid": "prod-456",
      "quantity": 1
    }
  ],
  "notes": "Optional delivery notes"
}
```

**Response (201 Created):**
```json
{
  "uuid": "order-789",
  "status": "pending",
  "subtotal": "200.00",
  "total_amount": "200.00",
  "items": [
    {
      "product_name": "Product A",
      "quantity": 2,
      "unit_price": "50.00",
      "total_amount": "100.00"
    }
  ],
  "created_at": "2026-02-04T18:30:00Z"
}
```

**Response (422 Unprocessable Entity):**
```json
{
  "message": "Validation failed",
  "errors": {
    "items.0.product_uuid": ["Product not found"],
    "items.1.quantity": ["Quantity must be at least 1"]
  }
}
```

---

### 3.3 Credit Endpoints

#### GET /api/client/companies/{company}/client/credit-balance

**Description:** Get current credit balance and summary

**Response (200 OK):**
```json
{
  "total_credit": "5000.00",
  "total_debt": "1250.50",
  "available_credit": "3749.50",
  "overdue_amount": "200.00",
  "next_payment_due": "2026-02-15",
  "transactions_count": 15
}
```

---

#### GET /api/client/companies/{company}/client/transaction-history

**Description:** List credit transaction history

**Parameters:**
- `per_page` (integer, optional, default: 15)

**Response (200 OK):**
```json
{
  "data": [
    {
      "uuid": "trans-123",
      "product_name": "Product A",
      "quantity": 2,
      "favored_total": "100.00",
      "favored_paid_amount": "30.00",
      "remaining_balance": "70.00",
      "created_at": "2026-01-20T10:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 15
  }
}
```

---

#### GET /api/client/companies/{company}/client/upcoming-payments

**Description:** Get upcoming payments grouped by due date

**Response (200 OK):**
```json
{
  "data": [
    {
      "due_date": "2026-02-15",
      "total_amount": "350.00",
      "transactions": [
        {
          "uuid": "trans-123",
          "product_name": "Product A",
          "amount_due": "150.00"
        },
        {
          "uuid": "trans-456",
          "product_name": "Product B",
          "amount_due": "200.00"
        }
      ]
    }
  ]
}
```

---

### 3.4 Notification Endpoints

#### GET /api/client/notifications

**Description:** List notifications for authenticated client

**Parameters:**
- `read` (boolean, optional) - Filter by read status
- `type` (string, optional) - Filter by type
- `per_page` (integer, optional, default: 20)

**Response (200 OK):**
```json
{
  "data": [
    {
      "uuid": "notif-123",
      "type": "order_update",
      "title": "Order Shipped",
      "description": "Your order #789 has been shipped",
      "message": "Track your order at...",
      "action_url": "/orders/order-789",
      "read_at": null,
      "created_at": "2026-02-04T15:30:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 8,
    "unread_count": 3
  }
}
```

---

#### GET /api/client/notifications/unread-count

**Description:** Get count of unread notifications

**Response (200 OK):**
```json
{
  "unread_count": 5
}
```

---

#### POST /api/client/notifications/{notification}/read

**Description:** Mark notification as read

**Response (200 OK):**
```json
{
  "uuid": "notif-123",
  "read_at": "2026-02-04T18:45:00Z"
}
```

---

#### POST /api/client/notifications/mark-all-read

**Description:** Mark all notifications as read

**Response (200 OK):**
```json
{
  "message": "All notifications marked as read",
  "count": 5
}
```

---

### 3.5 Payment Endpoints

#### POST /api/client/companies/{company}/payments/create-intent

**Description:** Create Stripe PaymentIntent

**Request Body:**
```json
{
  "amount": "100.00",
  "favored_transaction_uuid": "trans-123"
}
```

**Response (200 OK):**
```json
{
  "client_secret": "pi_xxx_secret_yyy",
  "payment_intent_id": "pi_xxx",
  "amount": "100.00",
  "currency": "brl"
}
```

**Response (400 Bad Request):**
```json
{
  "error": "Amount must be positive"
}
```

---

#### POST /api/client/companies/{company}/payments/confirm

**Description:** Confirm payment with Stripe

**Request Body:**
```json
{
  "payment_intent_id": "pi_xxx",
  "favored_transaction_uuid": "trans-123"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "payment_status": "succeeded",
  "amount_paid": "100.00",
  "remaining_balance": "50.00"
}
```

**Response (400 Bad Request):**
```json
{
  "error": "Payment failed: insufficient funds"
}
```

---

## 4. Error Responses

### Standard Error Format

```json
{
  "message": "Error description",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

### HTTP Status Codes

| Code | Meaning | Usage |
|------|---------|-------|
| 200 | OK | Successful GET/POST/PUT |
| 201 | Created | Resource created successfully |
| 400 | Bad Request | Invalid request data |
| 401 | Unauthorized | Missing or invalid token |
| 403 | Forbidden | No access to resource |
| 404 | Not Found | Resource doesn't exist |
| 422 | Unprocessable Entity | Validation failed |
| 500 | Internal Server Error | Server error |
| 502 | Bad Gateway | External API error (Stripe) |

---

## 5. Rate Limiting

**Limits:**
- 60 requests per minute per client
- 10 login attempts per minute per IP

**Headers:**
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1643990400
```

**Response (429 Too Many Requests):**
```json
{
  "message": "Too many requests. Please try again later."
}
```

---

## 6. Pagination

**Query Parameters:**
- `page` (integer, default: 1)
- `per_page` (integer, default: 15, max: 100)

**Response Meta:**
```json
{
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 45,
    "last_page": 3,
    "from": 1,
    "to": 15
  },
  "links": {
    "first": "/api/client/products?page=1",
    "last": "/api/client/products?page=3",
    "prev": null,
    "next": "/api/client/products?page=2"
  }
}
```

---

## 7. Implementation Files

### Controllers
- `app/Http/Controllers/Api/Client/ProductController.php`
- `app/Http/Controllers/Api/Client/OrderController.php`
- `app/Http/Controllers/Api/Client/CreditController.php`
- `app/Http/Controllers/Api/Client/NotificationController.php`
- `app/Http/Controllers/Api/Client/PaymentController.php`

### Routes
- `routes/api_client.php`

### Middleware
- `auth:sanctum` - Token authentication
- `SetClientTenant` - Tenant resolution

---

## 8. Testing Strategy

### API Tests
```php
public function test_client_can_list_products()
{
    $client = Client::factory()->create();
    $company = Company::factory()->create();
    $client->companies()->attach($company);
    
    $token = $client->createToken('test')->plainTextToken;
    
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson("/api/client/companies/{$company->uuid}/products");
    
    $response->assertStatus(200)
             ->assertJsonStructure(['data', 'meta']);
}
```

---

## 9. Future Enhancements

### Phase 2
- [ ] Webhook endpoints for Stripe events
- [ ] Payment history endpoint
- [ ] Bulk order creation
- [ ] Product favorites/wishlist
- [ ] Order tracking with real-time updates

### Phase 3
- [ ] GraphQL API
- [ ] WebSocket support for real-time notifications
- [ ] API versioning (v2)

---

## 10. References

- [AGENTS.md](../../../../AGENTS.md)
- [Laravel Sanctum Docs](https://laravel.com/docs/sanctum)
- [Stripe API Docs](https://stripe.com/docs/api)

---

**Document Owner:** API Team  
**Stakeholders:** Frontend, Mobile, Product  
**Review Cycle:** Quarterly
