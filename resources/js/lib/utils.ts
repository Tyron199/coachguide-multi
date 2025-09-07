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