# Quick Start Guide - Bookings & Reviews DataTable

## For Developers

### Running Migrations
Before using the Reviews module, run:
```bash
php artisan migrate
```

This adds the `approved` field to the reviews table.

### Accessing the Modules

**Bookings List:**
```
URL: /bookings
Route name: bookings.index
Component: resources/js/Pages/Booking/Index.vue
```

**Reviews List:**
```
URL: /reviews  
Route name: reviews.index
Component: resources/js/Pages/Review/Index.vue
```

## Features Overview

### Bookings Module

#### Displayed Columns
1. **Tutor** - Name and surnames of the tutor
2. **Dirección** - Street and neighborhood
3. **Niños** - Count of children in the booking
4. **Citas** - Count of appointments + nanny name
5. **Fecha Creación** - Creation date (sortable)
6. **Acciones** - View, Edit, Delete buttons

#### Available Actions
- 🔍 **Search**: Search by description
- 📊 **Sort**: Sort by creation date or description
- 📄 **Pagination**: 12 items per page
- 👁️ **View**: Navigate to booking details
- ✏️ **Edit**: Navigate to edit form
- 🗑️ **Delete**: Delete with confirmation modal
- 🔲 **Toggle Columns**: Show/hide columns

### Reviews Module

#### Displayed Columns
1. **Calificación** - Star rating (⭐) with numeric value (sortable)
2. **Comentario** - Review text (truncated, hover for full text)
3. **Para** - Reviewable entity (Niñera/Tutor) with name
4. **Estado** - Approval status badge (sortable)
   - 🟢 Aprobado (green)
   - 🟡 Pendiente (amber)
5. **Fecha** - Creation date (sortable)
6. **Acciones** - Toggle approval button

#### Available Actions
- 🔍 **Search**: Search by comment text
- 📊 **Sort**: Sort by rating, date, or approval status
- 🎯 **Filter**: Filter by approval status (Aprobados/No Aprobados)
- 📄 **Pagination**: 12 items per page
- 🌍 **Toggle Approval**: 
  - 🌍 Green icon = Public/Approved
  - 🌍❌ Amber icon = Private/Not Approved
- 🔲 **Toggle Columns**: Show/hide columns

## For Frontend Development

### BookingTable Component Usage

```vue
<script setup lang="ts">
import BookingTable from '@/Pages/Booking/components/BookingTable.vue';
import type { FetcherResponse } from '@/types/FetcherResponse';
import type { Booking } from '@/types/Booking';

defineProps<{
    bookings: FetcherResponse<Booking>;
}>();
</script>

<template>
    <BookingTable :resource="bookings" />
</template>
```

### ReviewTable Component Usage

```vue
<script setup lang="ts">
import ReviewTable from '@/Pages/Review/components/ReviewTable.vue';
import type { FetcherResponse } from '@/types/FetcherResponse';
import type { Review } from '@/types/Review';

defineProps<{
    reviews: FetcherResponse<Review>;
}>();
</script>

<template>
    <ReviewTable :resource="reviews" />
</template>
```

## For Backend Development

### Bookings Service Example

```php
use App\Services\BookingService;

class BookingController extends Controller
{
    public function index(BookingService $bookingService)
    {
        $bookings = $bookingService->indexFetch();
        
        return Inertia::render('Booking/Index', [
            'bookings' => $bookings
        ]);
    }
}
```

### Reviews Service Example

```php
use App\Services\ReviewService;

class ReviewController extends Controller
{
    public function index(ReviewService $reviewService)
    {
        $reviews = $reviewService->indexFetch();
        
        return Inertia::render('Review/Index', [
            'reviews' => $reviews
        ]);
    }
    
    public function toggleApproved(Review $review, ReviewService $reviewService)
    {
        $reviewService->toggleApproved($review);
        
        return back()->with('notification', 'Estado actualizado.');
    }
}
```

## Query Parameters

Both modules support standard DataTable query parameters:

### Search
```
?searchTerm=text
```

### Sorting
```
?sortBy=created_at&sortDirection=desc
```

### Filtering (Reviews only)
```
?filters[approved]=true
```

### Pagination
```
?page=2&per_page=12
```

### Multiple Parameters
```
?searchTerm=test&sortBy=rating&sortDirection=desc&filters[approved]=true&page=1
```

## API Endpoints

### Bookings
- `GET /bookings` - List bookings
- `GET /bookings/{id}` - Show booking
- `GET /bookings/create` - Create form
- `POST /bookings` - Store booking
- `GET /bookings/{id}/edit` - Edit form
- `PUT /bookings/{id}` - Update booking
- `DELETE /bookings/{id}` - Delete booking

### Reviews
- `GET /reviews` - List reviews
- `POST /reviews/{id}/toggle-approved` - Toggle approval status

## TypeScript Types

### Booking Type
```typescript
interface Booking {
  id: number;
  description: string;
  recurrent: boolean;
  created_at: string;
  tutor_id: number;
  address_id: number;
  status: string;
  
  // Relations
  tutor?: Tutor;
  address?: Address;
  children?: Child[];
  booking_appointments?: BookingAppointment[];
}
```

### Review Type
```typescript
interface Review {
  id: number;
  reviewable_type: string;
  reviewable_id: number;
  rating: number;
  comments?: string;
  approved: boolean;
  created_at: string;
  
  // Relations
  reviewable?: Nanny | Tutor;
  nanny?: Nanny;
  tutor?: Tutor;
}
```

## Adding to Navigation

To add Reviews to your main navigation, update your layout component:

```vue
<template>
  <nav>
    <!-- ... other links ... -->
    <Link :href="route('bookings.index')">
      <Icon icon="ri:calendar-schedule-line" />
      Servicios
    </Link>
    
    <Link :href="route('reviews.index')">
      <Icon icon="mdi:star-outline" />
      Reviews
    </Link>
  </nav>
</template>
```

## Troubleshooting

### Reviews not showing?
- Make sure you ran the migration: `php artisan migrate`
- Check that routes/reviews.php is loaded in routes/web.php

### Search not working?
- Verify the backend service includes `allowSearch()` with correct fields
- Check that searchable fields exist in the model

### Sorting not working?
- Ensure `field` prop in Column matches backend `$sortables` array
- Verify `:sortable="true"` is set on the Column component

### Relationships not loading?
- Check that eager loading is configured in the service's `with()` array
- Verify relationships are defined in the model

## Performance Tips

1. **Add Database Indexes**:
```sql
CREATE INDEX idx_bookings_created_at ON bookings(created_at);
CREATE INDEX idx_reviews_created_at ON reviews(created_at);
CREATE INDEX idx_reviews_approved ON reviews(approved);
CREATE INDEX idx_reviews_rating ON reviews(rating);
```

2. **Monitor N+1 Queries**:
Use Laravel Debugbar or Telescope to ensure relationships are eager-loaded.

3. **Optimize Large Datasets**:
Consider cursor-based pagination for very large tables (100k+ records).

## Testing Checklist

- [ ] Bookings list loads without errors
- [ ] Bookings search works
- [ ] Bookings sorting works
- [ ] Bookings pagination works
- [ ] Bookings actions (view/edit/delete) work
- [ ] Reviews list loads without errors
- [ ] Reviews search works
- [ ] Reviews sorting works
- [ ] Reviews filtering by approval works
- [ ] Reviews pagination works
- [ ] Reviews approval toggle works and shows notification
- [ ] Column visibility toggle works on both modules
- [ ] No console errors
- [ ] No N+1 query warnings in Laravel logs

## Support

For issues or questions:
1. Check the main documentation: `DATATABLE_IMPLEMENTATION_SUMMARY.md`
2. Review the pattern guide: `docs/datatable/patron-index-listados.md`
3. Check the API reference: `resources/js/components/datatable/README.md`
4. Look at the Users implementation as reference: `resources/js/Pages/User/`
