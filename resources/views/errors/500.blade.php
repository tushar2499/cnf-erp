<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 — Server Error — NAS Group ERP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0d2626 0%, #1a3d3d 60%, #0d2f3f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 1.5rem;
        }
        .err-wrap { text-align: center; max-width: 460px; width: 100%; }
        .brand { font-size: .8rem; font-weight: 700; color: #b2d8d8; margin-bottom: 2.5rem; letter-spacing: .01em; }
        .brand i { color: #64b5f6; margin-right: .35rem; }
        .err-code { font-size: 7rem; font-weight: 800; color: #f87171; line-height: 1; letter-spacing: -5px; }
        .err-icon { font-size: 2.2rem; color: #f87171; opacity: .65; margin: .75rem 0 .25rem; display: block; }
        .err-title { font-size: 1.25rem; font-weight: 700; color: #ffffff; margin: .4rem 0 .65rem; }
        .err-msg { font-size: .88rem; font-weight: 500; color: rgba(255,255,255,.72); line-height: 1.75; margin-bottom: 2rem; }
        .btn-red {
            display: inline-flex; align-items: center; justify-content: center; gap: .35rem;
            background: #ef4444; color: #fff; border: none;
            padding: .5rem 1.4rem; border-radius: .35rem;
            font-size: .78rem; font-weight: 600; font-family: 'Poppins', sans-serif;
            text-decoration: none; transition: background .15s; cursor: pointer; min-height: 44px;
            touch-action: manipulation;
        }
        .btn-red:hover { background: #dc2626; color: #fff; }
        .btn-ghost {
            display: inline-flex; align-items: center; justify-content: center; gap: .35rem;
            background: transparent; color: #8fbfbf; border: 1px solid rgba(255,255,255,.2);
            padding: .5rem 1.4rem; border-radius: .35rem;
            font-size: .78rem; font-weight: 500; font-family: 'Poppins', sans-serif;
            text-decoration: none; transition: all .15s; cursor: pointer; min-height: 44px;
            touch-action: manipulation;
        }
        .btn-ghost:hover { background: rgba(255,255,255,.08); color: #fff; border-color: rgba(255,255,255,.35); }
        .sep { border: none; border-top: 1px solid rgba(255,255,255,.07); margin: 2.25rem auto 1.5rem; max-width: 280px; }
        .footer-note { font-size: .68rem; color: rgba(178,216,216,.35); }
        .footer-note a { color: rgba(178,216,216,.45); text-decoration: none; }
        .footer-note a:hover { color: #b2d8d8; }
        @media (max-width: 576px) {
            body { padding: 1rem; }
            .err-wrap { max-width: 100%; }
            .err-code { font-size: 5.5rem; }
            .err-title { font-size: 1.05rem; }
            .err-msg { font-size: .85rem; }
            .d-flex { flex-direction: column; align-items: stretch; width: 100%; }
            .btn-ghost, .btn-red { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="err-wrap">
        <div class="brand">
            <i class="fa fa-layer-group" aria-hidden="true"></i> NAS Group ERP
        </div>
        <div class="err-code">500</div>
        <i class="fa fa-triangle-exclamation err-icon" aria-hidden="true"></i>
        <div class="err-title">Server Error</div>
        <p class="err-msg">
            Something went wrong on our end. The issue has been logged and we're on it.<br>
            Try again in a moment or contact your administrator.
        </p>
        <div class="d-flex gap-2 justify-content-center flex-wrap">
            <button type="button" onclick="location.reload()" class="btn-ghost">
                <i class="fa fa-rotate-right" aria-hidden="true"></i> Try Again
            </button>
            <a href="{{ url('/') }}" class="btn-red">
                <i class="fa fa-house" aria-hidden="true"></i> Home
            </a>
        </div>
        <hr class="sep">
        <div class="footer-note">
            Powered by <a href="https://a4bbd.com/" target="_blank" rel="noopener">Advertising For Business – A4B</a>
        </div>
    </div>
</body>
</html>
