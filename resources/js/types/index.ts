export interface SharedData {
    [key: string]: unknown;
    auth: { user: User | null };
}

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
}
