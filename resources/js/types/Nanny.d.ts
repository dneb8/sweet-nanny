import User from '.User';
export interface Nanny {
  id:number;
  ulid: number;
  user_id: number;
  bio?: string;
  availability: boolean;
  start_date: string; 

  user?:User;
  addresses?: Address[];
  courses?: Course[];
  careers?: Career[];
  qualities?: Quality[];
  reviews?: Review[];
  booking_appointments?: BookingAppointment[];
}