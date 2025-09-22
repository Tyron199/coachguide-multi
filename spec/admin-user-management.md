# **Admin/Coach Management Implementation Plan**

## **1. Controller Structure**

### **Recommendation: Two Separate Controllers**
Based on your existing pattern and the different responsibilities, I recommend **two separate controllers**:

#### **A. CoachController** (`app/Http/Controllers/Tenant/Admin/CoachController.php`)
- Manages coach users specifically
- Handles coach invitations, listing, editing, archiving
- Follows the same pattern as your `ClientController`

#### **B. AdminController** (`app/Http/Controllers/Tenant/Admin/AdminController.php`)  
- Manages admin users specifically
- Handles admin invitations, listing, editing, archiving
- Similar structure but for admin role

### **Why Two Controllers?**
1. **Separation of concerns** - Different user types, different business logic
2. **Follows your existing pattern** - You have separate controllers for different entities
3. **Easier permissions** - Different authorization rules for managing coaches vs admins
4. **Future extensibility** - Coach-specific features vs admin-specific features
5. **Route organization** - Cleaner URL structure

---

## **2. File Structure Plan**

```
app/Http/Controllers/Tenant/Admin/
├── CoachController.php          # NEW - Manage coaches
├── AdminController.php          # NEW - Manage admins
├── ThemeController.php          # EXISTS
├── LogoController.php           # EXISTS
└── SubscriptionController.php   # EXISTS

app/Notifications/Tenant/
├── SendCoachInvitation.php      # NEW - Coach invitation email
├── SendAdminInvitation.php      # NEW - Admin invitation email
├── SendClientInvitation.php     # EXISTS
└── [other existing notifications]

resources/js/pages/Tenant/admin/
├── coaches/                     # NEW - Coach management pages
│   ├── ListCoaches.vue         # List all coaches
│   ├── CreateCoach.vue         # Add new coach
│   ├── EditCoach.vue           # Edit coach details
│   └── ShowCoach.vue           # View coach details
├── administrators/              # NEW - Admin management pages  
│   ├── ListAdmins.vue          # List all admins
│   ├── CreateAdmin.vue         # Add new admin
│   ├── EditAdmin.vue           # Edit admin details
│   └── ShowAdmin.vue           # View admin details
├── platform-settings/          # EXISTS
└── subscription/                # EXISTS
```

---

## **3. Controller Methods Plan**

### **CoachController Methods:**
```php
class CoachController extends Controller
{
    public function index(Request $request)          // List coaches with search/filter
    public function archived(Request $request)      // List archived coaches
    public function create()                        // Show create coach form
    public function store(Request $request)         // Create new coach + send invitation
    public function show(User $coach)              // View coach details
    public function edit(User $coach)              // Show edit coach form
    public function update(Request $request, User $coach)  // Update coach
    public function archive(User $coach)           // Archive coach
    public function unarchive(User $coach)         // Unarchive coach
    public function destroy(User $coach)           // Soft delete coach
    public function sendInvitation(User $coach)    // Resend invitation
    
    // Batch operations (like your ClientController)
    public function archiveBatch(Request $request)
    public function unarchiveBatch(Request $request)
    public function destroyBatch(Request $request)
}
```

### **AdminController Methods:**
```php
class AdminController extends Controller
{
    // Same methods as CoachController but for admin role
    public function index(Request $request)
    public function archived(Request $request)
    public function create()
    public function store(Request $request)
    public function show(User $admin)
    public function edit(User $admin)
    public function update(Request $request, User $admin)
    public function archive(User $admin)
    public function unarchive(User $admin)
    public function destroy(User $admin)
    public function sendInvitation(User $admin)
    
    // Batch operations
    public function archiveBatch(Request $request)
    public function unarchiveBatch(Request $request)
    public function destroyBatch(Request $request)
}
```

---

## **4. Route Structure Plan**

### **Add to `routes/tenant/admin.php`:**
```php
Route::middleware(['auth', 'verified', 'two-factor', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Coach Management Routes
    Route::prefix('coaches')->name('coaches.')->group(function () {
        Route::get('/', [CoachController::class, 'index'])->name('index');
        Route::get('/archived', [CoachController::class, 'archived'])->name('archived');
        Route::get('/create', [CoachController::class, 'create'])->name('create');
        Route::post('/', [CoachController::class, 'store'])->name('store');
        Route::get('/{coach}', [CoachController::class, 'show'])->name('show');
        Route::get('/{coach}/edit', [CoachController::class, 'edit'])->name('edit');
        Route::put('/{coach}', [CoachController::class, 'update'])->name('update');
        Route::patch('/{coach}/archive', [CoachController::class, 'archive'])->name('archive');
        Route::patch('/{coach}/unarchive', [CoachController::class, 'unarchive'])->name('unarchive');
        Route::delete('/{coach}', [CoachController::class, 'destroy'])->name('destroy');
        Route::post('/{coach}/invite', [CoachController::class, 'sendInvitation'])->name('invite');
        
        // Batch operations
        Route::post('/batch/archive', [CoachController::class, 'archiveBatch'])->name('batch.archive');
        Route::post('/batch/unarchive', [CoachController::class, 'unarchiveBatch'])->name('batch.unarchive');
        Route::delete('/batch/destroy', [CoachController::class, 'destroyBatch'])->name('batch.destroy');
    });
    
    // Admin Management Routes  
    Route::prefix('administrators')->name('administrators.')->group(function () {
        // Same structure as coaches but for admins
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/archived', [AdminController::class, 'archived'])->name('archived');
        Route::get('/create', [AdminController::class, 'create'])->name('create');
        Route::post('/', [AdminController::class, 'store'])->name('store');
        Route::get('/{admin}', [AdminController::class, 'show'])->name('show');
        Route::get('/{admin}/edit', [AdminController::class, 'edit'])->name('edit');
        Route::put('/{admin}', [AdminController::class, 'update'])->name('update');
        Route::patch('/{admin}/archive', [AdminController::class, 'archive'])->name('archive');
        Route::patch('/{admin}/unarchive', [AdminController::class, 'unarchive'])->name('unarchive');
        Route::delete('/{admin}', [AdminController::class, 'destroy'])->name('destroy');
        Route::post('/{admin}/invite', [AdminController::class, 'sendInvitation'])->name('invite');
        
        // Batch operations
        Route::post('/batch/archive', [AdminController::class, 'archiveBatch'])->name('batch.archive');
        Route::post('/batch/unarchive', [AdminController::class, 'unarchiveBatch'])->name('batch.unarchive');
        Route::delete('/batch/destroy', [AdminController::class, 'destroyBatch'])->name('batch.destroy');
    });

    // Existing routes...
    Route::get('platform-settings/theme', [ThemeController::class, 'index'])->name('platform-settings.theme');
    // ... etc
});
```

---

## **5. Notification Classes Plan**

### **SendCoachInvitation.php**
```php
class SendCoachInvitation extends Notification implements ShouldQueue
{
    // Similar to SendClientInvitation but:
    // - Different email content for coaches
    // - Different greeting/messaging
    // - Same token-based registration system
    // - Explain coach role and platform features
}
```

### **SendAdminInvitation.php**
```php
class SendAdminInvitation extends Notification implements ShouldQueue
{
    // Similar structure but:
    // - Admin-specific email content
    // - Explain admin privileges and responsibilities
    // - Same token-based registration system
}
```

---

## **6. Policy Considerations**

### **New Policy Needed: `UserPolicy.php`**
```php
class UserPolicy
{
    // For managing coaches and admins
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin'); // Only admins can manage users
    }
    
    public function create(User $user): bool
    {
        return $user->hasRole('admin'); // Only admins can create coaches/admins
    }
    
    public function update(User $user, User $targetUser): bool
    {
        // Admins can edit coaches and other admins
        // Prevent self-deletion of admin role
        return $user->hasRole('admin');
    }
    
    public function delete(User $user, User $targetUser): bool
    {
        // Prevent admins from deleting themselves
        return $user->hasRole('admin') && $user->id !== $targetUser->id;
    }
}
```

---

## **7. Frontend Pages Plan**

### **Coach Management Pages:**
1. **ListCoaches.vue** - Table with search, filter, sorting (like your ListClients.vue)
2. **CreateCoach.vue** - Form to add new coach with invitation option
3. **EditCoach.vue** - Edit coach details, archive/unarchive
4. **ShowCoach.vue** - View coach profile and assigned clients

### **Admin Management Pages:**
1. **ListAdmins.vue** - Table of all administrators
2. **CreateAdmin.vue** - Form to add new admin with invitation
3. **EditAdmin.vue** - Edit admin details
4. **ShowAdmin.vue** - View admin profile

### **Navigation Updates:**
- Add "User Management" section to admin navigation
- Sub-items: "Coaches", "Administrators"

---

## **8. Database Considerations**

### **No New Tables Needed!**
- Use existing `users` table with role assignments
- Coach role: `UserRole::COACH`
- Admin role: `UserRole::ADMIN`
- Same `assigned_coach_id`, `archived`, `status` fields work

---

## **9. Key Differences from Client Management**

### **Coaches:**
- No company assignment needed
- No assigned coach (they ARE coaches)
- Different invitation email content
- Can view their assigned clients count
- Show coaching statistics

### **Admins:**
- No company assignment
- No coach assignment
- Admin-specific invitation content
- Show platform usage statistics
- Prevent self-deletion

---

## **10. Implementation Order**

1. **Phase 1:** Create notification classes
2. **Phase 2:** Create CoachController (easier, similar to clients)
3. **Phase 3:** Create AdminController (similar pattern)
4. **Phase 4:** Create policies
5. **Phase 5:** Add routes
6. **Phase 6:** Create frontend pages (start with coaches)
7. **Phase 7:** Add navigation links
8. **Phase 8:** Testing and refinement

---

This plan leverages your existing patterns while maintaining clean separation of concerns. The structure mirrors your successful client management system but adapts it for the different needs of coach and admin management.

Would you like me to start implementing any specific part of this plan?