export interface ProfessionalCredentialProvider {
    id: number;
    name: string;
    full_name: string;
    website_url: string;
    logo_url?: string;
    has_credential: boolean;
    credential?: UserProfessionalCredential;
}

export interface UserProfessionalCredential {
    id: number;
    credential_name?: string;
    expiry_date?: string;
    is_expired: boolean;
    is_expiring_soon: boolean;
    days_until_expiry?: number;
    original_filename: string;
    formatted_file_size: string;
    created_at: string;
}

export interface ProfessionalCredentialStats {
    total_providers: number;
    uploaded_count: number;
    expiring_soon_count: number;
    expired_count: number;
}

export interface ProfessionalCredentialsPageProps {
    providers: ProfessionalCredentialProvider[];
    stats: ProfessionalCredentialStats;
}
