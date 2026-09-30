export type FormsDemoPageProps = {
    demo: string;
    label: string;
    className: string;
    demos: Record<string, string>;
};

export type FormEntrySummary = {
    id: number;
    title: string;
    createdAt: string | null;
    updatedAt: string | null;
};

export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
};

export type FormEntryFile = {
    name: string;
    size: number;
    url: string;
};

export type FormEntryRow = {
    label: string;
    type: 'text' | 'json' | 'files' | 'empty';
    value: string | null;
    files: FormEntryFile[];
};

export type FormEntrySection = {
    title: string | null;
    rows: FormEntryRow[];
};
