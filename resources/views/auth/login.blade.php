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
            padding: 32px 24px 18px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            position: relative;
        }

        .wiz-login-brand {
            color: inherit;
        }

        .wiz-login-logo-static {
            width: 210px;
            height: 210px;
            object-fit: contain;
            margin-bottom: 18px;
        }

        .wiz-login-title {
            font-size: 38px;
            font-weight: 700;
            color: #061a44;
            letter-spacing: 1.5px;
            line-height: 1.1;
            margin-bottom: 30px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .auth-card {
            width: 100%;
            margin-top: auto;
            margin-bottom: auto;
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
            flex-shrink: 0;
            text-align: center;
            font-size: 14px;
            color: #64748b;
            line-height: 1.5;
            padding-top: 12px;
        }

        @media (max-width: 1199.98px) {
            .wiz-login-form-wrap {
                max-width: 620px;
            }

            .wiz-login-title {
                font-size: 38px;
            }

            .wiz-login-logo-static {
                width: 175px;
                height: 175px;
            }
        }


        @media (max-height: 760px) and (min-width: 768px) {
            .wiz-login-form-wrap {
                padding-top: 18px;
                padding-bottom: 12px;
            }

            .wiz-login-logo-static {
                width: 135px;
                height: 135px;
                margin-bottom: 10px;
            }

            .wiz-login-title {
                font-size: 30px;
                margin-bottom: 18px;
            }

            .auth-card h2 {
                font-size: 26px;
            }

            .auth-card .text-muted.mb-4 {
                margin-bottom: 1rem !important;
            }

            .auth-card .form-control {
                min-height: 42px;
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
                padding: 36px 26px 18px;
            }

            .wiz-login-title {
                font-size: 24px;
                white-space: normal;
            }

            .wiz-login-logo-static {
                width: 135px;
                height: 135px;
                margin-bottom: 14px;
            }

            .auth-card h2 {
                font-size: 28px;
            }

            .wiz-login-footer {
                bottom: 14px;
                font-size: 12px;
            }
        }
    </style>

    <section class="login-content wiz-login-page">
        <div class="row m-0 min-vh-100 bg-white">
            <div class="col-md-6 d-flex justify-content-center align-items-center wiz-login-left">
                <div class="wiz-login-form-wrap">
                    <div class="card card-transparent shadow-none border-0 mb-0 auth-card">
                        <div class="card-body text-center p-0">
                            <a href="{{ url('/') }}"
                               class="text-decoration-none d-inline-flex flex-column align-items-center justify-content-center mb-3 wiz-login-brand">
                                <img src="{{ asset('images/wizmaster-logo.png') }}"
                                     alt="Wizmaster Corporation"
                                     class="wiz-login-logo-static">

                                <h1 class="wiz-login-title">
                                    WIZMASTER CORPORATION
                                </h1>
                            </a>

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