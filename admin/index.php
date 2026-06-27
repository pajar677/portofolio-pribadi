<?php
session_start();
require_once '../includes/config.php';

// Login sederhana (ganti password di sini)
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'admin123');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if ($_POST['username'] === ADMIN_USER && $_POST['password'] === ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Username atau password salah.';
    }
}

if (isset($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Portfolio</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg: #0D0F14; --surface: #151821; --card: #1C2030;
            --border: #252A3A; --accent: #5B7FFF; --text: #E8EAEF;
            --muted: #7A82A0; --error: #F87171;
        }
        body {
            background: var(--bg); color: var(--text);
            font-family: 'Space Grotesk', sans-serif;
            min-height: 100vh; display: grid; place-items: center;
        }
        .login-box {
            background: var(--card); border: 1px solid var(--border);
            border-radius: 16px; padding: 2.5rem; width: min(420px, 92vw);
        }
        .login-logo {
            text-align: center; margin-bottom: 2rem;
            font-size: 1.5rem; font-weight: 700;
        }
        .login-logo span { color: var(--accent); }
        .form-group { margin-bottom: 1.1rem; }
        .form-group label { display: block; font-size: .82rem; color: var(--muted); margin-bottom: .4rem; }
        .form-group input {
            width: 100%; background: var(--surface); border: 1.5px solid var(--border);
            color: var(--text); padding: .8rem 1rem; border-radius: 8px;
            font-family: inherit; font-size: .9rem; outline: none;
            transition: border-color .2s;
        }
        .form-group input:focus { border-color: var(--accent); }
        .btn {
            width: 100%; padding: .85rem; border: none; border-radius: 8px;
            background: var(--accent); color: #fff; font-family: inherit;
            font-size: .95rem; font-weight: 600; cursor: pointer;
            transition: background .2s;
        }
        .btn:hover { background: #7B9FFF; }
        .error { background: rgba(248,113,113,.1); color: var(--error);
            border: 1px solid rgba(248,113,113,.2); padding: .75rem 1rem;
            border-radius: 8px; font-size: .85rem; margin-bottom: 1.25rem; }
        .back { text-align: center; margin-top: 1.25rem; }
        .back a { color: var(--muted); font-size: .82rem; text-decoration: none; }
        .back a:hover { color: var(--accent); }
    </style>
</head>
<body>
<div class="login-box">
    <div class="login-logo">⚙️ Panel <span>Admin</span></div>
    <?php if ($error): ?>
    <div class="error">✕ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" placeholder="Masukkan username" required autofocus>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Masukkan password" required>
        </div>
        <button type="submit" name="login" class="btn">Masuk →</button>
    </form>
    <div class="back"><a href="../">← Kembali ke Website</a></div>
</div>
</body>
</html>
