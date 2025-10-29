import Nanny from "./Nanny";
import { Rol } from "./Rol";
export interface User {
  ulid: string;
  name: string;
  surnames: string;
  email: string;
  email_verified_at: date;
  number: string;
  roles: Array<Rol>;
  tutor?: Tutor; 
  nanny?: Nanny;
  avatar_url?: string;
  created_at: string;
}
