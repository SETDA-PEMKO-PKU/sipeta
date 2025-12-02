# Design Document

## Overview

This design implements a role-based access control system for OPD-specific administrators. The system extends the existing admin authentication to support admin OPD role, where each admin is assigned to a specific OPD and can only access and manage data within their assigned OPD. The design maintains backward compatibility with existing admin roles (super_admin, admin_organisasi, admin_bkpsdm) while adding new OPD-scoped access controls.

## Architecture

### High-Level Architecture

The system follows Laravel's MVC architecture with the following key components:

1. **Database Layer**: Migration to add `opd_id` column to `admins` table
2. **Model Layer**: Enhanced Admin model with OPD relationship and scope methods
3. **Middleware Layer**: New middleware for OPD-scoped authorization
4. **Controller Layer**: Modified controllers with automatic OPD scoping
5. **View Layer**: Conditional rendering based on admin role and OPD context

### Authentication Flow

```
User Login → AdminAuth Middleware → Check Role → Apply OPD Scope → Controller → View
```

For admin OPD:
- Login credentials validated
- Session created with OPD context
- All queries automatically scoped to assigned OPD
- Unauthorized access attempts blocked at middleware level

## Components and Interfaces

### 1. Database Schema Changes

**Migration: add_opd_id_to_admins_table**

```php
Schema::table('admins', function (Blueprint $table) {
    $table->foreignId('opd_id')->nullable()->constrained('opds')->onDelete('cascade');
});
```

- `opd_id`: Foreign key to `opds` table, nullable to support existing admin roles
- Cascade delete: When OPD is deleted, associated admin OPD accounts are also deleted

### 2. Admin Model Enhancement

**New Methods:**
- `opd()`: BelongsTo relationship to Opd model
- `isAdminOpd()`: Check if admin has admin_opd role
- `hasOpdAccess($opdId)`: Check if admin can access specific OPD
- `scopeForOpd($query, $opdId)`: Query scope for OPD filtering

**New Constant:**
- `ROLE_ADMIN_OPD = 'admin_opd'`

### 3. Middleware Components

**CheckOpdAccess Middleware**

Purpose: Validate that admin OPD only accesses their assigned OPD's resources

```php
public function handle(Request $request, Closure $next): Response
{
    $admin = auth('admin')->user();
    
    // Super admin and other roles bypass OPD check
    if (!$admin->isAdminOpd()) {
        return $next($request);
    }
    
    // Extract OPD ID from request (route parameter, query, or form data)
    $requestedOpdId = $this->extractOpdId($request);
    
    // If OPD ID present in request, validate access
    if ($requestedOpdId && !$admin->hasOpdAccess($requestedOpdId)) {
        abort(403, 'Anda tidak memiliki akses ke OPD ini');
    }
    
    return $next($request);
}
```

### 4. Controller Modifications

**Base Controller Trait: HasOpdScope**

```php
trait HasOpdScope
{
    protected function applyOpdScope($query)
    {
        $admin = auth('admin')->user();
        
        if ($admin->isAdminOpd()) {
            return $query->where('opd_id', $admin->opd_id);
        }
        
        return $query;
    }
    
    protected function getAccessibleOpdIds()
    {
        $admin = auth('admin')->user();
        
        if ($admin->isAdminOpd()) {
            return [$admin->opd_id];
        }
        
        return Opd::pluck('id')->toArray();
    }
}
```

**Modified Controllers:**
- PegawaiController: Apply OPD scope to ASN queries
- DashboardController: Filter statistics by OPD
- AnalyticsController: Scope analytics to assigned OPD

### 5. View Components

**Conditional Rendering:**
- Hide OPD selector for admin OPD (automatically set to their OPD)
- Show OPD name prominently in header for admin OPD
- Filter dropdown options based on accessible OPDs

## Data Models

### Admin Model

```php
class Admin extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'opd_id'  // NEW
    ];
    
    const ROLE_ADMIN_OPD = 'admin_opd';  // NEW
    
    // NEW: Relationship
    public function opd()
    {
        return $this->belongsTo(Opd::class);
    }
    
    // NEW: Role check
    public function isAdminOpd(): bool
    {
        return $this->role === self::ROLE_ADMIN_OPD;
    }
    
    // NEW: Access check
    public function hasOpdAccess($opdId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        
        if ($this->isAdminOpd()) {
            return $this->opd_id == $opdId;
        }
        
        // admin_organisasi and admin_bkpsdm have access to all OPDs
        return true;
    }
    
    // NEW: Query scope
    public function scopeForOpd($query, $opdId)
    {
        return $query->where('opd_id', $opdId);
    }
}
```

### Opd Model Enhancement

```php
class Opd extends Model
{
    // NEW: Relationship
    public function adminOpds()
    {
        return $this->hasMany(Admin::class)->where('role', Admin::ROLE_ADMIN_OPD);
    }
}
```


## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Admin OPD Management Properties

Property 1: Admin OPD creation requires all fields
*For any* admin OPD creation attempt, the operation should fail if any required field (name, email, password, opd_id) is missing, and succeed when all fields are present
**Validates: Requirements 1.1**

Property 2: OPD assignment persistence
*For any* admin OPD created with an OPD assignment, retrieving that admin from the database should return the same OPD relationship
**Validates: Requirements 1.2**

Property 3: Admin list includes OPD information
*For any* admin list view, all admin OPD entries should include their assigned OPD name in the rendered output
**Validates: Requirements 1.3**

Property 4: OPD reassignment persistence
*For any* admin OPD and any new OPD assignment, updating the admin's OPD should persist the new relationship in the database
**Validates: Requirements 1.4**

Property 5: Deactivated admin login prevention
*For any* admin OPD with is_active set to false, login attempts should fail with an appropriate error message
**Validates: Requirements 1.5**

### Authentication Properties

Property 6: Valid credentials authentication
*For any* admin OPD with valid credentials, authentication should succeed and create a session
**Validates: Requirements 2.1**

Property 7: Invalid credentials rejection
*For any* login attempt with invalid credentials, authentication should fail and return an error message
**Validates: Requirements 2.2**

Property 8: Session contains OPD context
*For any* successful admin OPD login, the created session should contain the admin's OPD ID
**Validates: Requirements 2.4**

Property 9: Logout clears session
*For any* logged-in admin OPD, performing logout should destroy the session and clear authentication state
**Validates: Requirements 2.5**

### Data Access Control Properties

Property 10: Dashboard data scoping
*For any* admin OPD accessing the dashboard, all displayed data (jabatan count, ASN count, statistics) should only include data from their assigned OPD
**Validates: Requirements 3.1, 7.2, 7.3, 7.4**

Property 11: Jabatan list scoping
*For any* admin OPD viewing the jabatan list, the returned results should only contain jabatan where opd_id matches the admin's assigned OPD
**Validates: Requirements 3.2**

Property 12: ASN list scoping
*For any* admin OPD viewing the ASN list, the returned results should only contain ASN where opd_id matches the admin's assigned OPD
**Validates: Requirements 3.3, 5.5**

Property 13: Cross-OPD access denial
*For any* admin OPD attempting to access a resource (jabatan, ASN, analytics) from a different OPD, the system should return a 403 Forbidden response
**Validates: Requirements 3.4**

Property 14: Analytics data scoping
*For any* admin OPD viewing analytics, all charts and statistics should only include data from their assigned OPD
**Validates: Requirements 3.5, 7.5**

### Resource Management Properties

Property 15: Automatic OPD association on creation
*For any* resource (jabatan or ASN) created by an admin OPD, the resource's opd_id should automatically be set to the admin's assigned OPD
**Validates: Requirements 4.1, 5.1**

Property 16: Edit authorization by OPD ownership
*For any* edit operation on a resource (jabatan or ASN), the operation should only succeed if the resource's opd_id matches the admin OPD's assigned OPD
**Validates: Requirements 4.2, 5.2**

Property 17: Delete authorization by OPD ownership
*For any* delete operation on a resource (jabatan or ASN), the operation should only succeed if the resource's opd_id matches the admin OPD's assigned OPD
**Validates: Requirements 4.3, 5.3**

Property 18: Jabatan hierarchy completeness
*For any* admin OPD viewing jabatan hierarchy, the tree should include all jabatan from their OPD and no jabatan from other OPDs
**Validates: Requirements 4.4**

Property 19: Cross-OPD modification rejection
*For any* admin OPD attempting to modify (create, update, delete) a resource from another OPD, the system should reject the operation with a 403 error
**Validates: Requirements 4.5**

Property 20: ASN-Jabatan assignment validation
*For any* ASN-jabatan assignment by an admin OPD, both the ASN and jabatan must have the same opd_id as the admin's assigned OPD
**Validates: Requirements 5.4**

### Authorization and Security Properties

Property 21: Permission check includes OPD validation
*For any* permission check for an admin OPD, the system should verify both the admin's role and that the requested resource belongs to their assigned OPD
**Validates: Requirements 6.1, 6.2**

Property 22: Automatic query scoping
*For any* database query executed in the context of an admin OPD, the query should automatically include a WHERE clause filtering by the admin's opd_id
**Validates: Requirements 6.3**

Property 23: View data filtering
*For any* view rendered for an admin OPD, all data collections passed to the view should be pre-filtered to only include the admin's OPD data
**Validates: Requirements 6.4**

Property 24: Unauthorized access logging
*For any* unauthorized access attempt by an admin OPD, the system should log the attempt (including admin ID, requested resource, timestamp) and return a 403 response
**Validates: Requirements 6.5**

### Backward Compatibility Properties

Property 25: Super admin unrestricted access
*For any* super admin, they should have access to all OPDs without any OPD-based filtering applied
**Validates: Requirements 8.1**

Property 26: Admin organisasi unrestricted access
*For any* admin organisasi, they should have access to all OPDs without any OPD-based filtering applied
**Validates: Requirements 8.2**

Property 27: Admin BKPSDM unrestricted ASN access
*For any* admin BKPSDM, they should have access to all ASN data across all OPDs without OPD-based filtering
**Validates: Requirements 8.3**

Property 28: Role-based permission differentiation
*For any* permission check, the system should apply OPD-based restrictions only to admin OPD role, not to other admin roles
**Validates: Requirements 8.4**

Property 29: Null OPD assignment compatibility
*For any* admin account with null opd_id, the system should function normally without requiring OPD assignment (for backward compatibility with existing accounts)
**Validates: Requirements 8.5**

## Error Handling

### Validation Errors

1. **Missing Required Fields**: Return 422 Unprocessable Entity with field-specific error messages
2. **Invalid OPD Assignment**: Return 422 with message "OPD tidak valid"
3. **Duplicate Email**: Return 422 with message "Email sudah digunakan"

### Authorization Errors

1. **Cross-OPD Access**: Return 403 Forbidden with message "Anda tidak memiliki akses ke OPD ini"
2. **Inactive Account**: Return 401 Unauthorized with message "Akun Anda tidak aktif"
3. **Invalid Credentials**: Return 401 with message "Email atau password salah"

### Resource Not Found

1. **Invalid Resource ID**: Return 404 Not Found with message "Data tidak ditemukan"
2. **Deleted OPD**: Cascade delete admin OPD accounts, log the action

### Error Logging

All authorization failures should be logged with:
- Admin ID and email
- Requested resource type and ID
- Timestamp
- IP address
- User agent

## Testing Strategy

### Unit Testing

The system will use PHPUnit for unit testing. Key test areas:

1. **Model Tests**:
   - Admin model methods (isAdminOpd, hasOpdAccess)
   - Relationship integrity (admin->opd)
   - Query scopes (forOpd)

2. **Middleware Tests**:
   - CheckOpdAccess middleware with various scenarios
   - AdminAuth middleware with admin OPD accounts

3. **Controller Tests**:
   - OPD scoping in queries
   - Authorization checks
   - Automatic OPD assignment on creation

### Property-Based Testing

The system will use PHPUnit with custom generators for property-based testing. We will implement generators for:

1. **Admin Generator**: Creates random admin accounts with various roles and OPD assignments
2. **OPD Generator**: Creates random OPD data
3. **Resource Generator**: Creates random jabatan and ASN data

Property tests will run a minimum of 100 iterations to ensure comprehensive coverage across different input combinations.

Each property-based test will be tagged with a comment explicitly referencing the correctness property from this design document using the format: `**Feature: opd-admin-management, Property {number}: {property_text}**`

### Integration Testing

1. **Authentication Flow**: Test complete login/logout cycle for admin OPD
2. **CRUD Operations**: Test create, read, update, delete with OPD scoping
3. **Cross-OPD Access**: Test that admin OPD cannot access other OPDs' data
4. **Backward Compatibility**: Test that existing admin roles continue to work

### Manual Testing Checklist

1. Create admin OPD account via super admin interface
2. Login as admin OPD and verify dashboard shows only assigned OPD
3. Attempt to access another OPD's data (should fail)
4. Create, edit, delete jabatan and ASN within assigned OPD
5. Verify existing admin roles (super_admin, admin_organisasi, admin_bkpsdm) still work
