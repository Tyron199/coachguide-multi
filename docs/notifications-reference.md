# Notifications Reference

This document lists all email notifications in the CoachGuide system, including their messages and content.

---

## Table of Contents
1. [User Invitations](#user-invitations)
2. [Role Additions](#role-additions)
3. [Contract Notifications](#contract-notifications)

---

## User Invitations

### 1. Send Coach Invitation
**File:** `SendCoachInvitation.php`  
**Purpose:** Sends an invite to a coach to join the platform. Admins can send this when adding a coach or manually resend invites.  
**Subject:** "You're invited to join {AppName} as a Coach"

**Message Content:**
```
Hello {CoachName}!

Congratulations! You've been invited to join {AppName} as a coach.

As a coach on our platform, you'll have access to powerful tools designed to enhance your coaching practice and help you better serve your clients.

What you can do as a coach:
• Manage your client roster and track their progress
• Schedule and organize coaching sessions
• Create and assign coaching notes and tasks
• Use coaching frameworks and assessment tools
• Track client objectives and development goals
• Integrate with your calendar (Google Calendar, Outlook)
• Generate contracts and manage client agreements

Getting started:
Click the button below to create your coach account and start exploring the platform.

[Join as Coach Button]

Your registration details will be pre-filled for your convenience - you'll just need to set your password and complete your profile.

If you have any questions about the platform or need assistance getting started, please don't hesitate to reach out to our support team.

Welcome to the team!
The {AppName} Team
```

---

### 2. Send Admin Invitation
**File:** `SendAdminInvitation.php`  
**Purpose:** Sends an invite to an admin to join the platform. Admins can send this when adding another admin or manually resend invites.  
**Subject:** "You're invited to join {AppName} as an Administrator"

**Message Content:**
```
Hello {AdminName}!

You've been invited to join {AppName} as an administrator.

As an administrator, you'll have full access to manage the platform and oversee all coaching operations within your organization.

Administrative privileges include:
• Complete user management (coaches, clients, and other administrators)
• Platform configuration and customization
• Subscription and billing management
• Company and organizational structure management
• Full access to all coaching sessions, notes, and tasks
• System-wide reporting and analytics
• Theme and branding customization

Your responsibilities:
As an administrator, you'll help ensure the smooth operation of the coaching platform and support both coaches and clients in achieving their goals.

Getting started:
Click the button below to create your administrator account and access the full platform.

[Join as Administrator Button]

Your registration details will be pre-filled for your convenience - you'll just need to set your password and complete your profile.

If you have any questions about your administrative role or need assistance with platform management, please contact our support team.

Welcome to the administrative team!
The {AppName} Team
```

---

### 3. Send Client Invitation
**File:** `SendClientInvitation.php`  
**Purpose:** Sends an invite to a client to join the platform. Coaches can send this when adding a client or manually resend invites.  
**Subject:** "You're invited to join {AppName}"

**Message Content:**
```
Hello {ClientName}!

Great news! {CoachName} has invited you to join {AppName}.

As your coach, {CoachName} is already using {AppName} to manage your coaching journey, track your progress, and schedule sessions.

What you can do on the platform:
• View your coaching sessions and progress
• Access your personalized goals and objectives
• Communicate with your coach
• Track your development journey

Getting started is optional - your coach can continue working with you whether you join the platform or not. However, if you'd like to take advantage of these features, simply click the button below to create your account.

[Join the Platform Button]

Your registration details will be pre-filled for your convenience - you'll just need to set your password.

If you have any questions, feel free to reach out to {CoachName} directly.

Best regards,
The {AppName} Team
```

---

## Role Additions

### 4. Admin Role Added
**File:** `AdminRoleAdded.php`  
**Purpose:** Notifies an existing user that they've been granted administrator privileges.  
**Subject:** "You've been granted Administrator privileges on {AppName}"

**Message Content:**
```
Hello {UserName}!

Congratulations! You've been granted administrator privileges on {AppName}.

In addition to your existing access, you now have administrative capabilities that will allow you to:

New administrative features available to you:
• Complete user management (coaches, clients, and other administrators)
• Platform configuration and customization
• Subscription and billing management
• Company and organizational structure management
• Full access to all coaching sessions, notes, and tasks
• System-wide reporting and analytics
• Theme and branding customization

Your expanded responsibilities:
As an administrator, you'll help ensure the smooth operation of the coaching platform and support both coaches and clients in achieving their goals.

Getting started with administration:
You can access your new administrative features immediately by logging into your existing account. Look for the admin sections in your navigation menu.

If you have any questions about your new administrative role or need assistance with platform management, please contact our support team.

Welcome to the administrative team!
The {AppName} Team
```

---

### 5. Coach Role Added
**File:** `CoachRoleAdded.php`  
**Purpose:** Notifies an existing user that they've been granted coach privileges.  
**Subject:** "You've been granted Coach privileges on {AppName}"

**Message Content:**
```
Hello {UserName}!

Great news! You've been granted coach privileges on {AppName}.

In addition to your existing access, you now have coaching capabilities that will allow you to:

New coach features available to you:
• Manage your own client roster and track their progress
• Schedule and organize coaching sessions
• Create and assign coaching notes and tasks
• Use coaching frameworks and assessment tools
• Track client objectives and development goals
• Integrate with your calendar for session management
• Generate contracts and manage client agreements

Getting started with coaching:
You can access your new coaching features immediately by logging into your existing account. Look for the coaching sections in your navigation menu.

If you have any questions about your new coaching privileges or need assistance getting started with the coaching tools, please don't hesitate to reach out to our support team.

Welcome to the coaching team!
The {AppName} Team
```

---

## Contract Notifications

### 6. Send Contract Signing Request (Client)
**File:** `SendContractSigningRequestClient.php`  
**Purpose:** Sent to clients when a coach creates a contract and hits the "send" button.  
**Subject:** "Contract Ready for Your Signature"

**Message Content:**
```
Hello {ClientName},

Your coach, {CoachName}, has prepared a coaching contract for your review and signature.

Please take a moment to review the terms and conditions, then provide your digital signature to proceed.

[Review and Sign Contract Button]

This link is secure and unique to you. Please do not share it with others.

If you have any questions about the contract terms, please contact your coach directly.

Thank you for choosing our coaching services!
```

---

### 7. Send Contract Signing Request (Coach)
**File:** `SendContractSigningRequestCoach.php`  
**Purpose:** Sent to coaches when a client has signed and the coach needs to countersign.  
**Subject:** "Client Signed - Contract Ready for Your Countersignature"

**Message Content:**
```
Hello {CoachName},

Great news! Your client, {ClientName}, has signed the coaching contract.

The contract is now ready for your countersignature to make it fully executed.

[Review and Countersign Contract Button]

This link is secure and unique to you. Please do not share it with others.

Once you countersign, both you and your client will receive the final executed contract.

Thank you for using our coaching platform!
```

---

### 8. Contract Fully Executed
**File:** `ContractFullyExecuted.php`  
**Purpose:** Sent to both coaches and clients when the contract has been fully executed by both parties.  
**Subject:** "Contract Fully Executed - Download Available"

**Message Content (Coach Version):**
```
Hello {CoachName},

Great news! The coaching contract between you and {ClientName} has been fully executed.

Both parties have now signed the contract, making it legally binding.

You can now download your signed copy of the contract for your records.

[View and Download Contract Button]
[View Contract Button]

We recommend keeping a copy of this signed contract in a safe place.

Thank you for completing the contract signing process!
```

**Message Content (Client Version):**
```
Hello {ClientName},

Great news! The coaching contract between you and {CoachName} has been fully executed.

Both parties have now signed the contract, making it legally binding.

You can now download your signed copy of the contract for your records.

[View and Download Contract Button]

Thank you for completing the contract signing process!
```

---

## Technical Details

### Delivery Channels
All notifications use the **mail** channel and implement `ShouldQueue` for asynchronous delivery.

### Token-based Links
Invitation notifications use the `InvitationTokenService` to generate secure tokens for pre-filling registration forms with user details (name and email).

### Dynamic Recipients
The Contract Fully Executed notification adjusts its content and action URLs based on the recipient's role (coach vs. client).

### Variables Used
- `{AppName}` - Application name from config
- `{UserName}` - Recipient's name
- `{CoachName}` - Coach's name
- `{ClientName}` - Client's name
- `{AdminName}` - Administrator's name

---

*Last Updated: October 14, 2025*

