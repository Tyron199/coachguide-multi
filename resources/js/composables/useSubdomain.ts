import { ref, computed, watch } from 'vue';

// Reserved subdomains (should match backend)
const RESERVED_SUBDOMAINS = [
    'www', 'mail', 'app', 'admin', 'api', 'blog', 'support', 'help',
    'status', 'dev', 'staging', 'test', 'ftp', 'smtp', 'pop', 'imap'
];

/**
 * Sanitize company name into a valid subdomain
 */
export const sanitizeToSubdomain = (name: string): string => {
    return name
        .toLowerCase()
        .trim()
        .replace(/[^a-zA-Z0-9\s-]/g, '') // Remove special characters except spaces and hyphens
        .replace(/\s+/g, '-') // Replace spaces with hyphens
        .replace(/-+/g, '-') // Replace multiple hyphens with single hyphen
        .replace(/^-+|-+$/g, '') // Remove leading/trailing hyphens
        .substring(0, 30); // Limit to max length
};

/**
 * Validate subdomain according to our rules
 */
export const validateSubdomain = (value: string, reservedSubdomains: string[] = RESERVED_SUBDOMAINS): string | null => {
    if (!value) return 'Subdomain is required';
    if (value.length < 3) return 'Subdomain must be at least 3 characters';
    if (value.length > 30) return 'Subdomain cannot be longer than 30 characters';
    if (!/^[a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]$/.test(value)) {
        return 'Subdomain may only contain letters, numbers, and hyphens. Cannot start or end with a hyphen.';
    }
    if (reservedSubdomains.includes(value.toLowerCase())) {
        return 'This subdomain is reserved and cannot be used';
    }
    return null;
};

/**
 * Composable for subdomain management with auto-generation
 */
export const useSubdomain = (reservedSubdomains?: string[]) => {
    // Use provided reserved subdomains or fall back to default
    const reserved = reservedSubdomains || RESERVED_SUBDOMAINS;
    const companyName = ref('');
    const subdomain = ref('');
    const hasManuallyEditedSubdomain = ref(false);

    // Computed validation using the reserved subdomains
    const subdomainError = computed(() =>
        subdomain.value ? validateSubdomain(subdomain.value, reserved) : null
    );

    const isSubdomainValid = computed(() =>
        subdomain.value && !subdomainError.value
    );

    // Auto-generate subdomain from company name
    watch(companyName, (newName) => {
        if (newName && !hasManuallyEditedSubdomain.value) {
            const generated = sanitizeToSubdomain(newName);
            if (generated.length >= 3) {
                subdomain.value = generated;
            } else {
                subdomain.value = '';
            }
        } else if (!newName) {
            // If company name is cleared, clear subdomain too (unless manually edited)
            if (!hasManuallyEditedSubdomain.value) {
                subdomain.value = '';
            }
        }
    }, { immediate: true });

    // Mark as manually edited
    const markAsManuallyEdited = () => {
        hasManuallyEditedSubdomain.value = true;
    };

    return {
        companyName,
        subdomain,
        hasManuallyEditedSubdomain,
        subdomainError,
        isSubdomainValid,
        markAsManuallyEdited,
        validateSubdomain: (value: string) => validateSubdomain(value, reserved),
        sanitizeToSubdomain,
        reservedSubdomains: reserved
    };
};
