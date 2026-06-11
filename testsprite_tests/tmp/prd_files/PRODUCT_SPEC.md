# CampFix - Product Requirements Document (PRD)

## Product Overview

**Product Name:** CampFix  
**Version:** 1.0  
**Platform:** Web Application (Laravel 12 + Blade + PostgreSQL)  
**Deployment:** Vercel with Supabase Database  
**URL:** https://www.campfixsti.com

## Executive Summary

CampFix is a comprehensive facilities management and event coordination platform designed for educational institutions. It streamlines the process of reporting maintenance concerns, managing facility requests, coordinating events, and tracking resolutions through an integrated role-based workflow system.

## Target Users

1. **Students** - Report concerns and track status
2. **Faculty** - Submit event requests and manage classroom issues
3. **Maintenance Staff** - Receive assignments and update repair status
4. **MIS (Management Information System) Team** - Review and assign concerns
5. **Building Administrators** - Manage staff, facilities, and categories
6. **System Administrators** - Full system access and analytics

## Core Features

### 1. Authentication & User Management

**Authentication Methods:**
- JWT-based API authentication
- Session-based web authentication
- Two-factor authentication via OTP (SMS/Email)
- First-time login password change enforcement
- Account lockout after failed attempts

**User Roles:**
- Student
- Faculty
- Maintenance
- MIS
- Building Admin
- System Admin

**User Management Features:**
- User import via Excel
- Bulk operations (archive, restore, delete)
- Archive folder organization
- Profile management with picture upload
- Password reset and security settings

### 2. Concerns Management Module

**Core Functionality:**
- Submit facility/maintenance concerns
- Attach photos (via Supabase Storage)
- Categorize concerns (e.g., Electrical, Plumbing, HVAC)
- Assign to maintenance staff
- Track status workflow: Pending → In Progress → Resolved → Completed
- Cost tracking for each concern
- Follow-up notifications

**Status Workflow:**
1. **Pending** - Newly submitted
2. **MIS Acknowledged** - Reviewed by MIS team
3. **Assigned** - Assigned to maintenance staff
4. **In Progress** - Being worked on
5. **Resolved** - Fixed, awaiting verification
6. **Completed** - Verified and closed

**User Actions:**
- Create, edit, delete concerns
- Archive/restore concerns
- Soft delete with recovery option
- Batch operations (archive, delete, restore)
- Send follow-up reminders

**Admin Actions:**
- Assign to maintenance staff
- Update status and add resolution notes
- Track costs and materials
- Generate analytics and reports

### 3. Reports Module

**Functionality:**
- Submit detailed incident/damage reports
- Attach supporting documentation
- Track resolution progress
- Archive management
- Status updates similar to concerns

### 4. Event Requests Module

**Core Features:**
- Faculty event request submission
- Resource booking:
  - Classrooms/Rooms
  - Courts (Sports facilities)
  - Audio-Visual Rooms (AVR)
- Date/time conflict detection
- Approval workflow
- Event calendar visualization
- Excel import for bulk events
- PDF export for approved events

**Event Workflow:**
1. **Pending** - Awaiting approval
2. **Approved** - Approved by principal/admin
3. **Rejected** - Denied with reason
4. **Cancelled** - Cancelled by requester

**Event Features:**
- Real-time availability checking
- Multi-resource booking
- Discussion/chat system for each event
- Archive and restore functionality
- Batch operations

### 5. Facilities Management

**Facility Types:**
- Classrooms
- Laboratories
- Courts
- Audio-Visual Rooms
- Common Areas

**Facility Management:**
- Add/edit/delete facilities
- Track status (Available, Under Maintenance, Unavailable)
- Location mapping
- Capacity tracking
- Maintenance history

### 6. Categories Management

**Features:**
- Create maintenance categories
- Assign icons and colors
- Link to specific concern types
- Examples:
  - Electrical Issues
  - Plumbing
  - HVAC/Climate Control
  - Structural Damage
  - Security Issues
  - Cleanliness

### 7. Notifications System

**Notification Channels:**
- In-app notifications
- Email notifications (via Brevo SMTP)
- SMS notifications (via UnisSMS)
- Push notifications (via OneSignal)

**Notification Types:**
- New assignment
- Status updates
- Approval/rejection alerts
- Follow-up reminders
- System announcements

### 8. Analytics & Reporting

**Admin Analytics:**
- Concern statistics by:
  - Status
  - Category
  - Location
  - Time period
  - Cost analysis
- Resolution time tracking
- Staff performance metrics
- Trend analysis

**Export Options:**
- CSV export
- PDF reports with charts
- Period breakdown reports
- Location-specific reports
- Cost analysis reports
- Status distribution reports

### 9. Archive System

**Archive Features:**
- Custom archive folders
- Organized archiving for:
  - Concerns
  - Reports
  - Events
  - Users
  - Activity logs
- Bulk restore operations
- Auto-delete preferences (30/60/90 days)
- Soft delete with permanent delete option

### 10. Settings & Preferences

**User Settings:**
- Notification preferences (Email, SMS, Push, In-app)
- Theme selection (Light/Dark mode)
- Privacy settings
- Security settings
- Auto-delete preferences

**System Settings:**
- CORS configuration
- Session management
- Email/SMS provider config
- Push notification setup
- Database connection settings

### 11. Security Features

**Implemented Security Measures:**
- OWASP compliance
- Rate limiting on sensitive routes
- JWT token authentication
- CSRF protection
- XSS prevention
- SQL injection protection
- Password hashing (bcrypt)
- Session encryption
- Secure cookie settings
- Account lockout mechanism
- Security access verification for sensitive actions

### 12. Activity Logging

**Logged Actions:**
- User logins/logouts
- Concern creation/updates
- Status changes
- Assignment actions
- Deletions and restorations
- Archive operations
- Administrative actions

**Log Management:**
- Archive logs to folders
- View log details
- Restore archived logs
- Delete old logs

## API Endpoints

### Authentication
- `POST /api/auth/login` - User login
- `POST /api/auth/register` - User registration
- `POST /api/auth/logout` - User logout
- `GET /api/auth/user` - Get authenticated user
- `POST /api/auth/refresh` - Refresh JWT token

### Concerns
- `GET /api/concerns` - List user's concerns
- `POST /api/concerns` - Create new concern
- `GET /api/concerns/{id}` - Get concern details
- `PUT /api/concerns/{id}` - Update concern
- `DELETE /api/concerns/{id}` - Delete concern

### Events
- `GET /api/events` - List events
- `POST /api/events` - Create event request
- `GET /api/events/{id}` - Get event details

### Categories
- `GET /api/categories` - List all categories
- `POST /api/categories` - Create category (Admin)
- `DELETE /api/categories/{id}` - Delete category (Admin)

## Technical Stack

**Backend:**
- PHP 8.2
- Laravel 12
- PostgreSQL (Supabase)
- JWT Authentication (tymon/jwt-auth)

**Frontend:**
- Blade templating
- Alpine.js
- TailwindCSS
- Chart.js for analytics

**Storage:**
- Supabase Storage for file uploads

**Email:**
- Brevo SMTP

**SMS:**
- UnisSMS API

**Push Notifications:**
- OneSignal

**PDF Generation:**
- DomPDF (barryvdh/laravel-dompdf)

## Database Schema

**Key Tables:**
- users
- concerns
- reports
- event_requests
- categories
- facilities
- maintenance_staff
- archive_folders
- activity_logs
- notifications
- event_discussions

## User Workflows

### Student Workflow
1. Login with credentials + OTP
2. View dashboard
3. Submit new concern with photo
4. Track concern status
5. Receive notifications on updates
6. Archive resolved concerns

### Maintenance Staff Workflow
1. Login to view assigned concerns
2. Update status as work progresses
3. Add resolution notes
4. Mark as resolved when complete
5. View workload and history

### Admin Workflow
1. Login to admin dashboard
2. Review pending concerns
3. Assign to maintenance staff
4. Monitor resolution progress
5. Generate reports and analytics
6. Manage users and facilities

### Faculty Event Request Workflow
1. Login and navigate to Events
2. Create new event request
3. Select date, time, and resources
4. System checks availability
5. Submit for approval
6. Receive approval/rejection notification
7. View event in calendar

## Success Metrics

- Average concern resolution time
- User satisfaction ratings
- Number of concerns resolved per month
- System uptime and reliability
- User adoption rate
- Event approval turnaround time

## Future Enhancements

- Mobile app (iOS/Android)
- Real-time chat support
- AI-powered concern categorization
- Predictive maintenance scheduling
- Integration with school management systems
- QR code scanning for facility reporting

## Compliance & Standards

- OWASP Top 10 security practices
- GDPR data privacy considerations
- Accessibility standards (WCAG)
- RESTful API design principles

---

**Document Version:** 1.0  
**Last Updated:** June 2026  
**Contact:** mercuriofranck9@gmail.com
