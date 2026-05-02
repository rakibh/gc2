# GEMINI.md - IT Management System

## Project Overview
A comprehensive web-based IT management system designed for tracking equipment, network infrastructure, task assignment, and system maintenance in an office environment. The system supports multi-user role-based access control (Admin/Standard User) and features dynamic forms for equipment inventory.

### Key Features
- **Dynamic Equipment Inventory:** Uses custom JSON schema for flexible field definitions.
- **Network Infrastructure Management:** Links equipment to network nodes (IP/MAC/Switch/Patch Panel).
- **Task Management:** Board-style workflow (To Do, Doing, Past Due, Done, Dropped) with attachments and priority levels.
- **Real-time Notifications:** AJAX polling for system-wide and user-specific alerts.
- **Administrative Tools:** Database backup, optimization, repair, and system logs.

## Technology Stack
- **Backend:** PHP 8.3.14 (Strict types, PDO, Repository pattern)
- **Database:** MySQL 9.1.0 (InnoDB, utf8mb4)
- **Frontend:**
  - Tailwind CSS (Runtime utility generation via CDN)
  - Alpine.js (Reactive UI state management)
  - Vanilla JavaScript + Fetch API
  - Bootstrap Icons (Main UI Icons)
  - Font Awesome 6 (Legacy/Specific Icons)
- **Server:** Apache 2.4.62.1 (WAMP environment)

## Building and Running
### Local Development
1. **Environment:** Ensure PHP 8.3+ and MySQL 9.1+ are installed (e.g., via WAMP64).
2. **Database Setup:**
   - Create a database named as per your `.env` configuration.
   - Run the initial `install.sql` (TODO: Locate or create the installation script).
3. **Configuration:**
   - Create a `.env` file in the root directory (copy from `.env.example` if available).
   - Set database credentials and system preferences.
4. **Web Server:**
   - Point your Apache virtual host to the `public/` directory (or the root depending on final structure).

### Testing
- **TODO:** Implement and document test commands (e.g., PHPUnit).

## Development Conventions
- **Strict Typing:** All PHP files must start with `declare(strict_types=1);`.
- **Security:**
  - Use PDO prepared statements for all database interactions.
  - CSRF validation is mandatory for all POST/AJAX requests.
  - Password storage: Use `password_hash()` for production (Note: PRD mentioned MD5 for dev, but standardizes on upgrade).
- **Architecture:** Follow the Repository pattern for data access.
- **Code Structure:**
  - `/config`: Configuration files and `.env` handling.
  - `/core`: Core system logic and base classes.
  - `/modules`: Individual feature modules (Auth, Tasks, Equipment, etc.).
  - `/views`: UI templates.
  - `/public`: Entry point and static assets.
  - `/storage`: Uploads, logs, and backups.

## Key Files
- `prd.txt`: Comprehensive Product Requirements Document.
- `GEMINI.md`: This instructional context file.
