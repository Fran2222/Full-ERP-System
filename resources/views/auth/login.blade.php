<x-guest-layout>
    <style>
        .wiz-login-page {
            min-height: 100vh;
            min-height: 100dvh;
            background: #ffffff;
            overflow-x: hidden;
            overflow-y: auto;
        }

        .wiz-login-left {
            min-height: 100vh;
            min-height: 100dvh;
            background: #ffffff;
        }

        .wiz-login-form-wrap {
            width: 100%;
            max-width: 760px;
            min-height: 100vh;
            min-height: 100dvh;
            padding: clamp(18px, 3vh, 32px) 24px clamp(14px, 2vh, 18px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .wiz-login-brand {
            width: 100%;
            color: inherit;
            display: flex !important;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 14px !important;
        }

        .wiz-login-logo-static {
            display: block;
            width: 190px;
            height: 190px;
            object-fit: contain;
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 14px;
        }

        .wiz-login-title {
            display: block;
            width: 100%;
            font-size: 38px;
            font-weight: 700;
            color: #061a44;
            letter-spacing: 1.5px;
            line-height: 1.1;
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 22px;
            text-align: center;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .auth-card {
            width: 100%;
            max-width: 760px;
            margin: 0 auto;
        }

        .auth-card form {
            width: 100%;
            max-width: 560px;
            margin-left: auto;
            margin-right: auto;
        }

        .auth-card h2 {
            font-size: 32px;
            font-weight: 800;
            color: #111827;
        }

        .auth-card .form-label {
            font-weight: 500;
            color: #64748b;
            margin-bottom: 8px;
        }

        .auth-card .form-control {
            min-height: 46px;
            border-radius: 3px;
            border: 1px solid #e5e7eb;
            padding: 10px 14px;
            font-size: 15px;
        }

        .auth-card .form-control:focus {
            border-color: #4f6df5;
            box-shadow: 0 0 0 .12rem rgba(79, 109, 245, .16);
        }

        .auth-card .form-check-input {
            border-radius: 3px;
            width: 16px;
            height: 16px;
            margin-top: 4px;
            border-color: #4f6df5;
        }

        .auth-card .form-check-input:checked {
            background-color: #4f6df5;
            border-color: #4f6df5;
        }

        .auth-card .form-check-label {
            color: #64748b;
        }

        .auth-card .btn-primary {
            min-height: 44px;
            border-radius: 3px;
            font-weight: 600;
            background: #3f5be8;
            border-color: #3f5be8;
        }

        .auth-card .btn-primary:hover {
            background: #314bd1;
            border-color: #314bd1;
        }

        .wiz-login-right-panel {
            min-height: 100vh;
            min-height: 100dvh;
            background:
                linear-gradient(135deg, rgba(15, 23, 42, .02), rgba(29, 78, 216, .02)),
                url('{{ asset('images/auth/01.png') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .wiz-login-footer {
            position: static;
            width: 100%;
            max-width: 560px;
            margin-left: auto;
            margin-right: auto;
            flex-shrink: 0;
            text-align: center;
            font-size: 13px;
            color: #64748b;
            line-height: 1.4;
            padding-top: 10px;
        }

        @media (max-width: 1199.98px) {
            .wiz-login-form-wrap {
                max-width: 660px;
            }

            .auth-card {
                max-width: 700px;
            }

            .auth-card form,
            .wiz-login-footer {
                max-width: 540px;
            }

            .wiz-login-title {
                font-size: 36px;
            }

            .wiz-login-logo-static {
                width: 180px;
                height: 180px;
            }
        }

        /* 125% display scale / shorter browser height safe layout */
        @media (max-height: 820px) and (min-width: 768px) {
            .wiz-login-form-wrap {
                justify-content: center;
                padding-top: 18px;
                padding-bottom: 14px;
            }

            .wiz-login-logo-static {
                width: 165px;
                height: 165px;
                margin-bottom: 10px;
            }

            .wiz-login-title {
                font-size: 34px;
                margin-bottom: 18px;
            }

            .wiz-login-brand {
                margin-bottom: 10px !important;
            }

            .auth-card h2 {
                font-size: 30px;
            }

            .auth-card .text-muted.mb-4 {
                margin-bottom: 1rem !important;
            }

            .auth-card .form-group.mb-3 {
                margin-bottom: .85rem !important;
            }

            .auth-card form > .d-flex.mb-4 {
                margin-bottom: 1rem !important;
            }

            .auth-card .form-control {
                min-height: 44px;
            }

            .auth-card .btn-primary {
                min-height: 44px;
            }

            .wiz-login-footer {
                font-size: 12px;
                padding-top: 8px;
            }
        }

        @media (max-width: 767.98px) {
            .wiz-login-left {
                min-height: 100vh;
            }

            .wiz-login-form-wrap {
                max-width: 100%;
                padding: 34px 26px 18px;
                justify-content: center;
            }

            .auth-card,
            .auth-card form,
            .wiz-login-footer {
                max-width: 100%;
            }

            .wiz-login-title {
                font-size: 24px;
                white-space: normal;
                margin-bottom: 18px;
            }

            .wiz-login-logo-static {
                width: 135px;
                height: 135px;
                margin-bottom: 12px;
            }

            .auth-card h2 {
                font-size: 28px;
            }

            .wiz-login-footer {
                font-size: 12px;
                padding-top: 10px;
            }
        }
    </style>

    <section class="login-content wiz-login-page">
        <div class="row m-0 min-vh-100 bg-white">
            <div class="col-md-6 d-flex justify-content-center align-items-center wiz-login-left">
                <div class="wiz-login-form-wrap">
                    <div class="card card-transparent shadow-none border-0 mb-0 auth-card">
                        <div class="card-body text-center p-0">
                            <div class="text-decoration-none wiz-login-brand" aria-label="Wizmaster Corporation">
                                <img src="{{ asset('images/wizmaster-logo.png') }}"
                                     alt="Wizmaster Corporation"
                                     class="wiz-login-logo-static">

                                <h1 class="wiz-login-title">
                                    WIZMASTER CORPORATION
                                </h1>
                            </div>

                            <h2 class="mb-2">Sign In</h2>
                            <p class="text-muted mb-4">Login to stay connected.</p>

                            <x-auth-session-status class="mb-4" :status="session('status')" />
                            <x-auth-validation-errors class="mb-4" :errors="$errors" />

                            <form method="POST" action="{{ route('login') }}" class="text-start">
                                @csrf

                                <div class="form-group mb-3">
                                    <label class="form-label" for="email">Email</label>
                                    <input id="email"
                                           class="form-control"
                                           type="email"
                                           name="email"
                                           value="{{ old('email') }}"
                                           required
                                           autofocus
                                           autocomplete="username"
                                           placeholder="Email or Username">
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label" for="password">Password</label>
                                    <input id="password"
                                           class="form-control"
                                           type="password"
                                           name="password"
                                           required
                                           autocomplete="current-password"
                                           placeholder="Password">
                                </div>

                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="remember"
                                               id="remember_me">

                                        <label class="form-check-label" for="remember_me">
                                            Remember Me
                                        </label>
                                    </div>

                                    @if (Route::has('password.request'))
                                        <a class="text-primary text-decoration-none"
                                           href="{{ route('password.request') }}">
                                            Forgot Password?
                                        </a>
                                    @endif
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        Sign In
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="wiz-login-footer">
                        Copyright © 2026 onwards, Wizmaster Corporation.
                    </div>
                </div>
            </div>

            <div class="col-md-6 d-none d-md-flex p-0 wiz-login-right-panel"></div>
        </div>
    </section>
</x-guest-layout>