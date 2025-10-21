export interface Booking {
  id: number
  description: string
  recurrent: boolean
  created_at: string
  tutor_id: number
  address_id: number
  status: 'pending' | 'confirmed' | 'completed' | 'cancelled' | string
  qualities?: string[]
  careers?: string[] // Changed to array
  courses?: string[]

  booking_appointments?: BookingAppointment[]
  tutor?: Tutor
  address?: Address
  children?: Child[]
  childrenWithTrashed?: Child[]
}
