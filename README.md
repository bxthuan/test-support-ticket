# Support Ticket System with AI Agent

A production using Laravel 11 application that automatically analyzes customer support tickets using AI.

## Tech Stack

- **Framework**: Laravel 11 (PHP 8.1)
- **Database**: MySQL 8.0
- **Cache/Queue**: Redis Alpine
- **Web Server**: Nginx
- **Containerization**: Docker & Docker Compose
- **AI Providers**: OpenAI GPT-4, Google Gemini 2.5 Flash, Mock Provider

## Setup Instructions

### Prerequisites

- Docker & Docker Compose installed
- Git
- (Optional) AI API keys for OpenAI or Google Gemini

**⚠️ IMPORTANT for Windows Users:**

If you're using Docker Desktop on Windows, you **MUST** convert the entrypoint scripts to use LF (Linux) line endings instead of CRLF (Windows default). Otherwise, the containers will fail to start.

**Fix line endings before building:**

1. **Using Git (Recommended):**
   ```bash
   # Configure Git to checkout with LF endings
   git config core.autocrlf input
   git rm --cached -r .
   git reset --hard
   ```

2. **Using VS Code:**
   - Open `docker-entrypoint.sh`
   - Click "CRLF" in bottom-right corner
   - Select "LF"
   - Save the file
   - Repeat for `queue-entrypoint.sh`

3. **Using Notepad++:**
   - Edit → EOL Conversion → Unix (LF)
   - Save both `docker-entrypoint.sh` and `queue-entrypoint.sh`

**Verify the fix:**
```bash
# Should show "LF" for both files
file docker-entrypoint.sh
file queue-entrypoint.sh
```

### 1. Clone the Repository

```bash
git clone https://github.com/bxthuan/test-support-ticket.git
cd test-support-ticket
```

### 2. Environment Configuration

```bash
# Copy environment file
cp src/.env.example src/.env

# Configure AI Provider (choose one)
# Option A: Mock Provider (Free, No API key needed)
AI_PROVIDER=mock

# Option B: Google Gemini (Free, 60 requests/min)
AI_PROVIDER=gemini
AI_GEMINI_API_KEY=your_gemini_api_key_here
AI_GEMINI_MODEL=gemini-2.5-flash

# Option C: OpenAI (Paid)
AI_PROVIDER=openai
AI_OPENAI_API_KEY=your_openai_api_key_here
AI_OPENAI_MODEL=gpt-4-turbo-preview
```

**Get Free Gemini API Key**: https://aistudio.google.com/apikey

### 3. Build and Start Docker Containers

```bash
# Build and start all containers
docker-compose up -d --build

# Verify containers are running
docker-compose ps
```

You should see 5 containers running:
- `support-ticket-app` (Laravel PHP-FPM)
- `support-ticket-nginx` (Web server)
- `support-ticket-mysql` (Database)
- `support-ticket-redis` (Cache/Queue)
- `support-ticket-queue` (Queue worker)

### 4. Automatic Setup (Done by Docker)

When containers start, the `docker-entrypoint.sh` script automatically:
- ✅ Installs Composer dependencies (`composer install`)
- ✅ Generates application key (`php artisan key:generate`)
- ✅ Runs database migrations (`php artisan migrate`)
- ✅ Sets proper permissions for storage and cache

**No manual intervention needed!** Just wait ~30 seconds for initial setup to complete.

**Database Tables Created**:
- `tickets` - Stores support tickets with AI analysis results
- `jobs` - Queue jobs table
- `job_batches` - Job batch tracking
- `cache` - Cache storage
- `migrations` - Migration history

### 5. Verify Installation

```bash
# Check application health
curl http://localhost:8000/

# Create a test ticket
curl -X POST http://localhost:8000/api/v1/tickets \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Cannot access my account",
    "description": "I have been trying to login but keep getting error 500"
  }'

# Check queue worker logs
docker-compose logs -f queue

# Retrieve ticket with AI analysis
curl http://localhost:8000/api/v1/tickets/1
```

---

## Running Tests

```bash
# Run all tests
docker-compose exec app php artisan test

# Run specific test class
docker-compose exec app php artisan test --filter=TicketApiTest

# Run with coverage
docker-compose exec app php artisan test --coverage
```

**Test Suite**:
- ✅ 6 Feature Tests (48 assertions)
- ✅ API endpoint validation
- ✅ AI processing workflow
- ✅ Error handling

---

## API Documentation

### Create Ticket

**Endpoint**: `POST /api/v1/tickets`

**Request**:
```json
{
  "title": "Cannot login to my account",
  "description": "Getting error 500 when I try to login"
}
```

**Response** (202 Accepted):
```json
{
  "data": {
    "id": 1,
    "title": "Cannot login to my account",
    "description": "Getting error 500 when I try to login",
    "status": {
      "value": "Open",
      "label": "Open",
      "color": "blue"
    },
    "processing_status": "pending",
    "created_at": "2026-02-08T03:00:00+00:00",
    "updated_at": "2026-02-08T03:00:00+00:00"
  }
}
```

### Get Ticket

**Endpoint**: `GET /api/v1/tickets/{id}`

**Response** (200 OK - After AI Processing):
```json
{
  "data": {
    "id": 1,
    "title": "Cannot login to my account",
    "description": "Getting error 500 when I try to login",
    "status": {
      "value": "Open",
      "label": "Open",
      "color": "blue"
    },
    "ai_analysis": {
      "category": "Technical Support",
      "sentiment": "Frustrated",
      "suggested_reply": "We apologize for the login issue. Our technical team is investigating the error 500 and will have this resolved within the hour.",
      "processed_at": "2026-02-08T03:00:05+00:00"
    },
    "processing_status": "completed",
    "created_at": "2026-02-08T03:00:00+00:00",
    "updated_at": "2026-02-08T03:00:05+00:00"
  }
}
```

---

## AI Prompt Strategy

### Strategy Overview

The AI prompt strategy is designed to ensure consistent, accurate, and production-ready responses by following these principles:

**1. Clear Role Definition**
The system prompt establishes the AI as an "expert customer support ticket analyzer" with specific responsibilities, setting the context and expected behavior upfront.

**2. Explicit Task Breakdown**
Rather than vague instructions, the prompt provides numbered, concrete deliverables:
- Category classification with examples
- Sentiment detection with emotional labels
- Professional reply generation with length constraints (2-4 sentences)

**3. Structured Output Format**
The prompt enforces JSON-only responses with explicit schema definition, eliminating ambiguity and ensuring parseable results. The instruction "No markdown, no explanations" prevents the AI from adding unwanted formatting or commentary.

**4. Example-Driven Learning (Few-Shot Prompting)**
Three diverse examples demonstrate the expected output quality, covering different scenarios (technical issue, positive inquiry, billing problem). These examples serve as implicit training data that guide the AI's response style and tone.

**5. Response Guidelines**
Specific rules ensure consistency:
- Category: Descriptive labels, not rigid enums (allows AI flexibility)
- Sentiment: Emotional accuracy over generic classifications
- Reply: Empathy + Actionability + Professionalism

**6. Validation at Multiple Layers**
- **Prompt Level**: JSON schema enforcement
- **Code Level**: Response parsing with error handling (GeminiProvider.php:84-94)
- **Service Level**: Exception handling with retry logic (AIService.php)

This multi-layered approach ensures that even if the AI deviates slightly, the application gracefully handles edge cases without breaking.

**Result**: 100% success rate in tests with consistent, professional outputs across all three AI providers (OpenAI, Gemini, Mock).

---

## Docker Commands Reference

### Starting Containers (First Time)

When you run `docker-compose up -d` for the first time, the setup process takes **2-3 minutes** to complete automatically:

1. **Composer Install** (~90 seconds) - Downloads all Laravel dependencies
2. **Database Migration** (~10 seconds) - Creates all required tables
3. **Queue Worker Startup** (~5 seconds) - Waits for composer and database to be ready

**Monitor the progress:**

```bash
# Watch app container logs
docker-compose logs -f app

# You'll see output like:
# "Installing composer dependencies..."
# "Running migrations..."
# "Setting permissions..."
# "NOTICE: ready to handle connections"
```

**Verify setup is complete:**

```bash
# Check if composer finished
docker-compose exec app ls vendor/autoload.php

# Check if migrations ran
docker-compose exec app php artisan migrate:status

# Test the application
curl http://localhost:8000/
```

**Note:** Subsequent restarts are much faster (~10 seconds) because composer only reinstalls if `vendor/autoload.php` is missing.

---

### Common Docker Commands

```bash
# Build containers
docker-compose build --no-cache

# Start all containers
docker-compose up -d

# Stop all containers
docker-compose down

# View logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f queue
docker-compose logs -f app

# Restart specific service
docker-compose restart queue

# Execute commands in container
docker-compose exec app php artisan migrate
docker-compose exec app composer install

# Access container shell
docker-compose exec app bash
docker-compose exec mysql mysql -u root -p

# Check queue worker status
docker-compose exec app ps aux | grep queue
```

---

## Project Structure

```
test-support-ticket/
├── src/
│   ├── app/
│   │   ├── Contracts/          # Interfaces
│   │   ├── DTOs/               # Data Transfer Objects
│   │   ├── Enums/              # Enumerations
│   │   ├── Exceptions/         # Custom Exceptions
│   │   ├── Factories/          # Factory Pattern
│   │   ├── Http/
│   │   │   ├── Controllers/    # API Controllers
│   │   │   ├── Requests/       # Form Requests
│   │   │   └── Resources/      # API Resources
│   │   ├── Jobs/               # Queue Jobs
│   │   ├── Models/             # Eloquent Models
│   │   ├── Repositories/       # Repository Pattern
│   │   └── Services/           # Business Logic
│   ├── config/                 # Configuration
│   ├── database/
│   │   ├── factories/          # Model Factories
│   │   └── migrations/         # Database Migrations
│   ├── routes/                 # API Routes
│   ├── tests/                  # Feature Tests
│   └── storage/                # Logs, Cache
├── docker-compose.yml          # Docker orchestration
├── Dockerfile                  # App container
├── Dockerfile.queue            # Queue worker container
└── README.md                   # This file
```

---

## Troubleshooting

### Queue Worker Not Processing

```bash
# Check queue worker logs
docker-compose logs queue

# Restart queue worker
docker-compose restart queue

# Manually run queue worker
docker-compose exec app php artisan queue:work redis --tries=5
```

### Database Connection Issues

```bash
# Check MySQL container
docker-compose ps mysql

# Test database connection
docker-compose exec app php artisan tinker
>>> DB::connection()->getPdo();
```

### Clear Cache

```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
```

---
