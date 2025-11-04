import User from '.User';
import Address from '.Address';
import { Child } from './Child';

export interface Tutor {
  id: string;
  ulid: string;
  user_id: number;
  address_id?: number;
  emergency_contact?: string;
  emergency_number?: string;
  address?: Address; 
  user?: User;
  children?: Child[];
  addresses?: Address[];
}
