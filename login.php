<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#082b45">
    <title>U-PARK | Admin Sign In</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            color-scheme: light;
            --navy: #082b45;
            --navy-deep: #051d30;
            --blue: #124b70;
            --gold: #f5b51b;
            --ink: #172b3a;
            --muted: #71808c;
            --line: #dce4e9;
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            margin: 0;
            padding: 24px;
            display: grid;
            place-items: center;
            color: var(--ink);
            background:
                radial-gradient(ellipse at 12% 10%, rgba(245, 181, 27, .11), transparent 32%),
                #f2f5f7;
            font-family: "Segoe UI", Arial, sans-serif;
        }

        .auth-shell {
            width: min(100%, 1040px);
            min-height: 620px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            overflow: hidden;
            background: #fff;
            border: 1px solid rgba(8, 43, 69, .08);
            border-radius: 22px;
            box-shadow: 0 24px 70px rgba(8, 43, 69, .13);
        }

        .brand-panel {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 620px;
            padding: 46px;
            overflow: hidden;
            color: #fff;
            background: linear-gradient(145deg, var(--blue), var(--navy-deep) 78%);
        }

        .brand-panel::before,
        .brand-panel::after {
            position: absolute;
            content: "";
            pointer-events: none;
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 50%;
        }

        .brand-panel::before {
            width: 410px;
            height: 410px;
            right: -195px;
            top: 105px;
        }

        .brand-panel::after {
            width: 300px;
            height: 300px;
            right: -140px;
            top: 160px;
        }

        .brand-lockup,
        .brand-copy,
        .brand-foot { position: relative; z-index: 1; }

        .brand-lockup {
            display: flex;
            align-items: center;
            gap: 13px;
        }

        .brand-mark {
            width: 46px;
            height: 46px;
            display: grid;
            place-items: center;
            color: var(--navy);
            background: var(--gold);
            border-radius: 13px;
            font-size: 21px;
        }

        .brand-name {
            margin: 0;
            font-size: 19px;
            font-weight: 800;
            letter-spacing: .12em;
        }

        .brand-university {
            margin: 3px 0 0;
            color: rgba(255, 255, 255, .68);
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .1em;
        }

        .brand-copy { max-width: 390px; margin: 48px 0; }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 19px;
            color: var(--gold);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .15em;
            text-transform: uppercase;
        }

        .brand-copy h1 {
            margin: 0 0 16px;
            font-size: clamp(34px, 4vw, 48px);
            font-weight: 750;
            letter-spacing: -.04em;
            line-height: 1.1;
        }

        .brand-copy p {
            max-width: 340px;
            margin: 0;
            color: rgba(255, 255, 255, .72);
            font-size: 15px;
            line-height: 1.75;
        }

        .parking-illustration {
            position: absolute;
            width: 245px;
            height: 190px;
            right: 19px;
            bottom: 72px;
            display: grid;
            place-items: center;
            color: rgba(255, 255, 255, .9);
        }

        .parking-illustration .parking-lines {
            position: absolute;
            inset: 18px 28px;
            border: 2px dashed rgba(255, 255, 255, .24);
            border-radius: 18px;
            transform: rotate(-12deg);
        }

        .parking-illustration .fa-car-side {
            position: relative;
            color: var(--gold);
            font-size: 92px;
            filter: drop-shadow(0 12px 15px rgba(0, 0, 0, .2));
            transform: rotate(-12deg);
        }

        .brand-foot {
            display: flex;
            align-items: center;
            gap: 9px;
            color: rgba(255, 255, 255, .58);
            font-size: 11px;
        }

        .brand-foot i { color: var(--gold); }

        .form-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 56px clamp(30px, 6vw, 72px);
        }

        .login-form { width: 100%; max-width: 360px; }

        .mobile-brand { display: none; }

        .form-heading { margin-bottom: 32px; }

        .form-heading .eyebrow {
            margin-bottom: 12px;
            color: #a57500;
        }

        .form-heading h2 {
            margin: 0 0 9px;
            color: var(--navy);
            font-size: 30px;
            font-weight: 750;
            letter-spacing: -.035em;
        }

        .form-heading p { margin: 0; color: var(--muted); font-size: 14px; }

        .form-label {
            margin-bottom: 8px;
            color: #304453;
            font-size: 12px;
            font-weight: 750;
            letter-spacing: .04em;
        }

        .input-wrap { position: relative; }

        .input-wrap > .input-icon {
            position: absolute;
            top: 50%;
            left: 15px;
            z-index: 2;
            color: #84939d;
            transform: translateY(-50%);
        }

        .form-control {
            min-height: 51px;
            padding: 12px 14px 12px 43px;
            color: var(--ink);
            border: 1px solid var(--line);
            border-radius: 10px;
            font-size: 14px;
            transition: border-color .18s ease, box-shadow .18s ease;
        }

        .form-control::placeholder { color: #a4afb6; }

        .form-control:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 4px rgba(18, 75, 112, .11);
        }

        .password-input { padding-right: 48px; }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 8px;
            width: 36px;
            height: 36px;
            display: grid;
            place-items: center;
            color: #71808c;
            background: transparent;
            border: 0;
            border-radius: 8px;
            transform: translateY(-50%);
        }

        .password-toggle:hover { color: var(--navy); background: #f1f5f7; }
        .password-toggle:focus-visible { outline: 2px solid var(--blue); outline-offset: 2px; }

        .btn-signin {
            min-height: 51px;
            color: #fff;
            background: var(--navy);
            border: 0;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 750;
            transition: background-color .18s ease, transform .18s ease;
        }

        .btn-signin:hover,
        .btn-signin:focus-visible {
            color: var(--gold);
            background: #104568;
            transform: translateY(-1px);
        }

        .form-links {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px 12px;
            padding-top: 22px;
            border-top: 1px solid #edf0f2;
        }

        .form-links a {
            color: #5e6e7a;
            font-size: 12px;
            text-decoration: none;
        }

        .form-links a:hover { color: var(--blue); text-decoration: underline; }

        .alert { border-radius: 10px; font-size: 13px; }

        @media (max-width: 760px) {
            body { padding: 16px; }

            .auth-shell {
                width: min(100%, 520px);
                min-height: auto;
                grid-template-columns: 1fr;
                border-radius: 18px;
            }

            .brand-panel { display: none; }

            .form-panel { padding: 38px clamp(24px, 8vw, 48px); }

            .mobile-brand {
                display: flex;
                align-items: center;
                gap: 11px;
                margin-bottom: 34px;
            }

            .mobile-brand .brand-mark { width: 40px; height: 40px; font-size: 18px; }
            .mobile-brand .brand-name { color: var(--navy); font-size: 16px; }
            .mobile-brand .brand-university { color: var(--muted); font-size: 8px; }
        }

        @media (max-width: 380px) {
            body { padding: 10px; }
            .form-panel { padding: 30px 20px; }
            .form-heading h2 { font-size: 27px; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>
    <main class="auth-shell" aria-label="U-PARK administrator sign in">
        <section class="brand-panel" aria-label="U-PARK">
            <div class="brand-lockup">
                <span class="brand-mark" aria-hidden="true"><i class="fa-solid fa-square-parking"></i></span>
                <div>
                    <p class="brand-name">U-PARK</p>
                    <p class="brand-university">UNIVERSITY OF NEGROS OCCIDENTAL – RECOLETOS</p>
                </div>
            </div>

            <div class="brand-copy">
                <span class="eyebrow"><i class="fa-solid fa-circle"></i> CAMPUS PARKING</span>
                <h1>Campus parking, made simple.</h1>
                <p>Sign in to manage parking availability, reservations, and campus events from one place.</p>
            </div>

            <div class="parking-illustration" aria-hidden="true">
                <span class="parking-lines"></span>
                <i class="fa-solid fa-car-side"></i>
            </div>

            <div class="brand-foot">
                <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                <span>Authorized administrators only</span>
            </div>
        </section>

        <section class="form-panel">
            <div class="login-form">
                <div class="mobile-brand" aria-label="U-PARK">
                    <span class="brand-mark" aria-hidden="true"><i class="fa-solid fa-square-parking"></i></span>
                    <div>
                        <p class="brand-name">U-PARK</p>
                        <p class="brand-university">UNO-R CAMPUS PARKING</p>
                    </div>
                </div>

                <header class="form-heading">
                    <span class="eyebrow">ADMIN PORTAL</span>
                    <h2>Welcome back</h2>
                    <p>Enter your admin credentials to continue.</p>
                </header>

                <?php if (isset($_GET['msg']) && $_GET['msg'] === 'registered'): ?>
                    <div class="alert alert-success d-flex align-items-center gap-2 mb-4" role="status">
                        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                        <span>Admin registered successfully. You may now sign in.</span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" role="alert">
                        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                        <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php?action=login">
                    <div class="mb-3">
                        <label class="form-label" for="username">Username</label>
                        <div class="input-wrap">
                            <i class="fa-regular fa-user input-icon" aria-hidden="true"></i>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                class="form-control"
                                required
                                autocomplete="username"
                                autofocus
                                placeholder="Enter your username"
                            >
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-lock input-icon" aria-hidden="true"></i>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control password-input"
                                required
                                autocomplete="current-password"
                                placeholder="Enter your password"
                            >
                            <button
                                class="password-toggle"
                                type="button"
                                aria-label="Show password"
                                aria-pressed="false"
                                data-password-toggle
                            >
                                <i class="fa-regular fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-signin w-100 mb-4">
                        Sign in <i class="fa-solid fa-arrow-right ms-2" aria-hidden="true"></i>
                    </button>

                    <nav class="form-links" aria-label="Other pages">
                        <a href="index.php?action=register">Create an admin account</a>
                        <span class="text-muted" aria-hidden="true">·</span>
                        <a href="index.php?action=user_portal">Back to public view</a>
                    </nav>
                </form>
            </div>
        </section>
    </main>

    <script>
        const passwordToggle = document.querySelector('[data-password-toggle]');
        const passwordInput = document.querySelector('#password');

        passwordToggle.addEventListener('click', () => {
            const isVisible = passwordInput.type === 'text';
            passwordInput.type = isVisible ? 'password' : 'text';
            passwordToggle.setAttribute('aria-pressed', String(!isVisible));
            passwordToggle.setAttribute('aria-label', isVisible ? 'Show password' : 'Hide password');
            passwordToggle.innerHTML = `<i class="fa-regular ${isVisible ? 'fa-eye' : 'fa-eye-slash'}" aria-hidden="true"></i>`;
        });
    </script>
</body>
</html>
