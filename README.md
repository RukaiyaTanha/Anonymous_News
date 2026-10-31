# Anonymous News Portal

An anonymous news reporting and verification platform built with **Laravel, PHP, and PostgreSQL**. The platform allows registered users to submit news reports without publicly revealing their identities, browse published news, and participate in community-based reporting. An AI-assisted verification workflow and admin moderation system help maintain content quality and platform integrity.

## Project Overview

The Anonymous News Portal provides a platform where users can share news and information while maintaining their public anonymity. Submitted reports go through an AI-assisted analysis and administrative review process before becoming publicly available.

The system includes separate interfaces for regular users and administrators, with features for report submission, content moderation, credibility management, community engagement, and activity monitoring.

## Key Features

### User Features

- **User Authentication:** Account registration, login, logout, and password management.
- **Anonymous News Submission:** Submit news reports with titles, summaries, detailed descriptions, categories, and supporting media without displaying the reporter's identity publicly.
- **Personal Dashboard:** Track submitted reports and monitor their publication status.
- **News Browsing:** Explore verified and published news reports through category-based navigation.
- **Search and Filtering:** Find reports using keywords and available filtering options.
- **Community Engagement:** Upvote reports and flag potentially misleading or suspicious content.
- **Notifications:** Receive updates about report verification, approval, and rejection.
- **Profile Management:** Manage account information and personal settings.

### AI-Assisted Features

- **AI-Based Content Analysis:** Analyze submitted news reports and generate an AI confidence score.
- **Credibility Assessment:** Support the verification process through credibility scoring.
- **Duplicate Report Detection:** Identify potentially similar reports during the moderation process.

### Admin Features

- **Admin Dashboard:** Monitor platform activity, reports, and user statistics.
- **Report Moderation:** Review pending submissions and approve or reject reports.
- **Verified Reports Management:** Manage published news reports.
- **User Management:** Monitor user accounts and manage restricted or banned users.
- **Category Management:** Create, update, and manage news categories.
- **Audit Logs:** Maintain administrative activity records for accountability.
- **Moderation Controls:** Review flagged reports, manage credibility scores, and monitor suspicious submissions.

## News Verification Workflow

The platform follows a structured report submission and moderation process.

1. **Report Submission:** An authenticated user submits a news report.
2. **AI Analysis:** The system dispatches an AI-assisted analysis job and generates a confidence score.
3. **Moderation Queue:** The report enters the administrator's review queue.
4. **Admin Review:** The administrator reviews the report, AI analysis, duplicate similarity, and reporter reputation.
5. **Final Decision:** The administrator approves or rejects the submission.
6. **Publication:** Approved reports become publicly accessible.
7. **Notification:** The submitting user receives an update about the decision.

## User Roles

| Role | Permissions |
|---|---|
| Guest | Browse and read published news |
| Registered User | Submit reports, upvote, flag reports, and manage personal submissions |
| Administrator | Moderate reports, manage users, categories, and audit logs |

## Technology Stack

| Technology | Purpose |
|---|---|
| Laravel | Backend framework |
| PHP | Server-side development |
| PostgreSQL | Relational database |
| JavaScript | Frontend interactions |
| HTML5 | Web page structure |
| CSS3 | Interface styling |
| AI API | AI-assisted content analysis |

