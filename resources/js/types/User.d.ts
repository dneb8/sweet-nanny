import { Rol } from "./Rol";
export interface User {
    name: string;
    first_surname: string;
    last_surname: string;
    email: string;
    number: string;
    roles?: Array<Rol>;
}
