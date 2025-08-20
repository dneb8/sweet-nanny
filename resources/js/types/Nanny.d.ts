export interface Nanny {
  ulid: number;
  user_id: number;
  bio?: string;
  availability: boolean;
  start_date: string; 
  address_id?: number;
  address?: Address; 
}