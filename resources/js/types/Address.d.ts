import { User } from "./User"
export interface Address 
{
  id?: number
  postal_code: string
  street: string
  name: string
  neighborhood: string
  external_number: string
  internal_number?: string | null
  municipality?: string | null
  state?: string | null
  latitude?: number | null
  longitude?: number | null
  type: string
  nanny_id?: number  
  user?: User
}
