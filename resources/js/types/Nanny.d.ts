import User from '.User';
export interface Nanny {
  id:number;
  ulid: number;
  user_id: number;
  bio?: string;
  availability: boolean;
  start_date: string; 
  address_id?: number;
  address?: Address; 

  user?:User;
  address?: Address;
  courses?: Course[];
  careers?: Career[];
  qualities?: Quality[];
  reviews?: Review[];
  booking_appointments?: BookingAppointment[];
}