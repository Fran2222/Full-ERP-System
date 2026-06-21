@once
    <style>
        .wmc-memo-company-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 18px 22px 14px;
            border-top: 5px solid #0f3f8f;
            border-bottom: 3px solid #0f3f8f;
            background: linear-gradient(135deg, #ffffff 0%, #f8fbff 60%, #eef5ff 100%);
            border-radius: 14px 14px 0 0;
            margin: -8px -8px 22px;
        }

        .wmc-memo-company-brand {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }

        .wmc-memo-company-logo {
            width: 84px;
            height: 54px;
            object-fit: contain;
            flex: 0 0 auto;
        }

        .wmc-memo-company-name h1 {
            font-size: 28px;
            line-height: .95;
            letter-spacing: 1px;
            margin: 0;
            text-transform: uppercase;
            color: #0f3f8f;
            font-weight: 900;
        }

        .wmc-memo-company-name .wmc-memo-corp {
            display: block;
            color: #d9232e;
            font-size: 18px;
            letter-spacing: 2px;
            font-weight: 900;
            margin-top: 2px;
        }

        .wmc-memo-company-name .wmc-memo-services {
            display: block;
            color: #334155;
            font-size: 11px;
            letter-spacing: 1px;
            font-weight: 700;
            margin-top: 3px;
        }

        .wmc-memo-company-details {
            text-align: right;
            font-size: 10px;
            line-height: 1.35;
            color: #475569;
            max-width: 310px;
        }

        .wmc-memo-company-details strong {
            color: #334155;
        }

        @media (max-width: 767.98px) {
            .wmc-memo-company-header {
                align-items: flex-start;
                flex-direction: column;
                padding: 16px;
                margin: -8px -8px 18px;
            }

            .wmc-memo-company-logo {
                width: 70px;
                height: 46px;
            }

            .wmc-memo-company-name h1 {
                font-size: 22px;
            }

            .wmc-memo-company-name .wmc-memo-corp {
                font-size: 15px;
            }

            .wmc-memo-company-details {
                max-width: none;
                text-align: left;
                font-size: 9.5px;
            }
        }
    </style>
@endonce

<div class="wmc-memo-company-header">
    <div class="wmc-memo-company-brand">
        <img src="{{ asset('images/wizmaster-logo.png') }}" alt="Wizmaster Logo" class="wmc-memo-company-logo">
        <div class="wmc-memo-company-name">
            <h1>Wizmaster</h1>
            <span class="wmc-memo-corp">Corporation</span>
            <span class="wmc-memo-services">Computer Sales &amp; Services</span>
        </div>
    </div>
    <div class="wmc-memo-company-details">
        <div><strong>ADDRESS</strong> 1/F Solana District, Andres Bonifacio Ave., San Miguel, Iligan City</div>
        <div><strong>TEL. NUMBER</strong> (063) 222-4277 / (063) 915-501-4668</div>
        <div><strong>EMAIL</strong> sales@wizmaster.com.co</div>
        <div><strong>Website</strong> http://wizmaster.com.co</div>
    </div>
</div>
