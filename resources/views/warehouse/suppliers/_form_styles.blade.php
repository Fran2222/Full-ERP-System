<style>
    .warehouse-form-card,
    .warehouse-form-section {
        background: #ffffff;
        border-radius: 18px !important;
        border: 1px solid #edf0f5 !important;
        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.055) !important;
    }

    .warehouse-form-card {
        overflow: hidden;
    }

    .warehouse-form-section {
        padding: 22px;
    }

    .warehouse-section-heading {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
    }

    .warehouse-soft-btn {
        border-radius: 10px;
        padding: 10px 18px;
        font-weight: 700;
    }

    .warehouse-input {
        min-height: 44px;
        border-radius: 12px;
        border-color: #d9dee8;
    }

    .warehouse-input:focus {
        border-color: #3f5cff;
        box-shadow: 0 0 0 .18rem rgba(63, 92, 255, .12);
    }

    .warehouse-badge {
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

    .warehouse-badge-success {
        background: #eaf8f0;
        color: #078642;
    }

    .warehouse-badge-muted {
        background: #f3f4f6;
        color: #6b7280;
    }

    @media (max-width: 768px) {
        .warehouse-form-section {
            padding: 18px;
        }
    }
</style>