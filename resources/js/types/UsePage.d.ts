import { User } from "./User";

export interface PageProps {
    auth: {
        user: User;
        roles: string[];
        permisos: string[];
    } | null;
    flash: {
        message: string;
        notification: {
            type: string;
            message: string;
        };
    };
    [key: string]: any;
}
