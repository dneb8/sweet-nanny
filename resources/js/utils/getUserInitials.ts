import { User } from "@/types/User";
import { toUpper } from "lodash";

export function getUserInitials(user: User): string {
    const firstInitial = user.name.slice(0, 1);
    const secondInitial = user.surnames.slice(0, 1);
    const initials = toUpper(firstInitial + secondInitial);
    return initials;
}
