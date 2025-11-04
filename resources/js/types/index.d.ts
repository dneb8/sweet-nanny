import type { Config } from 'ziggy-js';

export interface Auth {
    user: User & {
        roles?: string[];
    };
    permisos?: string[];
    roles?: string[];
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: string;
    isActive?: boolean;
}

export interface FlashMessage {
    title: string;
    description?: string;
}

export interface FlashMessages {
    message?: FlashMessage;
    notification?: string;
    success?: string;
    error?: string;
    warning?: string;
    info?: string;
    status?: string;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
    flash?: FlashMessages;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    avatar_url?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;
