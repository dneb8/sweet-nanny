export interface Tutor {
  ulid: string;
  user_id: number;
  address_id?: number;
  emergency_contact?: string;
  emergency_number?: string;
  address?: Address; 
}
