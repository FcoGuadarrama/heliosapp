export interface DashboardPending {
    id: number;
    client_name: string;
    status: string;
    status_label: string;
    action_label: string;
    url: string;
    created_at: string;
}

export interface DashboardClient {
    id: number;
    name: string;
    municipality: string | null;
    created_at: string;
    url: string;
}

export interface DashboardOverview {
    metrics: {
        clients: number | null;
        review: number | null;
        processing: number | null;
        quotes: number | null;
    };
    pending: DashboardPending[];
    recent_clients: DashboardClient[];
    capabilities: {
        view_clients: boolean;
        view_receipts: boolean;
        create_clients: boolean;
        import_receipts: boolean;
    };
    updated_at: string;
}
