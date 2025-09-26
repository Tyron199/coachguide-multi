import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

//Format long numbers to a readable format
export function formatNumber(number: number) {
    return number.toLocaleString('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    });
}

// File icon utility types
export interface FileIconInfo {
    icon: string;
    color: string;
}

// Format date utility function
export function formatDate(dateString: string | Date): string {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

// Format relative date (e.g., "2 days ago", "in 3 months")
export function formatRelativeDate(dateString: string | Date): string {
    const date = new Date(dateString);
    const now = new Date();
    const diffInMs = now.getTime() - date.getTime();
    const diffInDays = Math.floor(diffInMs / (1000 * 60 * 60 * 24));

    if (diffInDays === 0) {
        return 'today';
    } else if (diffInDays === 1) {
        return 'yesterday';
    } else if (diffInDays < 7) {
        return `${diffInDays} days ago`;
    } else if (diffInDays < 30) {
        const weeks = Math.floor(diffInDays / 7);
        return `${weeks} ${weeks === 1 ? 'week' : 'weeks'} ago`;
    } else if (diffInDays < 365) {
        const months = Math.floor(diffInDays / 30);
        return `${months} ${months === 1 ? 'month' : 'months'} ago`;
    } else {
        const years = Math.floor(diffInDays / 365);
        return `${years} ${years === 1 ? 'year' : 'years'} ago`;
    }
}

// Format file size to human readable format
export function formatFileSize(bytes: number): string {
    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    let size = bytes;
    let unitIndex = 0;

    while (size >= 1024 && unitIndex < units.length - 1) {
        size /= 1024;
        unitIndex++;
    }

    return `${size.toFixed(unitIndex === 0 ? 0 : 2)} ${units[unitIndex]}`;
}

// Get appropriate icon and color for a file based on extension and MIME type
export function getFileIcon(fileName: string, mimeType?: string): FileIconInfo {
    const extension = fileName.toLowerCase().split('.').pop() || '';

    // Image files
    if (mimeType?.startsWith('image/') || ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp', 'ico'].includes(extension)) {
        return { icon: 'FileImage', color: 'text-blue-500' };
    }

    // PDF files
    if (mimeType === 'application/pdf' || extension === 'pdf') {
        return { icon: 'FileText', color: 'text-red-500' };
    }

    // Spreadsheet files
    if (mimeType?.includes('spreadsheet') || mimeType?.includes('excel') ||
        ['xlsx', 'xls', 'csv', 'ods'].includes(extension)) {
        return { icon: 'FileSpreadsheet', color: 'text-green-500' };
    }

    // Word documents
    if (mimeType?.includes('document') || mimeType?.includes('word') ||
        ['doc', 'docx', 'odt', 'rtf'].includes(extension)) {
        return { icon: 'FileText', color: 'text-blue-600' };
    }

    // Presentation files
    if (mimeType?.includes('presentation') ||
        ['ppt', 'pptx', 'odp'].includes(extension)) {
        return { icon: 'Presentation', color: 'text-orange-500' };
    }

    // Archive files
    if (['zip', 'rar', '7z', 'tar', 'gz', 'bz2'].includes(extension)) {
        return { icon: 'Archive', color: 'text-purple-500' };
    }

    // Video files
    if (mimeType?.startsWith('video/') ||
        ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'].includes(extension)) {
        return { icon: 'Video', color: 'text-pink-500' };
    }

    // Audio files
    if (mimeType?.startsWith('audio/') ||
        ['mp3', 'wav', 'flac', 'aac', 'ogg', 'm4a'].includes(extension)) {
        return { icon: 'Music', color: 'text-indigo-500' };
    }

    // Code files
    if (['js', 'ts', 'jsx', 'tsx', 'vue', 'html', 'css', 'scss', 'php', 'py', 'java', 'cpp', 'c', 'cs', 'rb', 'go', 'rs'].includes(extension)) {
        return { icon: 'Code', color: 'text-emerald-500' };
    }

    // Text files
    if (mimeType?.startsWith('text/') ||
        ['txt', 'md', 'json', 'xml', 'yaml', 'yml', 'log'].includes(extension)) {
        return { icon: 'FileText', color: 'text-gray-600' };
    }

    // Default fallback
    return { icon: 'File', color: 'text-gray-500' };
}