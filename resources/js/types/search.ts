export type SearchResultType =
    | 'navigation'
    | 'client'
    | 'company'
    | 'session'
    | 'note'
    | 'task'
    | 'framework'
    | 'credential'
    | 'development'
    | 'supervision';

export interface SearchResult {
    type: SearchResultType;
    title: string;
    subtitle?: string;
    url: string;
    icon?: string;
}

export interface SearchResponse {
    results: {
        navigation: SearchResult[];
        clients: SearchResult[];
        companies: SearchResult[];
        sessions: SearchResult[];
        notes: SearchResult[];
        tasks: SearchResult[];
        frameworks: SearchResult[];
        credentials: SearchResult[];
        professional_development: SearchResult[];
        supervisions: SearchResult[];
    };
}

