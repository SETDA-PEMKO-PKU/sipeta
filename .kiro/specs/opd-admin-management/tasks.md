# Implementation Plan

- [x] 1. Database migration for OPD assignment
  - Create migration to add `opd_id` column to `admins` table
  - Add foreign key constraint with cascade delete
  - _Requirements: 1.2_

- [x] 2. Enhance Admin model with OPD support
  - Add `opd_id` to fillable array
  - Add `ROLE_ADMIN_OPD` constant
  - Implement `opd()` relationship method
  - Implement `isAdminOpd()` method
  - Implement `hasOpdAccess($opdId)` method
  - Implement `scopeForOpd($query, $opdId)` query scope
  - _Requirements: 1.1, 1.2, 6.1, 8.4_

- [x] 2.1 Write property test for Admin model OPD access validation
  - **Property 21: Permission check includes OPD validation**
  - **Validates: Requirements 6.1, 6.2**

- [ ]* 2.2 Write property test for backward compatibility
  - **Property 29: Null OPD assignment compatibility**
  - **Validates: Requirements 8.5**

- [x] 3. Enhance Opd model with admin relationship
  - Add `adminOpds()` relationship method
  - _Requirements: 1.2_

- [x] 4. Create CheckOpdAccess middleware
  - Implement middleware to validate OPD access for admin OPD
  - Extract OPD ID from request (route params, query, form data)
  - Validate admin has access to requested OPD
  - Return 403 for unauthorized access
  - Log unauthorized access attempts
  - _Requirements: 3.4, 6.2, 6.5_

- [ ]* 4.1 Write property test for cross-OPD access denial
  - **Property 13: Cross-OPD access denial**
  - **Validates: Requirements 3.4**

- [ ]* 4.2 Write property test for unauthorized access logging
  - **Property 24: Unauthorized access logging**
  - **Validates: Requirements 6.5**

- [x] 5. Create HasOpdScope trait for controllers
  - Implement `applyOpdScope($query)` method
  - Implement `getAccessibleOpdIds()` method
  - Implement `validateOpdAccess($opdId)` method
  - _Requirements: 6.3, 6.4_

- [ ]* 5.1 Write property test for automatic query scoping
  - **Property 22: Automatic query scoping**
  - **Validates: Requirements 6.3**

- [x] 6. Update AdminController for admin OPD management
  - Add OPD selection dropdown in create form
  - Add validation for opd_id when role is admin_opd
  - Update store method to save opd_id
  - Update edit form to show and allow changing OPD assignment
  - Update update method to handle opd_id changes
  - Display assigned OPD in admin list view
  - _Requirements: 1.1, 1.2, 1.3, 1.4_

- [ ]* 6.1 Write property test for admin OPD creation validation
  - **Property 1: Admin OPD creation requires all fields**
  - **Validates: Requirements 1.1**

- [ ]* 6.2 Write property test for OPD assignment persistence
  - **Property 2: OPD assignment persistence**
  - **Validates: Requirements 1.2**

- [ ]* 6.3 Write property test for OPD reassignment
  - **Property 4: OPD reassignment persistence**
  - **Validates: Requirements 1.4**

- [x] 7. Update AuthController for admin OPD authentication
  - Ensure session includes OPD context for admin OPD
  - Update login validation to check is_active status
  - Add OPD information to session data
  - _Requirements: 2.1, 2.3, 2.4_

- [ ]* 7.1 Write property test for authentication with OPD context
  - **Property 8: Session contains OPD context**
  - **Validates: Requirements 2.4**

- [ ]* 7.2 Write property test for deactivated admin login prevention
  - **Property 5: Deactivated admin login prevention**
  - **Validates: Requirements 1.5**

- [x] 8. Update DashboardController with OPD scoping
  - Use HasOpdScope trait
  - Apply OPD scope to all statistics queries
  - Display OPD name prominently for admin OPD
  - Filter jabatan count by OPD
  - Filter ASN count by OPD
  - Filter fill rate calculation by OPD
  - _Requirements: 3.1, 7.1, 7.2, 7.3, 7.4_

- [ ]* 8.1 Write property test for dashboard data scoping
  - **Property 10: Dashboard data scoping**
  - **Validates: Requirements 3.1, 7.2, 7.3, 7.4**

- [x] 9. Update PegawaiController with OPD scoping
  - Use HasOpdScope trait
  - Apply OPD scope to index query
  - Auto-set opd_id in create method for admin OPD
  - Validate OPD access in edit method
  - Validate OPD access in update method
  - Validate OPD access in destroy method
  - Filter OPD dropdown for admin OPD (show only their OPD)
  - Validate ASN-jabatan assignment is within same OPD
  - _Requirements: 3.3, 5.1, 5.2, 5.3, 5.4, 5.5_

- [ ]* 9.1 Write property test for ASN list scoping
  - **Property 12: ASN list scoping**
  - **Validates: Requirements 3.3, 5.5**

- [ ]* 9.2 Write property test for automatic OPD association
  - **Property 15: Automatic OPD association on creation**
  - **Validates: Requirements 4.1, 5.1**

- [ ]* 9.3 Write property test for edit authorization
  - **Property 16: Edit authorization by OPD ownership**
  - **Validates: Requirements 4.2, 5.2**

- [ ]* 9.4 Write property test for delete authorization
  - **Property 17: Delete authorization by OPD ownership**
  - **Validates: Requirements 4.3, 5.3**

- [ ]* 9.5 Write property test for ASN-jabatan assignment validation
  - **Property 20: ASN-Jabatan assignment validation**
  - **Validates: Requirements 5.4**

- [x] 10. Create OpdJabatanController for jabatan management
  - Create new controller for jabatan CRUD operations
  - Use HasOpdScope trait
  - Apply OPD scope to all queries
  - Auto-set opd_id in create method for admin OPD
  - Validate OPD access in edit, update, destroy methods
  - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5_

- [ ]* 10.1 Write property test for jabatan list scoping
  - **Property 11: Jabatan list scoping**
  - **Validates: Requirements 3.2**

- [ ]* 10.2 Write property test for jabatan hierarchy completeness
  - **Property 18: Jabatan hierarchy completeness**
  - **Validates: Requirements 4.4**

- [ ]* 10.3 Write property test for cross-OPD modification rejection
  - **Property 19: Cross-OPD modification rejection**
  - **Validates: Requirements 4.5**

- [x] 11. Update AnalyticsController with OPD scoping
  - Use HasOpdScope trait
  - Apply OPD scope to all analytics queries
  - Filter gap analysis by OPD
  - Filter jabatan analytics by OPD
  - Filter kepegawaian analytics by OPD
  - Filter OPD overview to show only assigned OPD for admin OPD
  - _Requirements: 3.5, 7.5_

- [ ]* 11.1 Write property test for analytics data scoping
  - **Property 14: Analytics data scoping**
  - **Validates: Requirements 3.5, 7.5**

- [x] 12. Update views for admin OPD context
  - Update admin dashboard to show OPD name for admin OPD
  - Hide OPD selector for admin OPD in forms
  - Update admin list to display assigned OPD
  - Add OPD badge/indicator in navigation for admin OPD
  - Update breadcrumbs to include OPD context
  - _Requirements: 1.3, 7.1_

- [ ]* 12.1 Write property test for admin list OPD display
  - **Property 3: Admin list includes OPD information**
  - **Validates: Requirements 1.3**

- [ ]* 12.2 Write property test for view data filtering
  - **Property 23: View data filtering**
  - **Validates: Requirements 6.4**

- [x] 13. Register middleware in Kernel
  - Add CheckOpdAccess middleware to $routeMiddleware
  - Apply middleware to relevant admin routes
  - _Requirements: 6.2_

- [x] 14. Update routes with OPD middleware
  - Apply check.opd.access middleware to pegawai routes
  - Apply check.opd.access middleware to jabatan routes
  - Apply check.opd.access middleware to analytics routes
  - _Requirements: 3.4, 6.2_

- [x] 15. Create database seeder for admin OPD
  - Create sample admin OPD accounts for testing
  - Assign to different OPDs
  - _Requirements: 1.1, 1.2_

- [ ]* 16. Write property tests for backward compatibility
  - **Property 25: Super admin unrestricted access**
  - **Property 26: Admin organisasi unrestricted access**
  - **Property 27: Admin BKPSDM unrestricted ASN access**
  - **Property 28: Role-based permission differentiation**
  - **Validates: Requirements 8.1, 8.2, 8.3, 8.4**

- [ ] 17. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.
