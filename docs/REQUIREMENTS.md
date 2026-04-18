# CampFix - Facility Concern Reporting System
## Requirements Analysis & Implementation Status

---

## 1. Executive Summary

CampFix is a web-based facility concern reporting system designed for STI College Novaliches to streamline the process of reporting, tracking, and resolving campus facility issues. The system replaces manual reporting methods (verbal messages, paper forms, informal chats) with a centralized digital platform that ensures transparency, accountability, and efficient resolution of facility concerns.

---

## 2. Stakeholder Requirements Breakdown

### 2.1 Students
**Primary Role:** Concern Reporters

**Implemented Features:**
- ✅ Quick report submission (under 2 minutes)
- ✅ Mobile-friendly responsive interface
- ✅ Status tracking (real-time updates)
- ✅ Report history with filtering
- ✅ Photo upload capability

### 2.2 Faculty Members
**Primary Role:** Concern Reporters (Priority Handling)

**Implemented Features:**
- ✅ Priority flagging (Low, Medium, High, Urgent)
- ✅ Quick access dashboard
- ✅ Classroom/lab category selection
- ✅ Direct status update capability

### 2.3 Administrative Staff
**Primary Role:** Concern Managers & Monitors

**Implemented Features:**
- ✅ Central dashboard with statistics
- ✅ Status management (Pending → Assigned → In Progress → Resolved → Closed)
- ✅ Priority sorting and filtering
- ✅ Filter & search by keywords, status, category, date
- ✅ Assignment system to maintenance personnel
- ✅ CSV export for reports
- ✅ Category management (CRUD)
- ✅ Activity logs/Audit trail
- ✅ Department and phone fields for users

### 2.4 Maintenance Personnel
**Primary Role:** Concern Resolution Specialists

**Implemented Features:**
- ✅ Work order view with priority sorting
- ✅ Detailed location information
- ✅ Status update capability
- ✅ Photo evidence viewing
- ✅ Resolution notes/comments
- ✅ Dedicated maintenance role

### 2.5 System Administrator / IT Support
**Primary Role:** System Managers

**Implemented Features:**
- ✅ User management (Create, Edit, Delete, Suspend)
- ✅ Role management (Student, Faculty, Maintenance, Admin)
- ✅ Bulk user import from CSV
- ✅ System monitoring via activity logs
- ✅ Security management (OTP authentication)
- ✅ Category configuration
- ✅ Database backup capability

---

## 3. Functional Requirements

### 3.1 Authentication & Authorization ✅
- [x] User registration with email validation
- [x] Login with email/password
- [x] OTP (One-Time Password) verification for enhanced security
- [x] Role-based access control (RBAC)
- [x] Session management
- [x] Password reset functionality

**Roles Defined:**
| Role | Permissions |
|------|-------------|
| Student | Submit concerns, view own concerns, update profile |
| Faculty | Submit concerns (with priority), view own concerns, update profile |
| Maintenance | View assigned concerns, update status, add resolution notes |
| Admin | Full system access, user management, reports, settings |

### 3.2 Concern Management ✅
- [x] Create new concern with title, description, location, category
- [x] Upload photo attachments (up to 2MB)
- [x] View concern history
- [x] Filter concerns by status, category, date, priority
- [x] Search concerns by keywords
- [x] Priority levels: Low, Medium, High, Urgent
- [x] Status workflow: Pending → Assigned → In Progress → Resolved → Closed

### 3.3 Admin Dashboard ✅
- [x] Statistics overview (total, pending, resolved concerns)
- [x] Priority overview (Urgent, High, Medium, Low counts)
- [x] Recent concerns list
- [x] Quick status update capability
- [x] Category management (CRUD)
- [x] User management (CRUD)
- [x] CSV user import
- [x] CSV export for reports
- [x] Activity logs

### 3.4 Notifications
- [ ] Email notifications for status changes (optional - requires mail config)

### 3.5 Reporting & Analytics ✅
- [x] Filter by status, priority, category, date
- [x] CSV export
- [x] Activity logging for all actions
- [ ] PDF export (future enhancement)

---

## 4. New Features Added

### 4.1 Priority System
- **Low**: Minor issues that can wait
- **Medium**: Needs attention but not urgent
- **High**: Affecting activities
- **Urgent**: Emergency/Class disruption

### 4.2 Assignment System
- Admin can assign concerns to specific maintenance staff
- Track assignment date and time
- Maintenance staff can view only their assigned concerns

### 4.3 Resolution Notes
- Maintenance staff can add resolution notes
- Track resolution date/time
- Photos can be viewed before resolving

### 4.4 Activity Logging
- All actions are logged with:
  - User who performed the action
  - Timestamp
  - Description
  - Related concern ID

### 4.5 Photo Upload
- Support for JPEG, PNG, JPG, GIF
- Maximum file size: 2MB
- Images stored in secure storage directory
- Viewable in concern details

---

## 5. Database Schema Updates

### Concerns Table (New Fields)
- `priority` - enum('low', 'medium', 'high', 'urgent')
- `assigned_to` - foreign key to users
- `resolution_notes` - text
- `image_path` - string
- `is_anonymous` - boolean
- `resolved_at` - timestamp
- `assigned_at` - timestamp

### Users Table (New Fields)
- `phone` - string
- `department` - string

### Activity Logs Table (New)
- `id`, `user_id`, `action`, `description`, `concern_id`, `timestamps`

---

## 6. API/Routes Added

| Route | Method | Description |
|-------|--------|-------------|
| `/admin/export` | GET | Export concerns to CSV |
| `/assign-concern/{id}` | POST | Assign concern to staff |
| `/resolution-notes/{id}` | POST | Add resolution notes |
| `/admin/logs` | GET | View activity logs |
| `/concerns/create` | GET | Create concern form |
| `/concerns/{id}` | GET | View concern details |
| `/concerns/{id}/edit` | GET | Edit concern form |

---

## 7. Implementation Summary

### Completed Features ✅
1. ✅ User authentication with OTP verification
2. ✅ User roles: Student, Faculty, Maintenance, Admin
3. ✅ Priority levels (Low, Medium, High, Urgent)
4. ✅ Photo upload for concerns
5. ✅ Concern assignment to maintenance staff
6. ✅ Resolution notes
7. ✅ Activity logging system
8. ✅ CSV export for reports
9. ✅ CSV import for users
10. ✅ Filtering by status, priority, category, date
11. ✅ Anonymous reporting option
12. ✅ Enhanced admin dashboard with statistics
13. ✅ Department and phone fields for users
14. ✅ Responsive design

### Future Enhancements 🔧
1. Email notifications (requires mail server configuration)
2. PDF report export
3. SMS notifications
4. Mobile app integration
5. Push notifications
6. Analytics dashboard with charts

---

## 8. How to Use

### For Students:
1. Register/Login with email
2. Complete OTP verification
3. Click "New Concern" to submit
4. Fill in details, add photo if available
5. Track status in "My Concerns"

### For Faculty:
1. Same as student, but can mark as "Urgent"
2. Can mark concerns as "High" priority

### For Maintenance:
1. Login and see assigned concerns
2. Update status as needed
3. Add resolution notes when fixed

### For Admin:
1. Full access to all features
2. Assign concerns to maintenance
3. Generate reports
4. Manage users and categories
5. Monitor activity logs

---

*Last Updated: March 2026*
*Version: 2.0*
