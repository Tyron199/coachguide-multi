import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
    class?: string;
    items?: {
        title: string;
        href: NonNullable<InertiaLinkProps['href']>;
    }[];
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    tenant: {
        id: string;
        domain: string;
        subscribed: boolean;
    };
    sidebarOpen: boolean;
    flash: {
        success: string;
        error: string;
        info: string;
        warning: string;
    };
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    phone?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    roles: string[];
}



export interface Company {
    id: number;
    name: string;
    address?: string;
    industry_sector?: string;
    contact_person_name?: string;
    contact_person_position?: string;
    contact_person_email?: string;
    contact_person_phone?: string;
    billing_contact_name?: string;
    billing_contact_email?: string;
    billing_contact_phone?: string;
    invoicing_notes?: string;
    users_count?: number;
    created_at: string;
    updated_at: string;
}

export interface UserProfile {
    id: number;
    user_id: number;
    address?: string;
    birthdate?: string;
    medical_conditions?: string[];
    emergency_contact_name?: string;
    emergency_contact_phone?: string;
    preferred_method_of_communication?: string;
    goal_summary?: string;
    objectives?: string;
    focus_areas?: string[];
    created_at: string;
    updated_at: string;
}

export interface Client {
    id: number;
    name: string;
    email: string;
    phone?: string;
    avatar?: string;
    created_at: string;
    archived?: boolean;
    company?: Company;
    profile?: UserProfile;
    assigned_coach?: {
        id: number;
        name: string;
    };
}

export interface PaginatedClients {
    data: Client[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

export interface PaginatedCompanies {
    data: Company[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}
export type BreadcrumbItemType = BreadcrumbItem;

// Sorting interfaces
export type SortDirection = 'asc' | 'desc';

export interface SortableFilters {
    search?: string;
    sort_by?: string;
    sort_direction?: SortDirection;
}

export interface ClientFilters extends SortableFilters {
    company_id?: number;
    no_company?: boolean;
    coach_id?: number;
    archived?: boolean;
}

export interface CompanyFilters extends SortableFilters {
    search?: string;
}

export interface CalendarEvent {
    id: number;
    coaching_session_id: number;
    user_id: number;
    provider: string;
    external_event_id: string;
    external_calendar_id?: string;
    meeting_url?: string;
    meeting_id?: string;
    sync_status: string;
    last_synced_at?: string;
    created_at: string;
    updated_at: string;
}

export interface CoachingSession {
    id: number;
    client_id: number;
    coach_id: number;
    scheduled_at: string;
    start_at?: string;
    end_at?: string;
    session_type: string;
    duration?: number;
    client_attended?: boolean;
    session_number: number;
    formatted_duration?: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    client?: Client;
    coach?: User;
    is_past: boolean;
    calendar_events?: CalendarEvent[];
}

export interface CoachingNote {
    id: number;
    session_id?: number;
    coach_id: number;
    client_id: number;
    title: string;
    content: string;
    created_at: string;
    updated_at: string;
    session?: CoachingSession;
    coach?: User;
    client?: Client;
    attachments?: Attachment[];
}

export interface CoachingTask {
    id: number;
    session_id?: number;
    coach_id: number;
    client_id: number;
    title: string;
    description: string;
    deadline?: string;
    status: 'pending' | 'review' | 'completed' | 'cancelled';
    send_reminders: boolean;
    evidence_required: boolean;
    completed_at?: string;
    reviewed_at?: string;
    created_at: string;
    updated_at: string;
    session?: CoachingSession;
    coach?: User;
    client?: Client;
    reminders?: CoachingTaskReminder[];
    actions_count?: number;
}

export interface CoachingTaskReminder {
    id: number;
    coaching_task_id: number;
    user_id: number;
    remind_at: string;
    status: string;
    label: string;
    created_at: string;
    updated_at: string;
}

export interface Attachment {
    id: number;
    original_name: string;
    file_name: string;
    file_path: string;
    mime_type: string;
    file_size: number;
    url: string;
    formatted_size: string;
    is_image: boolean;
    created_at: string;
    updated_at: string;
}

export interface Coach {
    id: number;
    name: string;
    email: string;
    phone?: string;
    assigned_clients_count: number;
    archived: boolean;
    status: string;
    created_at: string;
    updated_at: string;
}

export interface PaginatedCoaches {
    data: Coach[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

export interface CoachFilters {
    search?: string;
    archived: boolean;
    sort_by?: string;
    sort_direction?: string;
}

export interface Administrator {
    id: number;
    name: string;
    email: string;
    phone?: string;
    archived: boolean;
    status: string;
    created_at: string;
    updated_at: string;
}

export interface PaginatedAdministrators {
    data: Administrator[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

export interface AdministratorFilters {
    search?: string;
    archived: boolean;
    sort_by?: string;
    sort_direction?: string;
}
