import { Nanny } from "./Nanny";
export interface Course {
  id?: number;
  name: string;
  organization?: string;
  date: string;
  nanny_id?: number;

  nanny?: Nanny
}