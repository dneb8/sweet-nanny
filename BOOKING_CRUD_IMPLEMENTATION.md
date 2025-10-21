# Booking CRUD Implementation with Polymorphic Addresses

## Overview
This implementation enhances the booking system with:
- **Polymorphic Addresses**: Addresses can now belong to Tutors, Nannies, or Bookings
- **Enhanced Booking Requirements**: Tutors can specify desired qualities, academic degree, and specialized courses
- **Improved UX**: 4-step wizard with better address management

## Database Changes

### New Migrations
1. `2025_10_18_000705_make_addresses_polymorphic.php`
   - Adds `addressable_type` and `addressable_id` columns to addresses table
   - Migrates existing data to polymorphic structure
   - Creates indexes for performance

2. `2025_10_18_000826_add_qualities_degree_courses_to_bookings.php`
   - Adds `qualities` (JSON array)
   - Adds `degree` (string)
   - Adds `courses` (JSON array)

### Model Changes

#### Address Model
```php
// New polymorphic relation
public function addressable()
{
    return $this->morphTo();
}
```

#### Booking Model
```php
// Changed to morphOne
public function address()
{
    return $this->morphOne(Address::class, 'addressable');
}

// New fields in $fillable and $casts
protected $fillable = [..., 'qualities', 'degree', 'courses'];
protected $casts = [
    'qualities' => 'array',
    'courses' => 'array',
];
```

#### Tutor & Nanny Models
```php
// Both now support multiple addresses
public function addresses()
{
    return $this->morphMany(Address::class, 'addressable');
}
```

## Backend Changes

### New Enums
- `App\Enums\Course\NameEnum`: Common course names (Primeros Auxilios, Cuidado Infantil, etc.)
- `App\Enums\Career\DegreeEnum`: Academic degrees (Licenciatura, Maestría, etc.)

### Controllers

#### EnumController (New)
Exposes enum values to frontend:
- `GET /api/enums/qualities`
- `GET /api/enums/degrees`
- `GET /api/enums/course-names`
- `GET /api/enums/all`

#### BookingController
Updated to pass enum values to views:
```php
return Inertia::render('Booking/Create', [
    'qualities'   => QualityEnum::labels(),
    'degrees'     => DegreeEnum::labels(),
    'courseNames' => CourseNameEnum::labels(),
]);
```

### Services

#### BookingService
- **create()**: Creates booking with polymorphic address and new fields
- **update()**: Updates booking including qualities, degree, courses
- Handles both inline address creation and existing address selection

#### AddressService
- **createAddress()**: Supports polymorphic owner (owner_type, owner_id)

### Request Validation
Both `CreateBookingRequest` and `UpdateBookingRequest` now validate:
```php
'booking.qualities'   => ['nullable', 'array'],
'booking.degree'      => ['nullable', 'string'],
'booking.courses'     => ['nullable', 'array'],
```

## Frontend Changes

### New Components

#### StepQualitiesCourses.vue
New wizard step (Step 4) for selecting:
- **Qualities**: Multi-select with badges (Responsable, Paciente, Creativa, etc.)
- **Degree**: Single select dropdown (Licenciatura, Maestría, etc.)
- **Courses**: Multi-select with badges (Primeros Auxilios, Nutrición Infantil, etc.)

#### EmptyState.vue
Reusable component for empty states in DataTables:
```vue
<EmptyState
  icon="solar:inbox-line-broken"
  title="No hay direcciones"
  description="Crea tu primera dirección"
  action-label="Crear dirección"
  @action="handleCreate"
/>
```

### Updated Components

#### StepAddress.vue
Complete redesign to support:
- **Select Mode**: Choose from existing tutor addresses (displayed as cards)
- **Create Mode**: Inline form for creating new address
- **Edit Mode**: Inline form for editing selected address
- Seamless switching between modes

#### BookingForm.vue
- Now supports 4 steps (added "Requisitos" step)
- Passes enum values to child components
- Updated step navigation logic

#### bookingFormService.ts
Updated to handle:
- New booking fields (qualities, degree, courses)
- Proper validation schema
- Payload creation with new fields

### Type Definitions
```typescript
export interface Booking {
  // ... existing fields
  qualities?: string[]
  degree?: string | null
  courses?: string[]
}
```

## Usage Examples

### Creating a Booking with Inline Address
```php
POST /bookings
{
  "booking": {
    "tutor_id": 1,
    "description": "Need a nanny for weekdays",
    "recurrent": false,
    "children": ["child-ulid-1"],
    "qualities": ["Responsable", "Paciente"],
    "degree": "Licenciatura",
    "courses": ["Primeros Auxilios", "Cuidado Infantil"]
  },
  "address": {
    "postal_code": "44100",
    "street": "Av. Hidalgo 123",
    "neighborhood": "Centro",
    "type": "Casa"
  },
  "appointments": [...]
}
```

### Creating a Booking with Existing Address
```php
POST /bookings
{
  "booking": {
    "tutor_id": 1,
    "address_id": 5,  // Existing tutor address
    "description": "Weekend childcare",
    // ... rest of fields
  }
}
```

### Retrieving Tutor Addresses
```php
$tutor = Tutor::with('addresses')->find($id);
foreach ($tutor->addresses as $address) {
    echo $address->street;
}
```

## Testing

Run the feature tests:
```bash
php artisan test --filter=BookingPolymorphicTest
```

Tests cover:
- Creating bookings with inline polymorphic addresses
- Selecting existing tutor addresses
- Updating bookings with new qualities/degree/courses
- Multiple addresses per tutor
- Polymorphic address relations

## Migration Instructions

1. **Backup your database** before running migrations

2. Run migrations:
```bash
php artisan migrate
```

3. The migration will:
   - Add polymorphic columns to addresses
   - Migrate existing User addresses
   - Migrate existing Booking addresses
   - Add new fields to bookings

4. Test the booking flow:
   - Create a new booking
   - Try selecting an existing address
   - Try creating a new inline address
   - Add qualities, degree, and courses
   - Verify everything saves correctly

## UI Flow

### Step 1: Servicio (Service Details)
- Description
- Children selection
- Recurrent toggle

### Step 2: Citas (Appointments)
- Date/time selection
- Duration

### Step 3: Dirección (Address)
- **If tutor has addresses**: Display cards to select from
- **Create New**: Inline form for new address
- **Edit**: Inline form for editing selected address
- Fields: postal code, street, neighborhood, type, etc.

### Step 4: Requisitos (Requirements)
- **Qualities**: Add multiple desired qualities (badges)
- **Degree**: Select academic level
- **Courses**: Add multiple specialized courses (badges)

## API Endpoints

### Enums
- `GET /api/enums/qualities` - Get all quality options
- `GET /api/enums/degrees` - Get all degree options
- `GET /api/enums/course-names` - Get all course options
- `GET /api/enums/all` - Get all enums at once

### Addresses (existing routes still work)
- `POST /addresses` - Create address
- `PATCH /addresses/{id}` - Update address
- `DELETE /addresses/{id}` - Delete address

### Bookings (existing routes, now with enhanced data)
- `GET /bookings` - List bookings
- `GET /bookings/create` - Show create form (with enum data)
- `POST /bookings` - Store booking
- `GET /bookings/{id}` - Show booking
- `GET /bookings/{id}/edit` - Edit form (with enum data)
- `PUT /bookings/{id}` - Update booking
- `DELETE /bookings/{id}` - Delete booking

## Troubleshooting

### Migration Issues
If migration fails:
1. Check if you have existing bookings with null address_id
2. Ensure User model has address_id column
3. Run migration step-by-step if needed

### Frontend Build Issues
```bash
npm install
npm run build
```

### Address Not Showing
- Verify addressable_type and addressable_id are set correctly
- Check eager loading: `Booking::with('address')->get()`

## Future Enhancements

Potential improvements:
- Geo-location for addresses
- Address validation via external API
- Nanny search by qualities/degree/courses
- Saved address templates
- Address sharing between family members
