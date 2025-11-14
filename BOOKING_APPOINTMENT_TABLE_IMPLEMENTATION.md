# BookingAppointment DataTable Implementation

## Overview

This document describes the implementation of the BookingAppointment DataTable feature, which allows Admins and Nannies to view and manage booking appointments.

## Features

### Authorization & Permissions

- **Admin**: Can view all booking appointments
- **Nanny**: Can view only appointments assigned to them (filtered at query level)
- **Tutor**: Cannot access the appointments list (they see appointments through their bookings)

### Table Columns

1. **Tutor**: Avatar + name with click to view user profile
2. **Niños (Children)**: Badge showing count of children for the appointment
3. **Servicio (Service)**: Link to the booking detail page
4. **Dirección (Address)**: Shows the neighborhood/zone
5. **Status**: Color-coded badge indicating appointment status
6. **Start Date**: Formatted date and time
7. **End Date**: Formatted date and time
8. **Niñera (Nanny)**: Avatar + name with click to view user profile (if assigned)

### Filtering

- **Status Filter**: Filter appointments by their status (draft, pending, confirmed, completed, cancelled)
- **Search**: Search by tutor name/surnames or nanny name/surnames

### Responsive Design

- Desktop: Full table view with all columns
- Mobile: Card view showing all relevant information in a compact format

## Technical Implementation

### Backend

#### Policy (app/Policies/BookingAppointmentPolicy.php)

Added two new methods:
- `viewAny(User $user)`: Checks if user can access the appointments list
- `view(User $user, BookingAppointment $appointment)`: Checks if user can view a specific appointment

#### Service (app/Services/BookingAppointmentService.php)

- `indexFetch()`: Fetches appointments with:
  - Eager loading of relationships to prevent N+1 queries
  - Permission-based filtering (Nannies only see their appointments)
  - Search functionality for tutor and nanny names
  - Status filtering
  - Pagination (12 per page)

#### Controller (app/Http/Controllers/BookingAppointmentController.php)

- `index()`: Returns Inertia response with paginated appointments

#### Route (routes/bookings.php)

- `GET /booking-appointments` → `booking-appointments.index`

### Frontend

#### Components

1. **BookingAppointmentTable.vue**: Main table component with all columns
2. **BookingAppointmentFiltros.vue**: Filter controls for status
3. **BookingAppointmentCard.vue**: Responsive card view for mobile
4. **Index.vue**: Page component that renders the table

#### Service (resources/js/services/bookingAppointmentTableService.ts)

Provides:
- Reactive filters state
- Column visibility management
- Navigation helpers
- Status color and label helpers

#### Sidebar Integration (resources/js/components/AppSidebar.vue)

Added "Citas" menu entry visible for Admin and Nanny roles

## Tests

Comprehensive test coverage for all policy methods:

1. Admin permissions
2. Nanny permissions (both for viewAny and view)
3. Tutor permissions
4. Edge cases (unassigned appointments, other users' appointments)

Total: 12 passing tests

## Query Optimization

All queries use eager loading to prevent N+1 problems:

```php
->with([
    'booking.tutor.user',
    'nanny.user',
    'children',
    'addresses',
])
```

## Status Badge Colors

The status badges follow the same color scheme as the BookingService:

- **Draft/Pending**: Amber
- **En Espera**: Yellow
- **Confirmed/Approved**: Emerald
- **In Progress**: Sky blue
- **Completed/Finished**: Slate gray
- **Cancelled**: Rose red

## Security

- ✅ All routes protected with authentication and authorization
- ✅ No SQL injection vulnerabilities
- ✅ Proper null safety in policies
- ✅ Validated by CodeQL scanner

## Usage

### As Admin
1. Navigate to "Citas" in the sidebar
2. View all booking appointments across all tutors and nannies
3. Filter by status or search by names
4. Click on tutor/nanny to view their profile
5. Click on service # to view booking details

### As Nanny
1. Navigate to "Citas" in the sidebar
2. View only appointments assigned to you
3. Filter and search as needed
4. Same navigation capabilities as admin
