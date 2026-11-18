# FitTrack

**FitTrack** is a responsive fitness and workout tracking web application. Members create personal accounts, log their workouts, review their activity analytics, set weekly goals, and receive encouraging messages based on their progress.

> **Project note:** This project was originally developed as private client work. This package is a portfolio/demo version and contains no client credentials, private URLs, personal data, or identifying client information. 

## What's included

### Public website and accounts
- Public landing page introducing FitTrack and its features.
- Account registration with name, email, password and confirmation.
- Login and logout.
- Passwords stored as PHP password hashes (`password_hash` / `password_verify`), never as plain text.
- Session-based authentication with session ID regeneration and HTTP-only, SameSite cookies.
- CSRF token validation for data-changing requests and logout.
- Protected dashboard and API: visitors must log in before accessing personal data.

### Personal fitness workspace
- Dashboard with weekly workout count, active minutes, estimated calories and goal completion.
- Workout logging: name, type, date, duration, calories, intensity, perceived effort (1–10), and optional notes.
- Workout history with recent sessions, current-week filtering and deletion.
- Personal weekly workout target, saved to the member's account.
- Progress analytics: weekly activity, workout frequency and calorie charts.
- Motivational messages and personal insights that respond to recorded activity.
- Responsive desktop, tablet and mobile interface.

### Multi-user data separation
- Every registered account has a unique internal ID.
- Workout records are stored in a separate file for each account (`data/users/<user-id>.json`).
- The API derives the account ID from the authenticated server session. It does not accept a user ID from the browser, preventing one member from requesting another member's workout file through the API.
- Weekly goals are stored with the corresponding account record.

## Technology

| Layer | Technology | Responsibility |
|---|---|---|
| Public pages and dashboard | HTML5 + PHP | Landing page, registration, login, protected workspace |
| Styling | CSS3 | Responsive interface and visual system |
| Client logic | JavaScript (ES6+) | Workout forms, dashboard calculations and charts |
| Server | PHP 8.1+ | Sessions, authentication, validation and JSON API |
| Prototype storage | JSON files | Account records and per-user workout records |
| Charts | HTML Canvas | Custom charts without an external chart dependency |

## Project structure

```text
FitTrack/
├── index.php                 # Public landing page
├── register.php              # Account registration
├── login.php                 # Login
├── logout.php                # CSRF-protected logout
├── dashboard.php             # Authenticated member dashboard
├── router.php                # Local PHP server router; blocks /data URLs
├── README.md
├── .htaccess                 # Apache access restrictions
├── assets/
│   ├── app.js
│   ├── style.css
│   ├── auth.css
│   └── landing.css
├── api/
│   ├── bootstrap.php         # Session, CSRF and storage helpers
│   └── workouts.php          # Authenticated per-user workout API
└── data/
    ├── .htaccess             # Denies direct access on Apache
    ├── users.json            # Account records (password hashes)
    └── users/                # One workout file per account
```

## Run locally

You need PHP 8.1 or newer. No Composer packages or database server are required for this prototype.

1. Extract the project ZIP.
2. Open a terminal in the `FitTrack` folder.
3. Start the PHP development server using the included router:

   ```bash
   php -S localhost:8000 router.php
   ```

4. Visit [http://localhost:8000](http://localhost:8000).
5. Select **Get started**, create an account, and log in. You can create multiple accounts to verify that each one has a separate workout history and weekly goal.

The PHP process needs permission to write to `data/` and `data/users/`. The included `router.php` prevents direct HTTP access to those folders when using PHP's built-in server. Apache deployments are also covered by the `.htaccess` files. If your host uses Nginx or another server, configure an equivalent deny rule for the entire `/data` directory before deploying.

## Main routes

| Route | Access | Purpose |
|---|---|---|
| `index.php` | Public | Marketing / welcome page |
| `register.php` | Public | Create a member account |
| `login.php` | Public | Authenticate an existing member |
| `dashboard.php` | Signed in | Personal fitness workspace |
| `logout.php` | Signed in, POST only | End the current session |
| `api/workouts.php` | Signed in | Read and save the current member's workouts and weekly goal |

## API behavior

`api/workouts.php` requires an authenticated session.

- `GET` returns the signed-in member's workouts, weekly target and CSRF token.
- `POST` accepts the current member's workout collection and weekly target. The request must include the CSRF token in the `X-CSRF-Token` header.
- Other HTTP methods are rejected.
- Workout values are validated and bounded on the server before being written.
- The API never takes a member ID from the request body.

## Data model

A member record in `data/users.json` contains an internal ID, display name, email, password hash, weekly target and creation timestamp. A member's workout file contains an array of objects similar to:

```json
{
  "id": "unique-workout-id",
  "name": "Morning strength session",
  "type": "Strength",
  "date": "2026-11-18",
  "duration": 45,
  "calories": 320,
  "intensity": "Moderate",
  "pressure": 6,
  "notes": "Felt strong and focused."
}
```

## Technical Scope & Future Improvements

FitTrack currently demonstrates a functional multi-user fitness tracking application, including user registration, authentication, session management, workout tracking and per-account data separation.

The project uses PHP and JSON-based storage to provide a lightweight, easy-to-run prototype. For a production deployment, the following improvements are planned:

1. **Database scalability:** Migrate JSON storage to MySQL or PostgreSQL, with proper indexing, constraints and transaction management.
2. **Security:** Implement HTTPS, environment-based configuration, login rate limiting and additional security testing.
3. **Account management:** Add email verification, password recovery, account deletion and session/device management.
4. **Privacy and compliance:** Introduce privacy policies, data retention rules and appropriate regulatory compliance.
5. **Deployment and reliability:** Configure automated backups, monitoring, logging and a structured deployment workflow.
6. **Commercial capabilities:** Integrate payment processing and subscription management if required.

These improvements represent the next development steps toward a scalable, secure commercial service.
## Health Data Disclaimer

Calorie values are user-provided estimates and should not be interpreted as medically accurate measurements or professional health advice. This prototype is not designed to collect or store sensitive medical information.

**FitTrack — Your journey, your pace.**
