# IT Management System - Project Progress

## ✅ Completed Tasks
- [x] **Project Foundation**
    - [x] Directory structure setup.
    - [x] Environment variable handling (`core/Env.php`).
    - [x] Singleton Database connection with PDO (`core/Database.php`).
    - [x] Secure session management (`core/Session.php`).
    - [x] Base Repository pattern implementation.
    - [x] Full database schema design (`install.sql`).
- [x] **Module 1: Authentication & User Management**
    - [x] Secure login with rate limiting. (Remember Me removed as per requirement)
    - [x] Advanced Admin User Management (Modal-based CRUD).
    - [x] Pagination, Sorting & CSV Export for user list.
    - [x] Revision History Feed (integrated with Audit Logs).
    - [x] Profile Photo handling (Upload with 1:1 Resize).
    - [x] Force Password Change on next login.
    - [x] Bilingual Support (English & Bengali) across UI.
    - [x] User Profile management (Info & Password updates).
- [x] **UI Framework & Design**
    - [x] Integrated **Bootstrap Icons** (replaced Font Awesome).
    - [x] Implemented **Tailwind UI Sidebar** (Fixed desktop sidebar, mobile slide-out).
    - [x] Unified dashboard layout with responsive header and breadcrumbs.
- [x] **Module 2: Task Management**
    - [x] Task Repository & Controller logic.
    - [x] Kanban-style **Task Board** UI.
    - [x] Workflow enforcement (Must be "Doing" before "Done").
    - [x] Multi-user assignments and priority levels.
    - [x] Resolved issues and bug fixing complete.

## ⏳ Pending Tasks
- [x] **Module 3: Equipment Management**
    - [x] Graphical UI Form Builder for Dynamic Equipment Types (replacing manual JSON).
    - [x] Predefined Reusable Blocks for standardized field groups.
    - [x] Support for 12+ field types including multi-selection checkboxes.
    - [x] Equipment Inventory with `custom_data` (JSON) storage.
    - [x] Warranty tracking, file uploads, and expiry logic.
    - [x] Referential integrity protection for blocks and types.
- [ ] **Module 4: Network Infrastructure**
    - [ ] Network Node management (IP/MAC/Switch/Patch Panel).
    - [ ] Link equipment to network nodes (Pivot mapping).
    - [ ] Validation: IP/MAC uniqueness and deletion rules.
- [x] **Module 5: Notifications & System Alerts**
    - [x] AJAX polling for real-time notifications.
    - [x] OS Native Notifications (Desktop alerts) with permission management.
    - [x] Full list view with Advanced Filtering (Read/Unread, Date Range, Archived).
    - [x] Enhanced Redirection Logic for all notification types (Task, Equipment, Network, User).
    - [x] Bulk Actions (Mark as Read/Unread, Archive selected items).
    - [x] Archive (Acknowledge) system to clear inbox without deletion.
    - [x] Notification triggers for Tasks, Equipment, and Warranty expiry.
- [x] **Module 6: Administrative Tools & Advanced Logging**
    - [x] Global configuration (System name, upload limits, refresh rates).
    - [x] Priority color management.
    - [x] Log retention settings.
    - [x] Centralized advanced logging (User, IP, UA, JSON context).
    - [x] Advanced Log Filtering & Search (Date range, Category, Level).
    - [x] CSV/TXT Export for system logs.
    - [x] Automatic 90-day log cleanup via MySQL events.
    - [x] Database Maintenance (Backup, Optimize, Cleanup Cache).

## 🛠 Refinement & Cleanup
- [x] UI/UX Refresh: Renamed Settings to Preferences with updated icon (bi-sliders).
- [x] Refined Warranty Notifications: Now alerts all active users at 30, 15, and 0 days.
- [ ] Implement file upload handling for task attachments.
- [ ] Final security audit (Input sanitization review).
- [ ] Documentation for deployment.
