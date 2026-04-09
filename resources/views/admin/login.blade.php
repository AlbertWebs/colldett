<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign in — Admin | {{ config('colldett.company.name', 'Colldett') }}</title>
    <meta name="theme-color" content="#0f172a">
    @vite(['resources/css/admin.css'])
</head>
<body class="admin-login-page">
    <div class="admin-login-backdrop" aria-hidden="true"></div>

    <div class="admin-login-inner">
        <main class="admin-login-main" id="main-content">
            <div class="admin-login-card">
                <div class="admin-login-card__accent" aria-hidden="true"></div>

                <div class="admin-login-card__body">
                    <div class="admin-login-brand">
                        <div class="admin-login-brand__icon" aria-hidden="true">
                            <svg class="admin-login-brand__svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="5" y="11" width="14" height="10" rx="2" />
                                <path d="M8 11V7a4 4 0 0 1 8 0v4" />
                            </svg>
                        </div>
                        <div class="admin-login-brand__text">
                            <p class="admin-login-brand__eyebrow">{{ config('colldett.company.name', 'Colldett') }}</p>
                            <h1 class="admin-login-brand__title">Admin sign in</h1>
                            <p class="admin-login-brand__lead">Enter the password or PIN configured for this panel.</p>
                        </div>
                    </div>

                    @if($errors->any())
                        <div class="admin-login-alert" role="alert">
                            <span class="admin-login-alert__icon" aria-hidden="true">!</span>
                            <p class="admin-login-alert__msg">{{ $errors->first() }}</p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.login.attempt') }}" class="admin-login-form" novalidate>
                        @csrf
                        <div class="admin-login-field">
                            <label for="access_code" class="admin-login-label">Password or PIN</label>
                            <div class="admin-login-input-wrap">
                                <input
                                    id="access_code"
                                    name="access_code"
                                    type="password"
                                    inputmode="text"
                                    autocomplete="current-password"
                                    required
                                    autofocus
                                    class="admin-login-input"
                                    placeholder="Enter access code"
                                    aria-describedby="access-code-hint"
                                />
                                <button type="button" class="admin-login-reveal" id="admin-login-reveal" aria-controls="access_code" aria-pressed="false" tabindex="0">
                                    <span class="admin-login-reveal__show">Show</span>
                                    <span class="admin-login-reveal__hide" hidden>Hide</span>
                                </button>
                            </div>
                            <p id="access-code-hint" class="admin-login-hint">Use the access password or PIN defined in your site’s environment configuration.</p>
                        </div>
                        <button type="submit" class="admin-login-submit">
                            <span>Continue</span>
                            <span class="admin-login-submit__arrow" aria-hidden="true">→</span>
                        </button>
                    </form>

                    <p class="admin-login-meta">Session-based access · not linked to staff user accounts</p>

                    <a href="{{ route('home') }}" class="admin-login-backlink">
                        <span class="admin-login-backlink__icon" aria-hidden="true">←</span>
                        Back to website
                    </a>
                </div>
            </div>
        </main>
    </div>

    <script>
        (function () {
            var input = document.getElementById('access_code');
            var btn = document.getElementById('admin-login-reveal');
            if (!input || !btn) return;
            var showEl = btn.querySelector('.admin-login-reveal__show');
            var hideEl = btn.querySelector('.admin-login-reveal__hide');
            btn.addEventListener('click', function () {
                var isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';
                btn.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
                btn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
                if (showEl) showEl.hidden = isHidden;
                if (hideEl) hideEl.hidden = !isHidden;
            });
            btn.setAttribute('aria-label', 'Show password');
        })();
    </script>
</body>
</html>
