<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        <div class="pos-lock-wrap">
            <div class="pos-lock-card">
                <div class="pos-lock-badge">POS Secure Access</div>
                <h1>Front Desk POS</h1>
                <p class="text-secondary mb-4">
                    Enter the POS access password before opening the cashier terminal.
                </p>

                @if(session('success'))
                    <div class="alert alert-success rounded-3">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger rounded-3">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('pos.authenticate') }}" autocomplete="off">
                    @csrf
                    <label class="form-label fw-semibold">POS Password / Cashier PIN</label>
                    <input type="password"
                           name="password"
                           class="form-control form-control-lg pos-pin-input"
                           placeholder="Enter POS password"
                           autofocus
                           required>

                    <button type="submit" class="btn btn-primary btn-lg w-100 mt-4 pos-open-btn">
                        Open POS Terminal
                    </button>
                </form>

                <div class="pos-lock-note mt-4">
                    Default temporary password is <strong>123456</strong>. You can change it in <code>.env</code> using
                    <code>POS_ACCESS_PASSWORD=yourpassword</code>.
                </div>
            </div>
        </div>
    </div>

    <style>
        .pos-lock-wrap {
            min-height: calc(100vh - 150px);
            display: grid;
            place-items: center;
            padding: 40px 16px;
            background:
                radial-gradient(circle at top left, rgba(59,130,246,.14), transparent 28%),
                radial-gradient(circle at bottom right, rgba(16,185,129,.14), transparent 28%);
        }
        .pos-lock-card {
            width: min(480px, 100%);
            background: rgba(255,255,255,.96);
            border: 1px solid rgba(226,232,240,.95);
            border-radius: 28px;
            padding: 34px;
            box-shadow: 0 24px 70px rgba(15,23,42,.12);
        }
        .pos-lock-badge {
            display: inline-flex;
            padding: 7px 12px;
            border-radius: 999px;
            background: #eef6ff;
            color: #2563eb;
            font-weight: 700;
            font-size: 12px;
            margin-bottom: 16px;
        }
        .pos-lock-card h1 {
            font-size: 34px;
            font-weight: 800;
            letter-spacing: -.04em;
            margin-bottom: 6px;
        }
        .pos-pin-input {
            border-radius: 16px;
            min-height: 54px;
        }
        .pos-open-btn {
            border-radius: 16px;
            min-height: 54px;
            font-weight: 700;
        }
        .pos-lock-note {
            color: #64748b;
            font-size: 13px;
            padding: 12px 14px;
            border-radius: 16px;
            background: #f8fafc;
        }
    </style>
</x-app-layout>
