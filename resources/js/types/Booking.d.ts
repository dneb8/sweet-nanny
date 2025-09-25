export interface Booking {
  id: number
  description: string
  recurrent: boolean
  created_at: string
  tutor_id: number
  address_id: number

  tutor?: Tutor
  address?: Address
}
