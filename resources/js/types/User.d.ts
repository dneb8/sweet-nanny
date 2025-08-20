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
}