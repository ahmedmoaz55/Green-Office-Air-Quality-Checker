<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OxyGuard | Secure Uplink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Tactical Grid Background */
        body { 
            background-color: #020603; 
            background-image: 
                linear-gradient(rgba(0, 230, 118, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 230, 118, 0.05) 1px, transparent 1px);
            background-size: 40px 40px;
            color: #00e676; 
            font-family: 'Courier New', Courier, monospace; /* Terminal Font */
            height: 100vh;
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
        }

        /* Animated Scanline */
        .scanline {
            position: fixed; top: 0; left: 0; width: 100vw; height: 15vh;
            background: linear-gradient(to bottom, transparent, rgba(0, 230, 118, 0.15), transparent);
            animation: scan 5s linear infinite;
            pointer-events: none; z-index: 9999;
        }
        @keyframes scan { 0% { transform: translateY(-100vh); } 100% { transform: translateY(100vh); } }

        /* Cyberpunk Clipped Card */
        .cyber-card {
            background: rgba(5, 15, 8, 0.85);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 230, 118, 0.4);
            width: 100%; max-width: 450px;
            padding: 3rem;
            position: relative;
            /* Cuts the bottom right corner off */
            clip-path: polygon(0 0, 100% 0, 100% calc(100% - 30px), calc(100% - 30px) 100%, 0 100%);
            box-shadow: 0 0 30px rgba(0, 230, 118, 0.1), inset 0 0 20px rgba(0, 230, 118, 0.05);
        }

        /* Decorative Corner Brackets */
        .cyber-card::before {
            content: ''; position: absolute; top: -2px; left: -2px; width: 20px; height: 20px;
            border-top: 2px solid #00e676; border-left: 2px solid #00e676;
        }
        .cyber-card::after {
            content: ''; position: absolute; bottom: -2px; left: -2px; width: 20px; height: 20px;
            border-bottom: 2px solid #00e676; border-left: 2px solid #00e676;
        }

        /* Tactical Inputs */
        .cyber-input {
            background-color: rgba(0, 230, 118, 0.03) !important;
            border: 1px solid rgba(0, 230, 118, 0.2) !important;
            color: #00e676 !important;
            border-radius: 0;
            padding: 0.8rem 1rem;
            letter-spacing: 1px;
            transition: all 0.3s;
        }
        .cyber-input:focus {
            border-color: #00e676 !important;
            box-shadow: 0 0 15px rgba(0, 230, 118, 0.3) !important;
            background-color: rgba(0, 230, 118, 0.08) !important;
            outline: none;
        }
        .cyber-input::placeholder { color: rgba(0, 230, 118, 0.3); }

        /* Neon Button */
        .btn-neon { 
            background-color: rgba(0, 230, 118, 0.1); 
            border: 1px solid #00e676; 
            color: #00e676; 
            font-weight: bold; letter-spacing: 3px; padding: 1rem;
            text-transform: uppercase; transition: all 0.2s ease;
        }
        .btn-neon:hover { 
            background-color: #00e676; color: #000; 
            box-shadow: 0 0 25px rgba(0, 230, 118, 0.6);
        }

        /* Blinking Cursor Effect */
        .blink { animation: blinker 1s linear infinite; }
        @keyframes blinker { 50% { opacity: 0; } }
    </style>
</head>
<body>

<div class="scanline"></div>

<div class="cyber-card">
    <div class="text-center mb-5">
        <p class="mb-1 small" style="color: rgba(0, 230, 118, 0.6);">AUTH_PROTOCOL_V.4.2</p>
        <h2 class="mb-0 fw-bold" style="font-family: 'Segoe UI', sans-serif; text-transform: uppercase; letter-spacing: 2px; text-shadow: 0 0 10px rgba(0, 230, 118, 0.5);">
            OxyGuard<span class="blink">_</span>
        </h2>
        <div class="mt-3" style="height: 1px; background: linear-gradient(90deg, transparent, #00e676, transparent);"></div>
    </div>

    <form action="/login" method="POST">
        @csrf
        
        @if ($errors->any())
            <div class="alert alert-danger p-2 small text-center rounded-0" style="background: rgba(220, 38, 38, 0.1); border: 1px solid #dc2626; color: #ff6b6b;">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="mb-4">
            <label class="form-label small text-uppercase fw-bold" style="color: rgba(0, 230, 118, 0.7);">> Operator_ID</label>
            <input type="email" name="email" class="form-control cyber-input" placeholder="admin@oxyguard.sys" required value="{{ old('email') }}">
        </div>
        
        <div class="mb-5">
            <label class="form-label small text-uppercase fw-bold" style="color: rgba(0, 230, 118, 0.7);">> Passcode_Hash</label>
            <input type="password" name="password" class="form-control cyber-input" placeholder="••••••••••••" required>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
                <input type="checkbox" name="remember" class="form-check-input bg-transparent" style="border-color: #00e676; border-radius: 0;" id="remember">
                <label class="form-check-label small" for="remember" style="color: rgba(0, 230, 118, 0.7);">Retain Uplink</label>
            </div>
        </div>

        <div class="d-grid mt-2">
            <button type="submit" class="btn btn-neon rounded-0">Execute Authentication</button>
        </div>
    </form>

    <div class="text-center mt-4">
        <p class="small" style="color: rgba(0, 230, 118, 0.5);">Unauthorized access restricted. <a href="/register" class="text-decoration-none" style="color: #00e676; border-bottom: 1px dashed #00e676;">Request Clearance</a></p>
    </div>
</div>

</body>
</html>