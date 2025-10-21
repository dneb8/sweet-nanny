export interface BookingAppointment {
  id: number
  booking_id: number
  nanny_id?: number | null
  // price_id: number

  start_date: string 
  end_date: string   
  status: 'pending' | 'confirmed' | 'completed' | 'cancelled' | string
  payment_status: 'unpaid' | 'paid' | 'refunded' | string
  extra_hours: number
  total_cost: number

  created_at: string
  updated_at: string

  booking?: Booking
  nanny?: Nanny
  price?: Price
}
