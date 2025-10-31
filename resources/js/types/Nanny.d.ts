import User from '.User';
export interface Nanny {
  id:number;
  ulid: string;
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

// Simplified nanny data for selection UI
export interface NannySelectionData {
  id: string;
  name: string;
  profile_photo_url?: string | null;
  qualities: string[];
  careers: string[];
  courses: string[];
  experience?: {
    start_date: string;
  } | null;
  description?: string | null;
}