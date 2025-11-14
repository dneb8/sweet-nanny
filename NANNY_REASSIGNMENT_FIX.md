# Nanny Reassignment Fix - Documentation

## Problem Description

When attempting to reassign a nanny from `SingleAppointmentCard`, a JavaScript error occurred:
```
app.ts:23 Uncaught (in promise) TypeError: pages[name] is not a function
```

### Root Cause
When `BookingAppointmentPolicy::chooseNanny()` returned `Response::deny()`, Laravel's `Gate::authorize()` threw an `AuthorizationException`. The default exception handler attempted to render an Inertia error page (`Errors/403`) that didn't exist, breaking the Inertia.js flow.

## Solution Implemented

### 1. Updated BookingAppointmentPolicy::chooseNanny()

**Previous Logic:**
- Only allowed choosing nanny in DRAFT status without a nanny assigned
- Blocked reassignment in PENDING status

**New Logic:**
- Allows choosing nanny when status is **DRAFT** (with or without nanny)
- Allows **reassigning nanny** when status is **PENDING** (key business rule)
- Denies for other statuses (confirmed, in_progress, completed, cancelled)

**Code Changes:**
```php
public function chooseNanny(User $user, BookingAppointment $appointment): Response
{
    // ... permission check ...
    
    // Allow in DRAFT or PENDING
    if (! in_array($appointment->status->value, [StatusEnum::DRAFT->value, StatusEnum::PENDING->value], true)) {
        return Response::deny('Solo se puede elegir niñera cuando la cita está en borrador o pendiente.');
    }
    
    // Removed: check that blocked reassignment when nanny_id was set
    
    // ... role-based authorization ...
}
```

### 2. Fixed Exception Handler (Laravel 11)

**File:** `bootstrap/app.php`

Added custom handling for `AuthorizationException` to prevent the Inertia error:

```php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (AuthorizationException $e, $request) {
        // For Inertia/AJAX requests, return JSON instead of HTML
        if ($request->expectsJson() || $request->header('X-Inertia')) {
            return response()->json([
                'message' => $e->getMessage() ?: 'No tienes permiso para realizar esta acción.',
            ], 403);
        }

        // For regular requests, redirect back with error
        return redirect()->back()
            ->with('error', $e->getMessage() ?: 'No tienes permiso para realizar esta acción.');
    });
})
```

### 3. Updated Tests

**File:** `tests/Feature/BookingAppointmentPolicyTest.php`

Updated and added tests to cover the new business rules:

- ✅ `test admin can choose nanny for any appointment in draft`
- ✅ `test admin can choose nanny for any appointment in pending`
- ✅ `test tutor can choose nanny for their own appointments in draft`
- ✅ `test tutor can reassign nanny for their own appointments in pending` (NEW)
- ✅ `test tutor cannot choose nanny for appointments not in draft or pending` (NEW)

## Business Rules Summary

### Appointment Status Flow & Nanny Assignment

```
DRAFT (no nanny)
   ↓ [assign nanny]
PENDING (with nanny)
   ↓ [nanny accepts]
CONFIRMED
   ↓ [automatic at start_date]
IN_PROGRESS
   ↓ [automatic at end_date]
COMPLETED
```

### When Can Nanny Be Chosen/Reassigned?

| Status        | Can Choose Nanny? | Can Reassign? | Notes                                    |
|---------------|-------------------|---------------|------------------------------------------|
| DRAFT         | ✅ Yes            | N/A           | Initial selection                        |
| PENDING       | ✅ Yes            | ✅ Yes        | **Reassignment allowed** (key fix)       |
| CONFIRMED     | ❌ No             | ❌ No         | Use `unassignNanny` instead              |
| IN_PROGRESS   | ❌ No             | ❌ No         | Service already started                  |
| COMPLETED     | ❌ No             | ❌ No         | Service finished                         |
| CANCELLED     | ❌ No             | ❌ No         | Appointment cancelled                    |

### Authorization

- **Admin:** Can always choose/reassign nanny (in DRAFT/PENDING)
- **Tutor (owner):** Can choose/reassign nanny for their own appointments (in DRAFT/PENDING)
- **Other roles:** Cannot choose/reassign nanny

## Impact

### Before Fix
- ❌ Reassigning nanny in PENDING status caused policy denial
- ❌ Policy denial triggered Inertia error page that didn't exist
- ❌ JavaScript error: "pages[name] is not a function"
- ❌ UI broken, user stuck

### After Fix
- ✅ Reassigning nanny in PENDING status works correctly
- ✅ Authorization errors return JSON for Inertia/AJAX requests
- ✅ No JavaScript errors
- ✅ Smooth user experience
- ✅ Proper error messages displayed

## Testing

To verify the fix:

```bash
# Run all policy tests
php artisan test --filter BookingAppointmentPolicyTest

# Specific reassignment test
php artisan test --filter "tutor can reassign nanny for their own appointments in pending"
```

## Frontend Integration

The `SingleAppointmentCard.vue` component can now safely call the nanny selection flow for PENDING appointments. The policy will:

1. Validate status is DRAFT or PENDING
2. Check user authorization (admin or owner tutor)
3. Allow the action to proceed
4. If denied, return proper JSON error instead of breaking Inertia

## Related Files

- `app/Policies/BookingAppointmentPolicy.php` - Policy logic
- `bootstrap/app.php` - Exception handling
- `tests/Feature/BookingAppointmentPolicyTest.php` - Test coverage
- `resources/js/Pages/Booking/components/SingleAppointmentCard.vue` - Frontend component

## Notes for Future Development

1. Consider adding a `reassignNanny` permission distinct from `chooseNanny` if business rules diverge
2. When reassigning in PENDING, consider sending notifications to the previous nanny
3. Track reassignment history if needed for auditing
4. Consider adding rate limiting for reassignments to prevent abuse
