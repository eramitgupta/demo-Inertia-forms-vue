const dateFormat = new Intl.DateTimeFormat(undefined, {
    dateStyle: 'medium',
    timeStyle: 'short',
});

export function formatDate(value: string | null): string {
    return value ? dateFormat.format(new Date(value)) : '—';
}

export function formatFileSize(bytes: number): string {
    if (bytes < 1024) {
        return `${bytes} B`;
    }

    return bytes < 1024 * 1024
        ? `${(bytes / 1024).toFixed(1)} KB`
        : `${(bytes / 1024 / 1024).toFixed(1)} MB`;
}
