# iBridge Backend — Laravel API

REST API powering the iBridge student exam-prep mobile app. Built with **Laravel 11** + **Sanctum** for token-based auth, role-based access control, and a Paystack integration for subscription billing.

---

## Requirements

| Tool | Version |
|---|---|
| PHP | ≥ 8.2 |
| Composer | ≥ 2.x |
| SQLite / MySQL | SQLite (default), MySQL 8 for production |
| Node | ≥ 18 (for Vite assets — optional) |

---

## Local Setup

```bash
# 1. Clone & install dependencies
git clone <repo-url> ibridge-backend
cd ibridge-backend
composer install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Database
php artisan migrate --seed

# 4. Run dev server
php artisan serve          # → http://127.0.0.1:8000
```

> **Mobile testing:** The Expo app points to `http://127.0.0.1:8000/api`. If testing on a physical device, update `EXPO_PUBLIC_API_URL` in the frontend `.env` to your machine's local network IP and change `APP_URL` below accordingly.

---

## Environment Variables

Copy `.env.example` to `.env` and fill in:

```env
APP_NAME=iBridge
APP_ENV=local
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=sqlite          # or mysql for production

# Paystack (subscription billing)
PAYSTACK_SECRET_KEY=sk_test_...
PAYSTACK_PUBLIC_KEY=pk_test_...
```

---

## API Reference

All endpoints are prefixed with `/api`. Authenticated routes require a `Bearer` token obtained from `/login` or `/register`.

### Auth

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| `POST` | `/register` | Public | Register a new student account |
| `POST` | `/login` | Public | Login, returns `user` + `token` |
| `POST` | `/logout` | ✓ | Revoke current token |
| `GET` | `/profile` | ✓ | Get authenticated user profile |
| `PATCH` | `/profile` | ✓ | Update `name` or `exam_board` |

#### Register payload
```json
{
  "name": "Chukwuemeka Obi",
  "email": "emeka@example.com",
  "password": "secret1234",
  "school_code": "SCH-001",     // optional
  "referral_code": "TUTOR123"   // optional
}
```

### Student Routes (`role: student` required)

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/sessions` | List sessions (filterable by `?subject=` `&mode=`) |
| `POST` | `/sessions` | Save a completed exam session |
| `GET` | `/sessions/{id}` | Get a single session |
| `GET` | `/analytics` | Performance summary, subject breakdown, XP & level |
| `GET` | `/leaderboard` | Top students by XP (`?board=WAEC\|JAMB`) |
| `POST` | `/subscription/verify` | Verify a Paystack payment + extend subscription |

#### Save session payload
```json
{
  "subject": "Mathematics",
  "mode": "deep",
  "score": 38,
  "accuracy": 76,
  "time_used": 1842,
  "total_questions": 50,
  "exam_board": "WAEC",
  "weakest_topic": "Algebra",
  "topic_breakdown": { ... },
  "time_per_question": [ ... ],
  "dropped_before_submit": false
}
```

#### Analytics response shape
```json
{
  "data": {
    "total_sessions": 12,
    "avg_accuracy": 74,
    "best_score": 96,
    "weakest_topic": "Algebra",
    "xp": 840,
    "level": 5,
    "xp_to_next_level": 160,
    "streak_days": 4,
    "best_streak": 9,
    "per_subject": [ ... ],
    "mode_breakdown": { "light": 5, "deep": 4, "real": 3 },
    "recent_sessions": [ ... ]
  }
}
```

### Partner Routes (`role: partner`)

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/partner/dashboard` | Referral stats & commission summary |

### School Admin Routes (`role: school_admin`)

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/school/students` | List school's enrolled students |
| `GET` | `/school/students/active-count` | Count of active students |
| `GET` | `/school/summary` | School-level performance summary |

---

## Architecture

```
app/
├── Http/
│   ├── Controllers/Api/   ← Thin controllers, delegate to services
│   ├── Requests/          ← Form request validation
│   └── Resources/         ← API response transformers (UserResource, SessionResource)
├── Services/
│   ├── AuthService.php        ← Register, login, profile update
│   ├── SessionService.php     ← Store session, award XP + streak
│   ├── AnalyticsService.php   ← Aggregate stats, level calculation
│   ├── LeaderboardService.php ← Top-N students by XP, name masking
│   └── SubscriptionService.php ← Paystack verification, expiry extension
└── Models/                ← User, Session, School, Partner …
```

### XP & Levelling (inside `SessionService`)

- **Base XP** = accuracy score
- **Mode multiplier:** `real × 1.5`, `deep × 1.2`, `light × 1.0`
- **Streak logic:** consecutive calendar days with at least one session
- Level thresholds: `100 XP × level²`

---

## Running Tests

```bash
php artisan test
```

---

## Deployment Checklist

- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Use MySQL — update `DB_*` vars
- [ ] Set real Paystack keys (`PAYSTACK_SECRET_KEY`)
- [ ] Set `APP_URL` to your production domain
- [ ] Run `php artisan migrate --force`
- [ ] Configure CORS (`config/cors.php`) to whitelist app domains
