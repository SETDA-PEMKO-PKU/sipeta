# Requirements Document

## Introduction

Sistem ini akan menambahkan fitur admin OPD dimana setiap OPD dapat memiliki admin khusus yang hanya dapat login dan mengelola data OPD mereka sendiri. Admin OPD memiliki akses terbatas hanya untuk OPD yang ditugaskan kepada mereka, berbeda dengan super admin atau admin organisasi yang memiliki akses ke semua OPD.

## Glossary

- **Admin OPD**: Administrator yang ditugaskan untuk mengelola satu OPD tertentu
- **OPD (Organisasi Perangkat Daerah)**: Unit organisasi pemerintah daerah
- **System**: Aplikasi manajemen kepegawaian berbasis Laravel
- **Super Admin**: Administrator dengan akses penuh ke seluruh sistem
- **Admin Organisasi**: Administrator yang dapat mengelola OPD dan Jabatan
- **Admin BKPSDM**: Administrator yang dapat mengelola ASN
- **ASN (Aparatur Sipil Negara)**: Pegawai negeri sipil
- **Jabatan**: Posisi atau jabatan dalam struktur organisasi OPD

## Requirements

### Requirement 1

**User Story:** As a super admin, I want to create admin OPD accounts and assign them to specific OPDs, so that each OPD can have dedicated administrators.

#### Acceptance Criteria

1. WHEN a super admin creates a new admin OPD account THEN the System SHALL require name, email, password, and OPD assignment
2. WHEN a super admin assigns an OPD to an admin OPD THEN the System SHALL store the relationship between the admin and the OPD
3. WHEN a super admin views the admin list THEN the System SHALL display all admins with their assigned OPDs
4. WHEN a super admin edits an admin OPD account THEN the System SHALL allow changing the assigned OPD
5. WHEN a super admin deactivates an admin OPD account THEN the System SHALL prevent that admin from logging in

### Requirement 2

**User Story:** As an admin OPD, I want to login to the system with my credentials, so that I can access my assigned OPD's management interface.

#### Acceptance Criteria

1. WHEN an admin OPD enters valid credentials THEN the System SHALL authenticate the user and redirect to their OPD dashboard
2. WHEN an admin OPD enters invalid credentials THEN the System SHALL reject the login and display an error message
3. WHEN an inactive admin OPD attempts to login THEN the System SHALL reject the login and display an appropriate message
4. WHEN an admin OPD logs in successfully THEN the System SHALL create a session with their OPD context
5. WHEN an admin OPD logs out THEN the System SHALL destroy the session and redirect to the login page

### Requirement 3

**User Story:** As an admin OPD, I want to view and manage only my assigned OPD's data, so that I can perform my administrative duties without accessing other OPDs.

#### Acceptance Criteria

1. WHEN an admin OPD accesses the dashboard THEN the System SHALL display only data from their assigned OPD
2. WHEN an admin OPD views the jabatan list THEN the System SHALL show only jabatan from their assigned OPD
3. WHEN an admin OPD views the ASN list THEN the System SHALL show only ASN from their assigned OPD
4. WHEN an admin OPD attempts to access another OPD's data THEN the System SHALL deny access and return an authorization error
5. WHEN an admin OPD views analytics THEN the System SHALL display analytics only for their assigned OPD

### Requirement 4

**User Story:** As an admin OPD, I want to manage jabatan within my OPD, so that I can maintain the organizational structure.

#### Acceptance Criteria

1. WHEN an admin OPD creates a new jabatan THEN the System SHALL automatically associate it with their assigned OPD
2. WHEN an admin OPD edits a jabatan THEN the System SHALL only allow editing jabatan from their assigned OPD
3. WHEN an admin OPD deletes a jabatan THEN the System SHALL only allow deleting jabatan from their assigned OPD
4. WHEN an admin OPD views jabatan hierarchy THEN the System SHALL display the complete tree structure for their OPD
5. WHEN an admin OPD attempts to modify jabatan from another OPD THEN the System SHALL reject the operation

### Requirement 5

**User Story:** As an admin OPD, I want to manage ASN within my OPD, so that I can maintain employee records.

#### Acceptance Criteria

1. WHEN an admin OPD creates a new ASN THEN the System SHALL automatically associate it with their assigned OPD
2. WHEN an admin OPD edits an ASN THEN the System SHALL only allow editing ASN from their assigned OPD
3. WHEN an admin OPD deletes an ASN THEN the System SHALL only allow deleting ASN from their assigned OPD
4. WHEN an admin OPD assigns an ASN to a jabatan THEN the System SHALL only allow assignment to jabatan within their OPD
5. WHEN an admin OPD views ASN list THEN the System SHALL display only ASN from their assigned OPD

### Requirement 6

**User Story:** As a system architect, I want clear role-based access control for admin OPD, so that security boundaries are enforced at the application level.

#### Acceptance Criteria

1. WHEN the System checks admin permissions THEN it SHALL verify the admin's role and OPD assignment
2. WHEN an admin OPD makes a request THEN the System SHALL validate that the requested resource belongs to their assigned OPD
3. WHEN the System applies data filters THEN it SHALL automatically scope queries to the admin's assigned OPD
4. WHEN the System renders views THEN it SHALL only include data from the admin's assigned OPD
5. WHEN an unauthorized access attempt occurs THEN the System SHALL log the attempt and return a 403 Forbidden response

### Requirement 7

**User Story:** As an admin OPD, I want to see a personalized dashboard showing my OPD's statistics, so that I can monitor key metrics.

#### Acceptance Criteria

1. WHEN an admin OPD accesses the dashboard THEN the System SHALL display their OPD name prominently
2. WHEN the dashboard loads THEN the System SHALL show total jabatan count for the assigned OPD
3. WHEN the dashboard loads THEN the System SHALL show total ASN count for the assigned OPD
4. WHEN the dashboard loads THEN the System SHALL show jabatan fill rate statistics for the assigned OPD
5. WHEN the dashboard displays charts THEN the System SHALL include only data from the assigned OPD

### Requirement 8

**User Story:** As a super admin, I want to maintain backward compatibility with existing admin roles, so that current functionality remains intact.

#### Acceptance Criteria

1. WHEN a super admin logs in THEN the System SHALL grant access to all OPDs as before
2. WHEN an admin organisasi logs in THEN the System SHALL grant access to all OPDs as before
3. WHEN an admin BKPSDM logs in THEN the System SHALL grant access to all ASN data as before
4. WHEN the System determines permissions THEN it SHALL differentiate between admin OPD and other admin roles
5. WHEN existing admin accounts are used THEN the System SHALL function without requiring OPD assignment
