<?php

declare(strict_types=1);

$bgSeed = random_int(1000, 999999);

$gradA1 = random_int(1, 6);
$gradA2 = random_int(12, 20);
$gradB1 = random_int(42, 52);
$gradB2 = random_int(98, 100);

$driftX = random_int(12, 24);
$driftY = random_int(-18, -10);

/* nur äußere Zonen */
$bgLeftX = random_int(8, 18);
$bgLeftY = random_int(12, 26);
$bgLeftSize = random_int(24, 32);
$bgLeftAlpha = random_int(34, 50) / 100;

$bgRightX = random_int(82, 92);
$bgRightY = random_int(10, 22);
$bgRightSize = random_int(24, 34);
$bgRightAlpha = random_int(28, 42) / 100;

$bgBottomLeftX = random_int(10, 22);
$bgBottomLeftY = random_int(78, 90);
$bgBottomLeftSize = random_int(22, 30);
$bgBottomLeftAlpha = random_int(20, 32) / 100;

$bgBottomRightX = random_int(78, 90);
$bgBottomRightY = random_int(78, 90);
$bgBottomRightSize = random_int(22, 30);
$bgBottomRightAlpha = random_int(20, 30) / 100;

$orb1Width = random_int(280, 360);
$orb1Height = random_int(280, 360);
$orb1Top = random_int(2, 8);
$orb1Left = random_int(2, 7);
$orb1Duration = random_int(12, 16);
$orb1CoreAlpha = random_int(38, 56) / 100;

$orb2Width = random_int(300, 390);
$orb2Height = random_int(300, 390);
$orb2Right = random_int(2, 7);
$orb2Bottom = random_int(2, 7);
$orb2Duration = random_int(14, 18);
$orb2CoreAlpha = random_int(34, 48) / 100;

$orb3Width = random_int(170, 230);
$orb3Height = random_int(170, 230);
$orb3Right = random_int(8, 14);
$orb3Top = random_int(8, 14);
$orb3Duration = random_int(10, 14);
$orb3CoreAlpha = random_int(24, 36) / 100;

$orb4Width = random_int(200, 260);
$orb4Height = random_int(200, 260);
$orb4Left = random_int(2, 8);
$orb4Bottom = random_int(2, 8);
$orb4Duration = random_int(16, 20);
$orb4CoreAlpha = random_int(20, 30) / 100;
?>
<style>
    body {
        overflow: hidden;
    }

    .login-shell {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #020617 <?= $gradA1 ?>%, #0f172a <?= $gradA2 ?>%, #111827 <?= $gradB1 ?>%, #030712 <?= $gradB2 ?>%);
    }

    .login-bg {
        position: absolute;
        inset: -8%;
        z-index: 0;
        pointer-events: none;
        will-change: transform;
        transition: transform 0.16s linear;
    }

    .login-bg::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at <?= $bgLeftX ?>% <?= $bgLeftY ?>%, rgba(59, 130, 246, <?= $bgLeftAlpha ?>) 0%, transparent <?= $bgLeftSize ?>%),
            radial-gradient(circle at <?= $bgRightX ?>% <?= $bgRightY ?>%, rgba(168, 85, 247, <?= $bgRightAlpha ?>) 0%, transparent <?= $bgRightSize ?>%),
            radial-gradient(circle at <?= $bgBottomLeftX ?>% <?= $bgBottomLeftY ?>%, rgba(14, 165, 233, <?= $bgBottomLeftAlpha ?>) 0%, transparent <?= $bgBottomLeftSize ?>%),
            radial-gradient(circle at <?= $bgBottomRightX ?>% <?= $bgBottomRightY ?>%, rgba(99, 102, 241, <?= $bgBottomRightAlpha ?>) 0%, transparent <?= $bgBottomRightSize ?>%);
        filter: blur(8px);
        animation: bgDrift 18s ease-in-out infinite alternate;
    }

    .login-overlay {
        position: absolute;
        inset: 0;
        z-index: 0;
        pointer-events: none;
        background:
            radial-gradient(circle at 50% 50%, rgba(2, 6, 23, 0.34) 0%, rgba(2, 6, 23, 0.24) 18%, rgba(2, 6, 23, 0.00) 42%),
            linear-gradient(180deg, rgba(2, 6, 23, 0.12) 0%, rgba(2, 6, 23, 0.42) 100%);
    }

    .login-grid {
        position: absolute;
        inset: 0;
        z-index: 0;
        pointer-events: none;
        opacity: 0.08;
        background-image:
            linear-gradient(rgba(255, 255, 255, 0.10) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.10) 1px, transparent 1px);
        background-size: 32px 32px;
        mask-image: radial-gradient(circle at center, black 35%, transparent 85%);
        -webkit-mask-image: radial-gradient(circle at center, black 35%, transparent 85%);
        will-change: transform;
        transition: transform 0.14s linear;
    }

    .login-orb-layer {
        position: absolute;
        inset: 0;
        z-index: 0;
        pointer-events: none;
        will-change: transform;
        transition: transform 0.14s linear;
    }

    .login-orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(10px);
        mix-blend-mode: screen;
        opacity: 1;
        will-change: transform;
    }

    .login-orb-1 {
        width: <?= $orb1Width ?>px;
        height: <?= $orb1Height ?>px;
        top: <?= $orb1Top ?>%;
        left: <?= $orb1Left ?>%;
        background: radial-gradient(circle, rgba(59, 130, 246, <?= $orb1CoreAlpha ?>) 0%, rgba(59, 130, 246, 0.16) 38%, rgba(59, 130, 246, 0) 72%);
        animation: floatOrb1 <?= $orb1Duration ?>s ease-in-out infinite alternate;
    }

    .login-orb-2 {
        width: <?= $orb2Width ?>px;
        height: <?= $orb2Height ?>px;
        right: <?= $orb2Right ?>%;
        bottom: <?= $orb2Bottom ?>%;
        background: radial-gradient(circle, rgba(168, 85, 247, <?= $orb2CoreAlpha ?>) 0%, rgba(168, 85, 247, 0.16) 40%, rgba(168, 85, 247, 0) 74%);
        animation: floatOrb2 <?= $orb2Duration ?>s ease-in-out infinite alternate;
    }

    .login-orb-3 {
        width: <?= $orb3Width ?>px;
        height: <?= $orb3Height ?>px;
        right: <?= $orb3Right ?>%;
        top: <?= $orb3Top ?>%;
        background: radial-gradient(circle, rgba(34, 211, 238, <?= $orb3CoreAlpha ?>) 0%, rgba(34, 211, 238, 0.12) 40%, rgba(34, 211, 238, 0) 74%);
        animation: floatOrb3 <?= $orb3Duration ?>s ease-in-out infinite alternate;
    }

    .login-orb-4 {
        width: <?= $orb4Width ?>px;
        height: <?= $orb4Height ?>px;
        left: <?= $orb4Left ?>%;
        bottom: <?= $orb4Bottom ?>%;
        background: radial-gradient(circle, rgba(99, 102, 241, <?= $orb4CoreAlpha ?>) 0%, rgba(99, 102, 241, 0.12) 42%, rgba(99, 102, 241, 0) 76%);
        animation: floatOrb4 <?= $orb4Duration ?>s ease-in-out infinite alternate;
    }

    .cursor-stamps {
        position: absolute;
        inset: 0;
        z-index: 1;
        pointer-events: none;
        overflow: hidden;
    }

    .cursor-stamp {
        position: absolute;
        left: 0;
        top: 0;
        width: 42px;
        height: 42px;
        margin-left: -21px;
        margin-top: -21px;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.52);
        box-shadow:
            0 0 24px rgba(96, 165, 250, 0.18),
            0 0 12px rgba(255, 255, 255, 0.10),
            inset 0 0 16px rgba(255, 255, 255, 0.05);
        background: radial-gradient(circle, rgba(255,255,255,0.07) 0%, rgba(255,255,255,0.00) 68%);
        opacity: 0;
        transform: translate(-9999px, -9999px) scale(0.70);
        transform-origin: center center;
        will-change: transform, opacity;
        mix-blend-mode: screen;
    }

    .login-card {
        position: relative;
        z-index: 2;
        width: 100%;
        max-width: 470px;
        padding: 2rem;
        border-radius: 1.4rem;
        border: 1px solid rgba(255, 255, 255, 0.14);
        background: rgba(15, 23, 42, 0.74);
        box-shadow:
            0 24px 60px rgba(0, 0, 0, 0.52),
            0 0 0 1px rgba(255, 255, 255, 0.03) inset,
            inset 0 1px 0 rgba(255, 255, 255, 0.06);
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
        will-change: transform;
        transition: transform 0.14s linear, box-shadow 0.18s ease;
        transform: perspective(1000px) rotateX(0deg) rotateY(0deg);
    }

    .login-card::before {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: inherit;
        padding: 1px;
        background: linear-gradient(135deg, rgba(96, 165, 250, 0.22), rgba(255, 255, 255, 0.04), rgba(168, 85, 247, 0.18));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none;
    }

    .login-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        padding: 0.45rem 0.85rem;
        border-radius: 999px;
        background: rgba(59, 130, 246, 0.16);
        border: 1px solid rgba(96, 165, 250, 0.35);
        color: #dbeafe;
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .login-title {
        color: #ffffff;
        font-size: 2rem;
        font-weight: 800;
        line-height: 1.15;
        text-align: center;
        margin-bottom: 0.65rem;
        text-shadow: 0 2px 18px rgba(0, 0, 0, 0.28);
    }

    .login-subtitle {
        color: rgba(226, 232, 240, 0.92);
        font-size: 0.98rem;
        line-height: 1.6;
        text-align: center;
        margin-bottom: 1.6rem;
    }

    .login-card .form-label {
        color: #e2e8f0;
        font-weight: 600;
        margin-bottom: 0.45rem;
    }

    .login-card .form-control {
        min-height: 52px;
        border-radius: 0.9rem;
        border: 1px solid rgba(148, 163, 184, 0.28);
        background: rgba(255, 255, 255, 0.10);
        color: #ffffff;
        padding-left: 0.95rem;
        padding-right: 0.95rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    .login-card .form-control::placeholder {
        color: rgba(226, 232, 240, 0.68);
    }

    .login-card .form-control:focus {
        background: rgba(255, 255, 255, 0.14);
        color: #ffffff;
        border-color: rgba(96, 165, 250, 0.85);
        box-shadow: 0 0 0 0.22rem rgba(59, 130, 246, 0.18);
    }

    .login-card .form-check {
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .login-card .form-check-input {
        margin-top: 0;
        background-color: rgba(255, 255, 255, 0.14);
        border-color: rgba(148, 163, 184, 0.45);
    }

    .login-card .form-check-input:checked {
        background-color: #2563eb;
        border-color: #2563eb;
    }

    .login-card .form-check-label {
        color: #f8fafc;
        font-weight: 500;
    }

    .login-btn {
        min-height: 52px;
        border: 0;
        border-radius: 0.95rem;
        font-weight: 700;
        font-size: 1rem;
        box-shadow: 0 12px 30px rgba(37, 99, 235, 0.28);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .login-btn:hover,
    .login-btn:focus {
        transform: translateY(-1px);
        box-shadow: 0 16px 34px rgba(37, 99, 235, 0.34);
    }

    .login-card .alert {
        border-radius: 0.9rem;
    }

    @keyframes bgDrift {
        0% {
            transform: scale(1) translate(0, 0);
        }
        100% {
            transform: scale(1.05) translate(<?= $driftX ?>px, <?= $driftY ?>px);
        }
    }

    @keyframes floatOrb1 {
        0% {
            transform: translate(0, 0) scale(1);
        }
        100% {
            transform: translate(45px, 30px) scale(1.12);
        }
    }

    @keyframes floatOrb2 {
        0% {
            transform: translate(0, 0) scale(1);
        }
        100% {
            transform: translate(-35px, -28px) scale(1.1);
        }
    }

    @keyframes floatOrb3 {
        0% {
            transform: translate(0, 0) scale(1);
        }
        100% {
            transform: translate(-20px, 38px) scale(1.08);
        }
    }

    @keyframes floatOrb4 {
        0% {
            transform: translate(0, 0) scale(1);
        }
        100% {
            transform: translate(30px, -22px) scale(1.14);
        }
    }

    @media (max-width: 575.98px) {
        body {
            overflow: auto;
        }

        .login-shell {
            padding: 1rem;
        }

        .login-card {
            padding: 1.25rem;
            border-radius: 1.1rem;
            transform: none !important;
        }

        .login-title {
            font-size: 1.65rem;
        }

        .login-subtitle {
            font-size: 0.93rem;
        }

        .login-bg,
        .login-grid,
        .login-orb-layer,
        .cursor-stamps {
            transform: none !important;
            display: none;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .login-bg::before,
        .login-orb,
        .login-bg,
        .login-grid,
        .login-orb-layer,
        .login-card,
        .cursor-stamp {
            animation: none !important;
            transition: none !important;
            transform: none !important;
        }
    }
</style>

<main class="login-shell" id="loginShell" data-bg-seed="<?= $bgSeed ?>">
    <div class="login-bg" id="loginBg"></div>
    <div class="login-overlay"></div>
    <div class="login-grid" id="loginGrid"></div>

    <div class="login-orb-layer" id="loginOrbLayer">
        <div class="login-orb login-orb-1"></div>
        <div class="login-orb login-orb-2"></div>
        <div class="login-orb login-orb-3"></div>
        <div class="login-orb login-orb-4"></div>
    </div>

    <div class="cursor-stamps" id="cursorStamps">
        <div class="cursor-stamp"></div>
        <div class="cursor-stamp"></div>
        <div class="cursor-stamp"></div>
        <div class="cursor-stamp"></div>
        <div class="cursor-stamp"></div>
        <div class="cursor-stamp"></div>
        <div class="cursor-stamp"></div>
        <div class="cursor-stamp"></div>
    </div>

    <div class="login-card" id="loginCard">
        <div class="login-badge">Admin Bereich</div>

        <h1 class="login-title">Benutzerlogin</h1>

        <div class="login-subtitle">
            Bitte melde dich mit deinem Login und Passwort an, um das Dashboard zu öffnen.
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= e((string)$error) ?></div>
        <?php endif; ?>

        <form method="post" action="index.php?page=login">
            <div class="mb-3">
                <label for="login" class="form-label">Login</label>
                <input
                    type="text"
                    id="login"
                    name="login"
                    class="form-control"
                    placeholder="Login oder Benutzername"
                    required
                    autocomplete="username"
                >
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Passwort</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    placeholder="Passwort eingeben"
                    required
                    autocomplete="current-password"
                >
            </div>

            <div class="form-check form-switch mb-4">
                <input
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    id="remember"
                    name="remember"
                    value="1"
                >
                <label class="form-check-label" for="remember">Angemeldet bleiben</label>
            </div>

            <button type="submit" class="btn btn-primary w-100 login-btn">Login</button>
        </form>
    </div>
</main>

<script>
    (function () {
        const shell = document.getElementById('loginShell');
        const bg = document.getElementById('loginBg');
        const grid = document.getElementById('loginGrid');
        const orbLayer = document.getElementById('loginOrbLayer');
        const card = document.getElementById('loginCard');
        const stamps = Array.from(document.querySelectorAll('.cursor-stamp'));

        if (!shell || !bg || !grid || !orbLayer || !card || !stamps.length) {
            return;
        }

        const isTouchDevice = window.matchMedia('(pointer: coarse)').matches;
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (isTouchDevice || prefersReducedMotion) {
            return;
        }

        let currentPX = 0;
        let currentPY = 0;
        let targetPX = 0;
        let targetPY = 0;

        let stampIndex = 0;
        let lastStampX = -9999;
        let lastStampY = -9999;
        let lastStampTime = 0;

        const stampState = stamps.map(function () {
            return {
                x: -9999,
                y: -9999,
                born: 0,
                active: false
            };
        });

        function placeStamp(x, y) {
            const now = performance.now();
            const state = stampState[stampIndex];

            state.x = x;
            state.y = y;
            state.born = now;
            state.active = true;

            stampIndex = (stampIndex + 1) % stamps.length;
            lastStampX = x;
            lastStampY = y;
            lastStampTime = now;
        }

        function animate() {
            const now = performance.now();

            currentPX += (targetPX - currentPX) * 0.08;
            currentPY += (targetPY - currentPY) * 0.08;

            bg.style.transform = 'translate(' + (currentPX * 1.6).toFixed(2) + 'px, ' + (currentPY * 1.6).toFixed(2) + 'px) scale(1.03)';
            grid.style.transform = 'translate(' + (currentPX * 1.0).toFixed(2) + 'px, ' + (currentPY * 1.0).toFixed(2) + 'px)';
            orbLayer.style.transform = 'translate(' + (currentPX * 2.4).toFixed(2) + 'px, ' + (currentPY * 2.4).toFixed(2) + 'px)';

            card.style.transform =
                'perspective(1000px) ' +
                'rotateX(' + (currentPY * -0.16).toFixed(2) + 'deg) ' +
                'rotateY(' + (currentPX * 0.16).toFixed(2) + 'deg) ' +
                'translate(' + (currentPX * 0.35).toFixed(2) + 'px, ' + (currentPY * 0.35).toFixed(2) + 'px)';

            for (let i = 0; i < stamps.length; i++) {
                const state = stampState[i];
                const el = stamps[i];

                if (!state.active) {
                    el.style.opacity = '0';
                    continue;
                }

                const age = now - state.born;
                const life = 1200;

                if (age >= life) {
                    state.active = false;
                    el.style.opacity = '0';
                    continue;
                }

                const progress = age / life;
                const scale = 0.80 + (progress * 2.15);
                const opacity = 0.62 * (1 - progress);
                const borderOpacity = 0.58 * (1 - progress);

                el.style.transform =
                    'translate(' + state.x.toFixed(2) + 'px, ' + state.y.toFixed(2) + 'px) ' +
                    'scale(' + scale.toFixed(2) + ')';

                el.style.opacity = opacity.toFixed(3);
                el.style.borderColor = 'rgba(255,255,255,' + borderOpacity.toFixed(3) + ')';
            }

            window.requestAnimationFrame(animate);
        }

        shell.addEventListener('mousemove', function (event) {
            const rect = shell.getBoundingClientRect();
            const centerX = rect.left + (rect.width / 2);
            const centerY = rect.top + (rect.height / 2);

            const x = event.clientX;
            const y = event.clientY;

            const percentX = (x - centerX) / (rect.width / 2);
            const percentY = (y - centerY) / (rect.height / 2);

            targetPX = Math.max(-18, Math.min(18, percentX * 18));
            targetPY = Math.max(-18, Math.min(18, percentY * 18));

            const dx = x - lastStampX;
            const dy = y - lastStampY;
            const distance = Math.sqrt((dx * dx) + (dy * dy));
            const now = performance.now();

            if (distance >= 44 || now - lastStampTime >= 120) {
                placeStamp(x, y);
            }
        });

        shell.addEventListener('mouseleave', function () {
            targetPX = 0;
            targetPY = 0;
        });

        animate();
    })();
</script>