<style>
    .sales-form-panel,
    .sales-panel,
    .sales-show-panel {
        border-radius: 18px !important;
        border: 1px solid #edf0f5 !important;
        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.055) !important;
        overflow: hidden;
    }

    .sales-soft-btn {
        border-radius: 10px;
        padding-top: 10px;
        padding-bottom: 10px;
        font-weight: 700;
    }

    .sales-input {
        min-height: 44px;
        border-radius: 12px;
        border-color: #d9dee8;
    }

    .sales-input:focus {
        border-color: #3f5cff;
        box-shadow: 0 0 0 .18rem rgba(63, 92, 255, .12);
    }

    .sales-form-section {
        border: 1px solid #edf0f5;
        border-radius: 16px;
        padding: 22px;
        background: #ffffff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.035);
    }

    .sales-section-heading {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
    }

    .sales-info-label {
        color: #8a94a6;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 6px;
    }

    .sales-customer-preview,
    .sales-line-preview {
        min-height: 44px;
        border: 1px solid #edf0f5;
        border-radius: 12px;
        background: #f8faff;
        padding: 10px 12px;
    }

    .sales-preview-name,
    .sales-line-code {
        color: #111827;
        font-size: 13px;
        font-weight: 800;
        line-height: 1.25;
    }

    .sales-preview-sub,
    .sales-line-name {
        color: #8a94a6;
        font-size: 12px;
        font-weight: 600;
        margin-top: 2px;
        line-height: 1.35;
    }

    .invoice-line-card {
        border: 1px solid #edf0f5;
        border-radius: 16px;
        padding: 18px;
        margin-bottom: 14px;
        background: linear-gradient(180deg, #fbfcff 0%, #ffffff 100%);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.03);
    }

    .invoice-line-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 14px;
        margin-bottom: 16px;
        padding-bottom: 14px;
        border-bottom: 1px dashed #edf0f5;
    }

    .invoice-remove-btn {
        padding-left: 14px;
        padding-right: 14px;
    }

    .sales-total-card {
        border: 1px solid #edf0f5;
        border-radius: 16px;
        padding: 22px;
        background: linear-gradient(180deg, #f8faff 0%, #ffffff 100%);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.035);
    }

    .sales-total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        margin-bottom: 12px;
        color: #6b7280;
    }

    .sales-total-row strong {
        color: #111827;
    }

    .sales-total-main {
        background: #eef4ff;
        color: #315cf6;
        border-radius: 14px;
        padding: 14px;
        margin-bottom: 0;
        font-size: 18px;
        font-weight: 900;
    }

    .sales-total-main strong {
        color: #315cf6;
    }

    .sales-total-note {
        color: #64748b;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 14px;
        padding: 13px 14px;
        font-size: 13px;
        font-weight: 600;
    }

    .sales-table thead th {
        background: #f4f6fb;
        color: #8a94a6;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 0;
        padding: 14px 16px;
        white-space: nowrap;
    }

    .sales-table tbody td {
        padding: 16px;
        border-bottom: 1px solid #edf0f5;
        vertical-align: middle;
    }

    .sales-table tbody tr {
        transition: all 0.18s ease-in-out;
    }

    .sales-table tbody tr:hover {
        background: #f8faff;
    }

    .sales-action-btn {
        width: 34px;
        height: 34px;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        line-height: 1;
        padding: 0;
        font-size: 14px;
    }

    .sales-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 800;
        line-height: 1;
        white-space: nowrap;
    }

    .sales-badge-success {
        background: #eaf8f0;
        color: #078642;
    }

    .sales-badge-info,
    .sales-badge-primary {
        background: #eef4ff;
        color: #315cf6;
    }

    .sales-badge-warning {
        background: #fff7e6;
        color: #b45309;
    }

    .sales-badge-danger {
        background: #fff1f2;
        color: #e11d48;
    }

    .sales-badge-muted {
        background: #f3f4f6;
        color: #6b7280;
    }

    .pagination {
        margin-bottom: 0;
    }

    @media (max-width: 768px) {
        .sales-form-section,
        .sales-total-card {
            padding: 18px;
        }

        .sales-section-heading,
        .invoice-line-top {
            flex-direction: column;
        }

        .invoice-remove-btn {
            width: 100%;
        }
    }
</style>