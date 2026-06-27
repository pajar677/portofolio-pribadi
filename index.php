<?php
require_once 'includes/config.php';
$conn = getConnection();

// Ambil data profil
$profil = $conn->query("SELECT * FROM profil LIMIT 1")->fetch_assoc();

// Ambil data pendidikan
$pendidikan = $conn->query("SELECT * FROM pendidikan ORDER BY urutan ASC")->fetch_all(MYSQLI_ASSOC);

// Ambil data pengalaman
$pengalaman = $conn->query("SELECT * FROM pengalaman ORDER BY urutan ASC")->fetch_all(MYSQLI_ASSOC);

// Ambil data keahlian
$keahlian = $conn->query("SELECT * FROM keahlian ORDER BY urutan ASC")->fetch_all(MYSQLI_ASSOC);

// Ambil data proyek
$proyek = $conn->query("SELECT * FROM proyek ORDER BY urutan ASC")->fetch_all(MYSQLI_ASSOC);

// Proses form kontak
$pesan_sukses = '';
$pesan_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kirim_pesan'])) {
    $nama   = sanitize($_POST['nama'] ?? '');
    $email  = sanitize($_POST['email'] ?? '');
    $subjek = sanitize($_POST['subjek'] ?? '');
    $pesan  = sanitize($_POST['pesan'] ?? '');

    if ($nama && $email && $pesan) {
        $stmt = $conn->prepare("INSERT INTO pesan (nama, email, subjek, pesan) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nama, $email, $subjek, $pesan);
        if ($stmt->execute()) {
            $pesan_sukses = "Pesan berhasil dikirim! Saya akan segera menghubungi Anda.";
        } else {
            $pesan_error = "Terjadi kesalahan. Silakan coba lagi.";
        }
        $stmt->close();
    } else {
        $pesan_error = "Mohon lengkapi semua field yang wajib diisi.";
    }
}

$conn->close();

// Kelompokkan keahlian berdasarkan kategori
$keahlian_grouped = [];
foreach ($keahlian as $skill) {
    $keahlian_grouped[$skill['kategori']][] = $skill;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($profil['nama'] ?? 'Portfolio') ?> — Developer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:       #0D0F14;
            --surface:  #151821;
            --card:     #1C2030;
            --border:   #252A3A;
            --accent:   #5B7FFF;
            --accent2:  #B57BFF;
            --text:     #E8EAEF;
            --muted:    #7A82A0;
            --success:  #4ADE80;
            --radius:   12px;
            --mono:     'JetBrains Mono', monospace;
            --sans:     'Space Grotesk', sans-serif;
        }

        html { scroll-behavior: smooth; }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: var(--sans);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* ── NAV ── */
        nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            background: rgba(13,15,20,.85);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            padding: 0 clamp(1rem, 5vw, 4rem);
            height: 64px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .nav-logo {
            font-family: var(--mono);
            font-size: .9rem;
            color: var(--accent);
        }
        .nav-logo span { color: var(--text); }
        .nav-links { display: flex; gap: 2rem; list-style: none; }
        .nav-links a {
            color: var(--muted); text-decoration: none;
            font-size: .875rem; font-weight: 500;
            transition: color .2s;
        }
        .nav-links a:hover { color: var(--text); }
        .hamburger { display: none; flex-direction: column; gap: 5px; cursor: pointer; }
        .hamburger span { width: 22px; height: 2px; background: var(--text); border-radius: 2px; transition: .3s; }

        /* ── HERO ── */
        #hero {
            min-height: 100vh;
            display: grid; place-items: center;
            padding: 6rem clamp(1rem, 8vw, 8rem) 4rem;
            position: relative; overflow: hidden;
        }
        .hero-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem; align-items: center;
            max-width: 1100px; width: 100%;
        }
        .hero-eyebrow {
            font-family: var(--mono);
            font-size: .8rem; color: var(--accent);
            letter-spacing: .12em; text-transform: uppercase;
            margin-bottom: 1rem;
        }
        .hero-name {
            font-size: clamp(2.5rem, 6vw, 4.5rem);
            font-weight: 700; line-height: 1.05;
            letter-spacing: -.02em;
        }
        .hero-name em {
            font-style: normal;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .hero-role {
            font-size: 1.1rem; color: var(--muted);
            margin: 1rem 0 2rem;
        }
        .hero-bio { color: var(--muted); max-width: 500px; margin-bottom: 2.5rem; }
        .btn-group { display: flex; gap: 1rem; flex-wrap: wrap; }
        .btn {
            display: inline-flex; align-items: center; gap: .5rem;
            padding: .75rem 1.75rem; border-radius: 8px;
            font-family: var(--sans); font-size: .9rem; font-weight: 600;
            text-decoration: none; cursor: pointer; border: none;
            transition: all .2s; line-height: 1;
        }
        .btn-primary {
            background: var(--accent); color: #fff;
        }
        .btn-primary:hover { background: #7B9FFF; transform: translateY(-2px); }
        .btn-outline {
            background: transparent;
            border: 1.5px solid var(--border);
            color: var(--text);
        }
        .btn-outline:hover { border-color: var(--accent); color: var(--accent); transform: translateY(-2px); }
        .hero-avatar {
            display: flex; justify-content: center;
        }
        .avatar-wrap {
            width: min(320px, 90%); aspect-ratio: 1;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            padding: 3px;
            position: relative;
        }
        .avatar-inner {
            width: 100%; height: 100%; border-radius: 50%;
            background: var(--card);
            display: grid; place-items: center;
            font-size: 6rem;
        }
        .hero-bg-blob {
            position: absolute; width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(91,127,255,.08), transparent 70%);
            border-radius: 50%; top: 50%; left: 50%; transform: translate(-50%, -50%);
            pointer-events: none;
        }
        .social-row { display: flex; gap: 1rem; margin-top: 1.5rem; }
        .social-link {
            display: inline-flex; align-items: center; gap: .4rem;
            color: var(--muted); text-decoration: none;
            font-size: .82rem; font-family: var(--mono);
            transition: color .2s;
        }
        .social-link:hover { color: var(--accent); }

        /* ── SECTIONS ── */
        section { padding: 6rem clamp(1rem, 8vw, 8rem); }
        .section-header { margin-bottom: 3.5rem; }
        .section-label {
            font-family: var(--mono); font-size: .75rem;
            color: var(--accent); letter-spacing: .15em;
            text-transform: uppercase; margin-bottom: .75rem;
        }
        .section-title {
            font-size: clamp(1.6rem, 3vw, 2.5rem);
            font-weight: 700; letter-spacing: -.02em;
        }
        .section-title span {
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .divider {
            width: 48px; height: 3px;
            background: linear-gradient(90deg, var(--accent), var(--accent2));
            border-radius: 2px; margin-top: 1rem;
        }
        .max-w { max-width: 1100px; margin: 0 auto; }

        /* ── TENTANG ── */
        #tentang { background: var(--surface); }
        .tentang-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem; align-items: start;
        }
        .tentang-text p { color: var(--muted); margin-bottom: 1.2rem; }
        .info-list { margin-top: 2rem; display: grid; gap: .75rem; }
        .info-item {
            display: flex; gap: 1rem; align-items: flex-start;
        }
        .info-label {
            font-family: var(--mono); font-size: .78rem;
            color: var(--muted); min-width: 80px;
        }
        .info-value { color: var(--text); font-size: .9rem; }
        .stat-grid {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .stat-card {
            background: var(--card); border: 1px solid var(--border);
            border-radius: var(--radius); padding: 1.5rem;
            text-align: center;
        }
        .stat-num {
            font-size: 2.5rem; font-weight: 700;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .stat-desc { color: var(--muted); font-size: .82rem; margin-top: .25rem; }

        /* ── KEAHLIAN ── */
        .skill-categories { display: grid; gap: 2rem; }
        .skill-cat-title {
            font-family: var(--mono); font-size: .78rem;
            color: var(--muted); letter-spacing: .1em;
            text-transform: uppercase; margin-bottom: 1rem;
        }
        .skill-list { display: flex; flex-direction: column; gap: .75rem; }
        .skill-item { display: grid; gap: .4rem; }
        .skill-meta { display: flex; justify-content: space-between; align-items: center; }
        .skill-name { font-size: .9rem; font-weight: 500; }
        .skill-pct {
            font-family: var(--mono); font-size: .75rem; color: var(--accent);
        }
        .skill-bar {
            height: 6px; background: var(--border); border-radius: 3px; overflow: hidden;
        }
        .skill-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--accent), var(--accent2));
            border-radius: 3px;
            width: 0;
            transition: width 1s ease;
        }
        .skill-cols {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }
        .skill-group {
            background: var(--card); border: 1px solid var(--border);
            border-radius: var(--radius); padding: 1.5rem;
        }

        /* ── TIMELINE (pendidikan & pengalaman) ── */
        .timeline { display: grid; gap: 0; position: relative; }
        .timeline::before {
            content: '';
            position: absolute; left: 15px; top: 0; bottom: 0;
            width: 1px; background: var(--border);
        }
        .tl-item {
            display: grid;
            grid-template-columns: 40px 1fr;
            gap: 1.5rem; padding-bottom: 2.5rem;
            position: relative;
        }
        .tl-dot {
            width: 32px; height: 32px; border-radius: 50%;
            background: var(--card);
            border: 2px solid var(--accent);
            display: grid; place-items: center;
            flex-shrink: 0; position: relative; z-index: 1;
            font-size: .75rem; color: var(--accent);
        }
        .tl-card {
            background: var(--card); border: 1px solid var(--border);
            border-radius: var(--radius); padding: 1.25rem 1.5rem;
            transition: border-color .2s;
        }
        .tl-card:hover { border-color: var(--accent); }
        .tl-title { font-weight: 600; font-size: 1rem; margin-bottom: .25rem; }
        .tl-sub { color: var(--accent); font-size: .85rem; font-weight: 500; }
        .tl-date {
            font-family: var(--mono); font-size: .75rem;
            color: var(--muted); margin: .5rem 0;
        }
        .tl-desc { color: var(--muted); font-size: .875rem; margin-top: .5rem; }
        .badge {
            display: inline-block; padding: .2rem .65rem;
            background: rgba(91,127,255,.15); color: var(--accent);
            border-radius: 99px; font-size: .72rem;
            font-family: var(--mono); margin-top: .5rem;
        }

        /* ── PROYEK ── */
        #proyek { background: var(--surface); }
        .proyek-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }
        .proyek-card {
            background: var(--card); border: 1px solid var(--border);
            border-radius: var(--radius); overflow: hidden;
            display: flex; flex-direction: column;
            transition: transform .2s, border-color .2s;
        }
        .proyek-card:hover { transform: translateY(-4px); border-color: var(--accent); }
        .proyek-thumb {
            height: 160px;
            background: linear-gradient(135deg, rgba(91,127,255,.15), rgba(181,123,255,.15));
            display: grid; place-items: center;
            font-size: 3rem;
        }
        .proyek-body { padding: 1.5rem; flex: 1; display: flex; flex-direction: column; }
        .proyek-year {
            font-family: var(--mono); font-size: .72rem;
            color: var(--muted); margin-bottom: .5rem;
        }
        .proyek-title { font-weight: 600; font-size: 1.05rem; margin-bottom: .75rem; }
        .proyek-desc { color: var(--muted); font-size: .85rem; flex: 1; }
        .proyek-tech {
            display: flex; flex-wrap: wrap; gap: .4rem; margin-top: 1rem;
        }
        .tech-tag {
            padding: .2rem .6rem;
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 4px; font-size: .7rem;
            color: var(--muted); font-family: var(--mono);
        }
        .proyek-links { display: flex; gap: 1rem; margin-top: 1.25rem; }
        .proyek-link {
            color: var(--accent); text-decoration: none;
            font-size: .8rem; font-weight: 500;
            display: flex; align-items: center; gap: .3rem;
            transition: opacity .2s;
        }
        .proyek-link:hover { opacity: .7; }

        /* ── KONTAK ── */
        .kontak-grid {
            display: grid; grid-template-columns: 1fr 1.4fr;
            gap: 4rem; align-items: start;
        }
        .kontak-info h3 { font-size: 1.1rem; margin-bottom: 1.5rem; }
        .kontak-item {
            display: flex; align-items: flex-start; gap: 1rem;
            margin-bottom: 1.25rem;
        }
        .kontak-icon {
            width: 40px; height: 40px; border-radius: 8px;
            background: var(--card); border: 1px solid var(--border);
            display: grid; place-items: center; font-size: 1rem;
            flex-shrink: 0;
        }
        .kontak-detail .label { font-size: .75rem; color: var(--muted); }
        .kontak-detail .value { font-size: .9rem; }
        .form-grid { display: grid; gap: 1rem; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .form-group { display: flex; flex-direction: column; gap: .4rem; }
        .form-group label { font-size: .8rem; color: var(--muted); font-weight: 500; }
        .form-group input,
        .form-group textarea {
            background: var(--card); border: 1.5px solid var(--border);
            color: var(--text); padding: .75rem 1rem;
            border-radius: 8px; font-family: var(--sans); font-size: .9rem;
            outline: none; transition: border-color .2s;
            resize: none;
        }
        .form-group input:focus,
        .form-group textarea:focus { border-color: var(--accent); }
        .form-group textarea { min-height: 130px; }
        .alert {
            padding: .85rem 1.25rem; border-radius: 8px;
            font-size: .875rem;
        }
        .alert-success { background: rgba(74,222,128,.1); color: var(--success); border: 1px solid rgba(74,222,128,.2); }
        .alert-error   { background: rgba(248,113,113,.1); color: #F87171; border: 1px solid rgba(248,113,113,.2); }

        /* ── FOOTER ── */
        footer {
            background: var(--surface);
            border-top: 1px solid var(--border);
            padding: 2rem clamp(1rem, 8vw, 8rem);
            text-align: center; color: var(--muted);
            font-size: .82rem;
        }
        footer a { color: var(--accent); text-decoration: none; }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .nav-links { display: none; position: absolute; top: 64px; left: 0; right: 0;
                background: var(--surface); flex-direction: column; padding: 1.5rem;
                border-bottom: 1px solid var(--border); gap: 1.25rem; }
            .nav-links.open { display: flex; }
            .hamburger { display: flex; }
            .hero-grid, .tentang-grid, .kontak-grid { grid-template-columns: 1fr; }
            .hero-avatar { order: -1; }
            .avatar-wrap { width: 180px; }
            .form-row { grid-template-columns: 1fr; }
            .stat-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>

<!-- NAV -->
<nav>
    <div class="nav-logo">
    &lt;<span><?= htmlspecialchars($profil['nama'] ?? '') ?></span> /&gt;
</div>
    <ul class="nav-links" id="navLinks">
        <li><a href="#tentang">Tentang</a></li>
        <li><a href="#keahlian">Keahlian</a></li>
        <li><a href="#pendidikan">Pendidikan</a></li>
        <li><a href="#pengalaman">Pengalaman</a></li>
        <li><a href="#proyek">Proyek</a></li>
        <li><a href="#kontak">Kontak</a></li>
    </ul>
    <div class="hamburger" onclick="toggleNav()">
        <span></span><span></span><span></span>
    </div>
</nav>

<!-- HERO -->
<section id="hero">
    <div class="hero-bg-blob"></div>
    <div class="hero-grid">
        <div>
            <p class="hero-eyebrow">// Selamat datang</p>
            <h1 class="hero-name">
                Halo, saya<br><em><?= htmlspecialchars($profil['nama'] ?? 'Fajar Dwi Nugroho') ?></em>
            </h1>
            <p class="hero-role"><?= htmlspecialchars($profil['jabatan'] ?? '') ?></p>
            <p class="hero-bio"><?= htmlspecialchars($profil['bio'] ?? '') ?></p>
            <div class="btn-group">
                <a href="#proyek" class="btn btn-primary">Lihat Proyek →</a>
                <a href="#kontak" class="btn btn-outline">Hubungi Saya</a>
            </div>
            <div class="social-row">
                <?php if (!empty($profil['github'])): ?>
                <a href="<?= htmlspecialchars($profil['github']) ?>" class="social-link" target="_blank">⌥ GitHub</a>
                <?php endif; ?>
                <?php if (!empty($profil['linkedin'])): ?>
                <a href="<?= htmlspecialchars($profil['linkedin']) ?>" class="social-link" target="_blank">⌘ LinkedIn</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="hero-avatar">
            <div class="avatar-wrap">
                <div class="avatar-inner">
                    <?php if (!empty($profil['foto'])): ?>
                    <img src="<?= htmlspecialchars($profil['foto']) ?>" alt="Foto" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                    <?php else: ?>
                    👨‍💻
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- TENTANG -->
<section id="tentang">
    <div class="max-w">
        <div class="section-header">
            <p class="section-label">// 01 — tentang saya</p>
            <h2 class="section-title">Siapa <span>Saya?</span></h2>
            <div class="divider"></div>
        </div>
        <div class="tentang-grid">
            <div class="tentang-text">
                <p><?= htmlspecialchars($profil['bio'] ?? '') ?></p>
                <p>Saya percaya bahwa teknologi yang baik harus mudah digunakan, skalabel, dan memberikan nilai nyata bagi penggunanya.</p>
                <div class="info-list">
                    <?php if (!empty($profil['email'])): ?>
                    <div class="info-item">
                        <span class="info-label">Email</span>
                       <?= htmlspecialchars($profil['nugrohofajardwi7@gmail.com'] ?? 'nugrohofajardwi7@gmail.com') ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($profil['telepon'])): ?>
                    <div class="info-item">
                        <span class="info-label">Telepon</span>
                        <?= htmlspecialchars($profil['082323531573'] ?? '082323531573') ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($profil['lokasi'])): ?>
                    <div class="info-item">
                        <span class="info-label">Lokasi</span>
                        <span class="info-value">📍 <?= htmlspecialchars($profil['Tegal'] ?? 'Tegal') ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-num"><?= count($proyek) ?>+</div>
                    <div class="stat-desc">Proyek Selesai</div>
                </div>
                <div class="stat-card">
                    <div class="stat-num"><?= count($pengalaman) ?>+</div>
                    <div class="stat-desc">Pengalaman Kerja</div>
                </div>
                <div class="stat-card">
                    <div class="stat-num"><?= count($keahlian) ?>+</div>
                    <div class="stat-desc">Teknologi Dikuasai</div>
                </div>
                <div class="stat-card">
                    <div class="stat-num"><?= count($pendidikan) ?></div>
                    <div class="stat-desc">Jenjang Pendidikan</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- KEAHLIAN -->
<section id="keahlian">
    <div class="max-w">
        <div class="section-header">
            <p class="section-label">// 02 — teknologi</p>
            <h2 class="section-title">Keahlian <span>Teknis</span></h2>
            <div class="divider"></div>
        </div>
        <div class="skill-cols">
            <?php foreach ($keahlian_grouped as $kategori => $skills): ?>
            <div class="skill-group">
                <p class="skill-cat-title"><?= htmlspecialchars($kategori) ?></p>
                <div class="skill-list">
                    <?php foreach ($skills as $skill): ?>
                    <div class="skill-item">
                        <div class="skill-meta">
                            <span class="skill-name"><?= htmlspecialchars($skill['nama']) ?></span>
                            <span class="skill-pct"><?= $skill['level'] ?>%</span>
                        </div>
                        <div class="skill-bar">
                            <div class="skill-fill" data-width="<?= $skill['level'] ?>"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- PENDIDIKAN -->
<section id="pendidikan" style="background:var(--surface)">
    <div class="max-w">
        <div class="section-header">
            <p class="section-label">// 03 — riwayat belajar</p>
            <h2 class="section-title">Pendidikan <span>Formal</span></h2>
            <div class="divider"></div>
        </div>
        <div class="timeline">
            <?php foreach ($pendidikan as $p): ?>
            <div class="tl-item">
                <div class="tl-dot">🎓</div>
                <div class="tl-card">
                    <div class="tl-title">
    <?= htmlspecialchars($p['institusi'] ?? '') ?>
</div>
                    <div class="tl-sub"><?= htmlspecialchars($p['jurusan'] ?? 'IPA') ?></div>
                    <div class="tl-date">
                        <?= htmlspecialchars($p['tahun_mulai'] ?? '2022') ?>
    —
    <?= htmlspecialchars($p['tahun_selesai'] ?? '2024') ?>
</div>
                    </div>
                    <?php if (!empty($p['jenjang'])): ?>
                    <span class="badge"><?= htmlspecialchars($p['jenjang']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($p['deskripsi'])): ?>
                    <p class="tl-desc"><?= htmlspecialchars($p['deskripsi']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- PENGALAMAN -->
<section id="pengalaman">
    <div class="max-w">
        <div class="section-header">
            <p class="section-label">// 04 — riwayat kerja</p>
            <h2 class="section-title">Pengalaman <span>Kerja</span></h2>
            <div class="divider"></div>
        </div>
        <div class="timeline">
            <?php foreach ($pengalaman as $p): ?>
            <div class="tl-item">
                <div class="tl-dot">💼</div>
                <div class="tl-card">
                    <div class="tl-title"><?= htmlspecialchars($p['perusahaan']) ?></div>
                    <div class="tl-sub"><?= htmlspecialchars($p['posisi'] ?? '') ?></div>
                    <div class="tl-date">
                        <?= htmlspecialchars($p['tahun_mulai']) ?> —
                        <?= $p['masih_bekerja'] ? '<span style="color:var(--success)">Sekarang</span>' : htmlspecialchars($p['tahun_selesai']) ?>
                    </div>
                    <?php if (!empty($p['deskripsi'])): ?>
                    <p class="tl-desc"><?= htmlspecialchars($p['deskripsi']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- PROYEK -->
<section id="proyek">
    <div class="max-w">
        <div class="section-header">
            <p class="section-label">// 05 — karya</p>
            <h2 class="section-title">Proyek <span>Pilihan</span></h2>
            <div class="divider"></div>
        </div>
        <div class="proyek-grid">
            <?php
            $icons = ['🚀','💡','⚡','🛠️','🌐','📱'];
            foreach ($proyek as $i => $p):
                $teknologi = array_map('trim', explode(',', $p['teknologi'] ?? ''));
            ?>
            <div class="proyek-card">
                <div class="proyek-thumb"><?= $icons[$i % count($icons)] ?></div>
                <div class="proyek-body">
                    <p class="proyek-year"><?= $p['tahun'] ?? '' ?></p>
                    <h3 class="proyek-title"><?= htmlspecialchars($p['judul']) ?></h3>
                    <p class="proyek-desc"><?= htmlspecialchars($p['deskripsi'] ?? '') ?></p>
                    <div class="proyek-tech">
                        <?php foreach ($teknologi as $t): ?>
                        <span class="tech-tag"><?= htmlspecialchars(trim($t)) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="proyek-links">
                        <?php if (!empty($p['url_demo']) && $p['url_demo'] !== '#'): ?>
                        <a href="<?= htmlspecialchars($p['url_demo']) ?>" class="proyek-link" target="_blank">↗ Demo</a>
                        <?php endif; ?>
                        <?php if (!empty($p['url_github']) && $p['url_github'] !== '#'): ?>
                        <a href="<?= htmlspecialchars($p['url_github']) ?>" class="proyek-link" target="_blank">⌥ Kode</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- KONTAK -->
<section id="kontak" style="background:var(--surface)">
    <div class="max-w">
        <div class="section-header">
            <p class="section-label">// 06 — hubungi saya</p>
            <h2 class="section-title">Mari <span>Berkolaborasi</span></h2>
            <div class="divider"></div>
        </div>
        <div class="kontak-grid">
            <div class="kontak-info">
                <h3>Terbuka untuk peluang baru</h3>
                <p style="color:var(--muted);margin-bottom:2rem;font-size:.9rem;">Apakah Anda memiliki proyek yang ingin didiskusikan atau sekadar ingin menyapa? Jangan ragu untuk menghubungi saya.</p>
                <?php if (!empty($profil['email'])): ?>
                <div class="kontak-item">
                    <div class="kontak-icon">📧</div>
                    <div class="kontak-detail">
                        <p class="label">Email</p>
                        <p class="value"><?= htmlspecialchars($profil['email']) ?></p>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($profil['telepon'])): ?>
                <div class="kontak-item">
                    <div class="kontak-icon">📱</div>
                    <div class="kontak-detail">
                        <p class="label">WhatsApp / Telepon</p>
                        <p class="value"><?= htmlspecialchars($profil['telepon']) ?></p>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($profil['lokasi'])): ?>
                <div class="kontak-item">
                    <div class="kontak-icon">📍</div>
                    <div class="kontak-detail">
                        <p class="label">Lokasi</p>
                        <p class="value"><?= htmlspecialchars($profil['lokasi']) ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div>
                <?php if ($pesan_sukses): ?>
                <div class="alert alert-success" style="margin-bottom:1.25rem;">✓ <?= htmlspecialchars($pesan_sukses) ?></div>
                <?php endif; ?>
                <?php if ($pesan_error): ?>
                <div class="alert alert-error" style="margin-bottom:1.25rem;">✕ <?= htmlspecialchars($pesan_error) ?></div>
                <?php endif; ?>
                <form method="POST" class="form-grid">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nama">Nama <span style="color:#F87171">*</span></label>
                            <input type="text" id="nama" name="nama" placeholder="Nama lengkap Anda" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email <span style="color:#F87171">*</span></label>
                            <input type="email" id="email" name="email" placeholder="email@contoh.com" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="subjek">Subjek</label>
                        <input type="text" id="subjek" name="subjek" placeholder="Topik pesan Anda">
                    </div>
                    <div class="form-group">
                        <label for="pesan">Pesan <span style="color:#F87171">*</span></label>
                        <textarea id="pesan" name="pesan" placeholder="Ceritakan proyek atau pertanyaan Anda..." required></textarea>
                    </div>
                    <button type="submit" name="kirim_pesan" class="btn btn-primary" style="justify-content:center;">
                        Kirim Pesan →
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <p>Dibuat dengan ❤️ menggunakan <strong>PHP</strong> & <strong>MySQL</strong> &nbsp;|&nbsp;
    &copy; <?= date('Y') ?> <?= htmlspecialchars($profil['nama'] ?? '') ?></p>
    <p style="margin-top:.5rem"><a href="admin/">Panel Admin</a></p>
</footer>

<script>
// Toggle mobile nav
function toggleNav() {
    document.getElementById('navLinks').classList.toggle('open');
}

// Animate skill bars on scroll
const fills = document.querySelectorAll('.skill-fill');
const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            e.target.style.width = e.target.dataset.width + '%';
            observer.unobserve(e.target);
        }
    });
}, { threshold: 0.1 });
fills.forEach(f => observer.observe(f));

// Smooth active nav highlight
const sections = document.querySelectorAll('section[id]');
const navLinks = document.querySelectorAll('.nav-links a');
window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach(s => {
        if (window.scrollY >= s.offsetTop - 100) current = s.id;
    });
    navLinks.forEach(a => {
        a.style.color = a.getAttribute('href') === '#' + current ? 'var(--text)' : '';
    });
});
</script>
</body>
</html>
