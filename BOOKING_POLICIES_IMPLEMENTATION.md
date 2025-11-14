# Booking and BookingAppointment Policies Implementation

This document outlines the comprehensive policy system implemented for Booking and BookingAppointment management.

## Overview

This implementation follows the `UserPolicy` pattern with `hasPermission()` methods and establishes complete business rules for managing bookings and appointments throughout their lifecycle.

## Permission Enums

### BookingPermission
- `ViewAny`: View list of bookings (Admin, Tutor)
- `View`: View specific booking (Admin, Tutor - owner only)
- `Create`: Create new bookings (Admin, Tutor)
- `Update`: Edit bookings (Admin, Tutor - owner only, with restrictions)
- `Delete`: Delete bookings (Admin, Tutor - owner only, with restrictions)

### BookingAppointmentPermission
- `ViewAny`: View list of appointments (Admin, Nanny)
- `View`: View specific appointment (Admin, Nanny - assigned only, Tutor - owner only)
- `ChooseNanny`: Browse nannies for appointment (Admin, Tutor - draft only)
- `AssignNanny`: Assign nanny to appointment (Admin, Tutor - draft only)
- `UpdateDates`: Update start/end dates (Admin, Tutor - draft/pending only)
- `UpdateAddress`: Update address (Admin, Tutor - draft/pending only)
- `UpdateChildren`: Update children (Admin, Tutor - draft/pending only)
- `Accept`: Accept pending appointment (Admin, Nanny - assigned only)
- `Reject`: Reject pending appointment (Admin, Nanny - assigned only)
- `UnassignNanny`: Remove nanny from confirmed appointment (Admin, Tutor, Nanny)
- `Cancel`: Cancel appointment (Admin, Tutor, Nanny - not if completed)
- `ReviewTutor`: Nanny reviews tutor (Admin, Nanny - completed only, once)
- `ReviewNanny`: Tutor reviews nanny (Admin, Tutor - completed only, once)

## Business Rules

### Booking Management

#### Edit Restrictions
A booking **cannot be edited** if:
- Any of its appointments has a nanny assigned (`nanny_id IS NOT NULL`)

#### Delete Restrictions
A booking **cannot be deleted** if:
- Any of its appointments has a nanny assigned (`nanny_id IS NOT NULL`)

### BookingAppointment State Flow

```
draft → (assign nanny) → pending → (accept) → confirmed → (start time) → in_progress → (end time) → completed
   ↓                                  ↓            ↓
(update fields)                   (reject)    (unassign)
                                      ↓            ↓
                                    draft       pending
```

#### State-Specific Rules

**Draft Status:**
- Can choose/assign nanny
- Can update all fields (dates, address, children)
- No nanny assigned yet

**Pending Status:**
- Nanny is assigned and awaiting response
- Tutor can update dates/address/children (will unassign nanny → back to draft)
- Nanny can accept (→ confirmed) or reject (→ draft, unassign)

**Confirmed Status:**
- Nanny has accepted
- Can be unassigned by tutor, nanny, or admin (→ pending)
- Cannot edit dates, address, or children

**In Progress Status:**
- Automatically set by Observer when start_date is reached
- Service is currently being performed

**Completed Status:**
- Automatically set by Observer when end_date is reached
- Can leave reviews (one per side per appointment)
- Cannot cancel
- Cannot modify

## Review System

### Review Tracking Fields
- `reviewed_by_tutor` (boolean): Tracks if tutor has reviewed nanny for this appointment
- `reviewed_by_nanny` (boolean): Tracks if nanny has reviewed tutor for this appointment

### Review Rules
1. Reviews can only be left after appointment is `completed`
2. Each side can leave only ONE review per appointment
3. Nanny reviews tutor (if `!reviewed_by_nanny`)
4. Tutor reviews nanny (if `!reviewed_by_tutor`)
5. Admin can create reviews on behalf of either party

## UI Implications

### BookingTable Actions Column

The Edit and Delete buttons should be conditionally shown based on:

```javascript
// Edit button - check if booking can be edited
canEdit = booking.bookingAppointments.every(apt => apt.nanny_id === null)

// Delete button - check if booking can be deleted  
canDelete = booking.bookingAppointments.every(apt => apt.nanny_id === null)
```

### BookingAppointmentTable Actions Column

Actions depend on appointment status and user role:

**Draft Status (no nanny):**
- Tutor/Admin: "Choose Nanny" button

**Draft Status (nanny assigned - shouldn't happen but handle gracefully):**
- No actions (invalid state)

**Pending Status:**
- Nanny (assigned): "Accept" and "Reject" buttons
- Tutor (owner): Can still edit dates/address/children (will unassign)

**Confirmed Status:**
- Tutor/Nanny/Admin: "Unassign Nanny" button

**In Progress Status:**
- No actions (service ongoing)

**Completed Status:**
- Nanny: "Review Tutor" button (if `!reviewed_by_nanny`)
- Tutor: "Review Nanny" button (if `!reviewed_by_tutor`)

## Testing Checklist

### BookingPolicy Tests
- [ ] Admin can view any booking
- [ ] Tutor can view own bookings only
- [ ] Nanny cannot view bookings
- [ ] Admin can create bookings
- [ ] Tutor can create bookings
- [ ] Cannot edit booking with nanny-assigned appointments
- [ ] Can edit booking with all draft appointments
- [ ] Cannot delete booking with nanny-assigned appointments
- [ ] Can delete booking with all draft appointments

### BookingAppointmentPolicy Tests
- [ ] Admin can view any appointment
- [ ] Nanny can view only assigned appointments
- [ ] Tutor can view own appointments
- [ ] Can choose nanny only in draft status
- [ ] Cannot choose nanny if already assigned
- [ ] Can assign nanny only in draft status
- [ ] Tutor can update dates in draft/pending
- [ ] Updating dates in pending unassigns nanny
- [ ] Nanny can accept pending appointment
- [ ] Nanny can reject pending appointment
- [ ] Can unassign nanny from confirmed appointment
- [ ] Cannot cancel completed appointment
- [ ] Can review only once per side per appointment
- [ ] Can review only completed appointments

## Implementation Status

✅ **Completed:**
- Permission enums created
- Policies implemented with comprehensive business rules
- Database migration for review tracking
- Model updates

🚧 **Pending:**
- Controller updates to use all policy methods
- UI updates to conditionally show/hide actions
- Comprehensive test suite
- Observer for automatic state transitions (in_progress, completed)
- Review creation endpoint to mark appointments as reviewed

