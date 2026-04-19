<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RPG API - Landing Page</title>
    <meta name="description" content="REST API untuk autentikasi, pengguna, pasien, jadwal, layanan, dan antrean.">
    <link rel="shortcut icon" type="image/png" href="/favicon.ico">
    <style {csp-style-nonce}>
        :root {
            --bg: #f3f7f8;
            --panel: #ffffff;
            --text: #15313a;
            --muted: #52717b;
            --brand: #0e8a86;
            --brand-dark: #0a5c66;
            --stroke: #d8e4e7;
            --ok: #086b5f;
            --warn: #9a4a0d;
            --shadow: 0 14px 40px rgba(12, 46, 55, 0.12);
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            background: radial-gradient(circle at 15% 15%, #d4efee 0, transparent 38%),
                        radial-gradient(circle at 88% 5%, #e6f2ff 0, transparent 32%),
                        var(--bg);
            color: var(--text);
            font-family: "Avenir Next", "Segoe UI", "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
        }

        .container {
            max-width: 1120px;
            margin: 0 auto;
            padding: 28px 18px 56px;
        }

        .hero {
            display: grid;
            gap: 26px;
            grid-template-columns: 1.1fr 0.9fr;
            align-items: stretch;
        }

        .hero-main,
        .hero-side,
        .panel {
            background: var(--panel);
            border: 1px solid var(--stroke);
            border-radius: 18px;
            box-shadow: var(--shadow);
        }

        .hero-main {
            padding: 34px 30px;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            background: #d5f2f1;
            color: var(--brand-dark);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        h1, h2, h3 {
            margin: 0 0 10px;
            line-height: 1.2;
        }

        h1 {
            font-size: clamp(1.7rem, 2.6vw, 2.6rem);
            margin-top: 14px;
        }

        p {
            margin: 0;
            color: var(--muted);
        }

        .hero-copy {
            margin-top: 14px;
            max-width: 62ch;
        }

        .actions {
            margin-top: 24px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            border-radius: 12px;
            padding: 10px 16px;
            font-weight: 700;
            border: 1px solid transparent;
        }

        .btn-primary {
            background: linear-gradient(120deg, var(--brand), var(--brand-dark));
            color: #fff;
        }

        .btn-ghost {
            border-color: var(--stroke);
            color: var(--brand-dark);
            background: #f7fbfc;
        }

        .hero-side {
            padding: 24px;
        }

        .kv {
            display: grid;
            grid-template-columns: 130px 1fr;
            gap: 8px 12px;
            margin: 0;
            font-size: 0.95rem;
        }

        .kv dt {
            color: var(--muted);
            font-weight: 700;
        }

        .kv dd {
            margin: 0;
            word-break: break-word;
            color: var(--text);
        }

        .stack {
            margin-top: 22px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .chip {
            background: #f0f8f8;
            border: 1px solid #cde5e4;
            border-radius: 999px;
            padding: 4px 11px;
            font-size: 12px;
            font-weight: 700;
            color: #29665f;
        }

        .section {
            margin-top: 24px;
        }

        .section-title {
            margin-bottom: 10px;
            font-size: 1.3rem;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .panel {
            padding: 18px;
        }

        .panel h3 {
            font-size: 1rem;
            margin-bottom: 6px;
        }

        .method {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 50px;
            border-radius: 7px;
            padding: 2px 8px;
            font-size: 12px;
            font-weight: 800;
            margin-right: 7px;
        }

        .post { background: #d3ecff; color: #0b4d8a; }
        .get { background: #d8f8df; color: #1b6b24; }
        .put { background: #ffeeca; color: #935e0d; }
        .delete { background: #ffd9db; color: #93252d; }

        code, pre {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        }

        pre {
            margin: 0;
            background: #0f2530;
            color: #dbf5ff;
            border-radius: 12px;
            padding: 14px;
            overflow: auto;
            font-size: 0.86rem;
            line-height: 1.45;
        }

        .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .status-ok {
            color: var(--ok);
            font-weight: 700;
        }

        .status-warn {
            color: var(--warn);
            font-weight: 700;
        }

        footer {
            margin-top: 30px;
            font-size: 0.9rem;
            color: var(--muted);
        }

        @media (max-width: 960px) {
            .hero {
                grid-template-columns: 1fr;
            }

            .grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 680px) {
            .grid,
            .two-col {
                grid-template-columns: 1fr;
            }

            .kv {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php $apiBase = rtrim(base_url('api/v1'), '/'); ?>
<main class="container">
    <section class="hero">
        <article class="hero-main">
            <span class="badge">RPG API v1</span>
            <h1>REST API untuk operasional layanan dan antrean</h1>
            <p class="hero-copy">
                API ini menyediakan autentikasi berbasis JWT, manajemen data pengguna, pasien, jadwal, jenis layanan,
                antrean, dan log antrean dengan format response JSON yang konsisten.
            </p>
            <div class="actions">
                <a class="btn btn-primary" href="#endpoints">Lihat Endpoint</a>
                <a class="btn btn-ghost" href="#quickstart">Quick Start</a>
            </div>
        </article>

        <aside class="hero-side">
            <h2>Ringkasan API</h2>
            <dl class="kv">
                <dt>Base URL</dt>
                <dd><code><?= esc($apiBase) ?></code></dd>
                <dt>Auth</dt>
                <dd><span class="status-ok">JWT</span> (`Authorization: Bearer ...`)</dd>
                <dt>Versioning</dt>
                <dd><code>/api/v1</code></dd>
                <dt>Format</dt>
                <dd>JSON (`status`, `message`, `data`, `errors`)</dd>
                <dt>Rate Limit</dt>
                <dd><span class="status-warn">Aktif di group API</span></dd>
            </dl>
            <div class="stack">
                <span class="chip">CodeIgniter 4</span>
                <span class="chip">PHP</span>
                <span class="chip">RESTful Resource</span>
                <span class="chip">JWT Filter</span>
            </div>
        </aside>
    </section>

    <section class="section" id="endpoints">
        <h2 class="section-title">Endpoint Utama</h2>
        <div class="grid">
            <article class="panel">
                <h3><span class="method post">POST</span>/auth/register</h3>
                <p>Registrasi akun pengguna baru.</p>
            </article>
            <article class="panel">
                <h3><span class="method post">POST</span>/auth/login</h3>
                <p>Login dan mendapatkan access token JWT.</p>
            </article>
            <article class="panel">
                <h3><span class="method get">GET</span>/users</h3>
                <p>Daftar user (admin only).</p>
            </article>
            <article class="panel">
                <h3><span class="method get">GET</span>/patients</h3>
                <p>Daftar pasien dan pencarian detail pasien.</p>
            </article>
            <article class="panel">
                <h3><span class="method get">GET</span>/schedules</h3>
                <p>Kelola jadwal layanan.</p>
            </article>
            <article class="panel">
                <h3><span class="method get">GET</span>/queues</h3>
                <p>Kelola antrean dan status antrean.</p>
            </article>
        </div>
    </section>

    <section class="section two-col" id="quickstart">
        <article class="panel">
            <h2 class="section-title">Contoh Request</h2>
            <pre><code>curl -X POST '<?= esc($apiBase) ?>/auth/login' \
  -H 'Content-Type: application/json' \
  -d '{
    "email": "admin@example.com",
    "password": "secret123"
  }'</code></pre>
        </article>
        <article class="panel">
            <h2 class="section-title">Format Response</h2>
            <pre><code>{
  "status": "success",
  "message": "Queue created successfully",
  "data": {
    "id": 1,
    "tanggal_kunjungan": "2026-04-19",
    "patient_id": 12,
    "status": "booked"
  },
  "errors": null
}</code></pre>
        </article>
    </section>

    <footer>
        Gunakan endpoint di atas sebagai titik awal integrasi frontend, mobile app, atau layanan internal.
    </footer>
</main>
</body>
</html>
