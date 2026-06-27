<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php'); exit;
}
require_once '../includes/config.php';
$conn = getConnection();

// Handle semua aksi POST
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── Update Profil ──
    if ($action === 'update_profil') {
        $fields = ['nama','jabatan','bio','email','telepon','lokasi','github','linkedin','website'];
        $values = array_map(fn($f) => sanitize($_POST[$f] ?? ''), $fields);
        $existing = $conn->query("SELECT id FROM profil LIMIT 1")->fetch_assoc();
        if ($existing) {
            $sql = "UPDATE profil SET " . implode(', ', array_map(fn($f) => "$f=?", $fields)) . " WHERE id=" . $existing['id'];
        } else {
            $sql = "INSERT INTO profil (" . implode(',', $fields) . ") VALUES (" . str_repeat("?,", count($fields)-1) . "?)";
        }
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(str_repeat("s", count($fields)), ...$values);
        $stmt->execute();
        $msg = "Profil berhasil diperbarui.";
    }

    // ── Tambah/Edit Pendidikan ──
    if ($action === 'save_pendidikan') {
        $id = (int)($_POST['id'] ?? 0);
        $institusi = sanitize($_POST['institusi'] ?? '');
        $jurusan   = sanitize($_POST['jurusan'] ?? '');
        $jenjang   = sanitize($_POST['jenjang'] ?? '');
        $mulai     = (int)($_POST['tahun_mulai'] ?? 0);
        $selesai   = (int)($_POST['tahun_selesai'] ?? 0);
        $urutan    = (int)($_POST['urutan'] ?? 0);
        if ($id) {
            $stmt = $conn->prepare("UPDATE pendidikan SET institusi=?,jurusan=?,jenjang=?,tahun_mulai=?,tahun_selesai=?,urutan=? WHERE id=?");
            $stmt->bind_param("sssiiii", $institusi,$jurusan,$jenjang,$mulai,$selesai,$urutan,$id);
        } else {
            $stmt = $conn->prepare("INSERT INTO pendidikan (institusi,jurusan,jenjang,tahun_mulai,tahun_selesai,urutan) VALUES(?,?,?,?,?,?)");
            $stmt->bind_param("sssiiii", $institusi,$jurusan,$jenjang,$mulai,$selesai,$urutan);
        }
        $stmt->execute();
        $msg = "Data pendidikan disimpan.";
    }

    // ── Hapus baris (universal) ──
    if ($action === 'delete') {
        $tbl  = preg_replace('/[^a-z_]/', '', $_POST['tabel'] ?? '');
        $id   = (int)($_POST['id'] ?? 0);
        $allowed = ['pendidikan','pengalaman','keahlian','proyek','pesan'];
        if (in_array($tbl, $allowed) && $id) {
            $conn->query("DELETE FROM $tbl WHERE id=$id");
            $msg = "Data berhasil dihapus.";
        }
    }

    // ── Tambah/Edit Pengalaman ──
    if ($action === 'save_pengalaman') {
        $id       = (int)($_POST['id'] ?? 0);
        $perusahaan = sanitize($_POST['perusahaan'] ?? '');
        $posisi   = sanitize($_POST['posisi'] ?? '');
        $mulai    = sanitize($_POST['tahun_mulai'] ?? '');
        $selesai  = sanitize($_POST['tahun_selesai'] ?? '');
        $aktif    = isset($_POST['masih_bekerja']) ? 1 : 0;
        $deskripsi = sanitize($_POST['deskripsi'] ?? '');
        $urutan   = (int)($_POST['urutan'] ?? 0);
        if ($id) {
            $stmt = $conn->prepare("UPDATE pengalaman SET perusahaan=?,posisi=?,tahun_mulai=?,tahun_selesai=?,masih_bekerja=?,deskripsi=?,urutan=? WHERE id=?");
            $stmt->bind_param("ssssissi", $perusahaan,$posisi,$mulai,$selesai,$aktif,$deskripsi,$urutan,$id);
        } else {
            $stmt = $conn->prepare("INSERT INTO pengalaman (perusahaan,posisi,tahun_mulai,tahun_selesai,masih_bekerja,deskripsi,urutan) VALUES(?,?,?,?,?,?,?)");
            $stmt->bind_param("ssssiis", $perusahaan,$posisi,$mulai,$selesai,$aktif,$deskripsi,$urutan);
        }
        $stmt->execute();
        $msg = "Data pengalaman disimpan.";
    }

    // ── Tambah/Edit Keahlian ──
    if ($action === 'save_keahlian') {
        $id       = (int)($_POST['id'] ?? 0);
        $nama     = sanitize($_POST['nama'] ?? '');
        $kategori = sanitize($_POST['kategori'] ?? '');
        $level    = (int)($_POST['level'] ?? 50);
        $urutan   = (int)($_POST['urutan'] ?? 0);
        if ($id) {
            $stmt = $conn->prepare("UPDATE keahlian SET nama=?,kategori=?,level=?,urutan=? WHERE id=?");
            $stmt->bind_param("ssiii", $nama,$kategori,$level,$urutan,$id);
        } else {
            $stmt = $conn->prepare("INSERT INTO keahlian (nama,kategori,level,urutan) VALUES(?,?,?,?)");
            $stmt->bind_param("ssii", $nama,$kategori,$level,$urutan);
        }
        $stmt->execute();
        $msg = "Data keahlian disimpan.";
    }

    // ── Tambah/Edit Proyek ──
    if ($action === 'save_proyek') {
        $id       = (int)($_POST['id'] ?? 0);
        $judul    = sanitize($_POST['judul'] ?? '');
        $deskripsi = sanitize($_POST['deskripsi'] ?? '');
        $teknologi = sanitize($_POST['teknologi'] ?? '');
        $url_demo  = sanitize($_POST['url_demo'] ?? '');
        $url_github = sanitize($_POST['url_github'] ?? '');
        $tahun    = (int)($_POST['tahun'] ?? date('Y'));
        $urutan   = (int)($_POST['urutan'] ?? 0);
        if ($id) {
            $stmt = $conn->prepare("UPDATE proyek SET judul=?,deskripsi=?,teknologi=?,url_demo=?,url_github=?,tahun=?,urutan=? WHERE id=?");
            $stmt->bind_param("sssssiiii", $judul,$deskripsi,$teknologi,$url_demo,$url_github,$tahun,$urutan,$id);
        } else {
            $stmt = $conn->prepare("INSERT INTO proyek (judul,deskripsi,teknologi,url_demo,url_github,tahun,urutan) VALUES(?,?,?,?,?,?,?)");
            $stmt->bind_param("sssssii", $judul,$deskripsi,$teknologi,$url_demo,$url_github,$tahun,$urutan);
        }
        $stmt->execute();
        $msg = "Data proyek disimpan.";
    }

    // ── Tandai pesan dibaca ──
    if ($action === 'baca_pesan') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) $conn->query("UPDATE pesan SET dibaca=1 WHERE id=$id");
    }
}

// Ambil semua data
$profil     = $conn->query("SELECT * FROM profil LIMIT 1")->fetch_assoc() ?? [];
$pendidikan = $conn->query("SELECT * FROM pendidikan ORDER BY urutan ASC")->fetch_all(MYSQLI_ASSOC);
$pengalaman = $conn->query("SELECT * FROM pengalaman ORDER BY urutan ASC")->fetch_all(MYSQLI_ASSOC);
$keahlian   = $conn->query("SELECT * FROM keahlian ORDER BY urutan ASC")->fetch_all(MYSQLI_ASSOC);
$proyek     = $conn->query("SELECT * FROM proyek ORDER BY urutan ASC")->fetch_all(MYSQLI_ASSOC);
$pesan      = $conn->query("SELECT * FROM pesan ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$pesan_baru = array_filter($pesan, fn($p) => !$p['dibaca']);
$conn->close();

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin — Portfolio</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg:#0D0F14; --surface:#151821; --card:#1C2030;
            --border:#252A3A; --accent:#5B7FFF; --accent2:#B57BFF;
            --text:#E8EAEF; --muted:#7A82A0; --success:#4ADE80;
            --error:#F87171; --mono:'JetBrains Mono',monospace;
            --sans:'Space Grotesk',sans-serif;
        }
        body { background:var(--bg); color:var(--text); font-family:var(--sans); display:flex; min-height:100vh; }

        /* Sidebar */
        .sidebar {
            width: 240px; background:var(--surface);
            border-right:1px solid var(--border);
            padding:1.5rem 0; flex-shrink:0;
            display:flex; flex-direction:column;
            position:sticky; top:0; height:100vh;
        }
        .sidebar-logo {
            padding:0 1.5rem 1.5rem;
            font-weight:700; font-size:1rem;
            color:var(--accent); border-bottom:1px solid var(--border);
        }
        .sidebar-logo span { color:var(--text); }
        .sidebar-nav { padding:1rem 0; flex:1; overflow-y:auto; }
        .sidebar-nav a {
            display:flex; align-items:center; gap:.75rem;
            padding:.65rem 1.5rem; color:var(--muted);
            text-decoration:none; font-size:.875rem;
            transition:all .2s;
        }
        .sidebar-nav a:hover, .sidebar-nav a.active {
            color:var(--text); background:rgba(91,127,255,.1);
            border-right:2px solid var(--accent);
        }
        .badge-notif {
            margin-left:auto; background:var(--error);
            color:#fff; font-size:.65rem; font-weight:700;
            padding:.1rem .45rem; border-radius:99px;
        }
        .sidebar-footer { padding:1rem 1.5rem; border-top:1px solid var(--border); }
        .sidebar-footer a {
            color:var(--muted); font-size:.8rem; text-decoration:none;
            display:block; margin-bottom:.4rem;
        }
        .sidebar-footer a:hover { color:var(--error); }

        /* Main */
        .main { flex:1; padding:2rem; overflow-y:auto; }
        .page-title { font-size:1.5rem; font-weight:700; margin-bottom:.25rem; }
        .page-sub { color:var(--muted); font-size:.85rem; margin-bottom:2rem; }
        .tab-section { display:none; }
        .tab-section.active { display:block; }

        /* Cards & tables */
        .card {
            background:var(--card); border:1px solid var(--border);
            border-radius:12px; padding:1.5rem; margin-bottom:1.5rem;
        }
        .card-title { font-weight:600; margin-bottom:1rem; display:flex; justify-content:space-between; align-items:center; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:.75rem 1rem; text-align:left; border-bottom:1px solid var(--border); font-size:.875rem; }
        th { color:var(--muted); font-weight:500; font-size:.75rem; text-transform:uppercase; letter-spacing:.05em; }
        tr:last-child td { border-bottom:none; }

        /* Forms */
        .form-grid { display:grid; gap:1rem; }
        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        .form-group { display:flex; flex-direction:column; gap:.35rem; }
        .form-group label { font-size:.78rem; color:var(--muted); font-weight:500; }
        .form-group input,
        .form-group select,
        .form-group textarea {
            background:var(--surface); border:1.5px solid var(--border);
            color:var(--text); padding:.7rem .9rem;
            border-radius:8px; font-family:var(--sans); font-size:.875rem;
            outline:none; transition:border-color .2s;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color:var(--accent); }
        .form-group textarea { min-height:100px; resize:vertical; }
        .form-group input[type="range"] { padding:0; border:none; background:transparent; }
        .check-row { display:flex; align-items:center; gap:.5rem; }
        .check-row input[type="checkbox"] { width:16px; height:16px; }

        /* Buttons */
        .btn {
            display:inline-flex; align-items:center; gap:.4rem;
            padding:.55rem 1.1rem; border:none; border-radius:7px;
            font-family:var(--sans); font-size:.82rem; font-weight:600;
            cursor:pointer; transition:all .2s; text-decoration:none;
        }
        .btn-sm { padding:.35rem .75rem; font-size:.75rem; }
        .btn-primary { background:var(--accent); color:#fff; }
        .btn-primary:hover { background:#7B9FFF; }
        .btn-success { background:rgba(74,222,128,.15); color:var(--success); border:1px solid rgba(74,222,128,.2); }
        .btn-danger { background:rgba(248,113,113,.1); color:var(--error); border:1px solid rgba(248,113,113,.2); }
        .btn-danger:hover { background:rgba(248,113,113,.2); }
        .btn-outline { background:transparent; border:1.5px solid var(--border); color:var(--text); }
        .btn-outline:hover { border-color:var(--accent); color:var(--accent); }

        /* Alert */
        .alert { padding:.75rem 1.1rem; border-radius:8px; font-size:.875rem; margin-bottom:1.25rem; }
        .alert-success { background:rgba(74,222,128,.1); color:var(--success); border:1px solid rgba(74,222,128,.2); }

        /* Stat row */
        .stat-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:1rem; margin-bottom:2rem; }
        .stat-box { background:var(--card); border:1px solid var(--border); border-radius:10px; padding:1.25rem; }
        .stat-num { font-size:2rem; font-weight:700; color:var(--accent); }
        .stat-label { color:var(--muted); font-size:.78rem; margin-top:.2rem; }

        /* Pesan */
        .pesan-item { padding:1rem 0; border-bottom:1px solid var(--border); }
        .pesan-item:last-child { border-bottom:none; }
        .pesan-header { display:flex; justify-content:space-between; margin-bottom:.5rem; }
        .pesan-nama { font-weight:600; font-size:.9rem; }
        .pesan-time { color:var(--muted); font-size:.75rem; font-family:var(--mono); }
        .pesan-email { color:var(--accent); font-size:.8rem; margin-bottom:.4rem; }
        .pesan-subjek { font-size:.82rem; font-weight:500; margin-bottom:.4rem; }
        .pesan-teks { color:var(--muted); font-size:.85rem; }
        .unread { border-left:3px solid var(--accent); padding-left:1rem; }
        .badge-new { background:var(--accent); color:#fff; font-size:.65rem; padding:.1rem .4rem; border-radius:4px; }

        @media(max-width:768px) {
            .sidebar { display:none; }
            .form-row { grid-template-columns:1fr; }
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-logo">&lt;<span>Admin</span> /&gt;</div>
    <nav class="sidebar-nav">
        <a href="#" class="active" onclick="showTab('dashboard',this)">📊 Dashboard</a>
        <a href="#" onclick="showTab('profil',this)">👤 Profil</a>
        <a href="#" onclick="showTab('pendidikan',this)">🎓 Pendidikan</a>
        <a href="#" onclick="showTab('pengalaman',this)">💼 Pengalaman</a>
        <a href="#" onclick="showTab('keahlian',this)">⚡ Keahlian</a>
        <a href="#" onclick="showTab('proyek',this)">🚀 Proyek</a>
        <a href="#" onclick="showTab('pesan',this)">
            📬 Pesan
            <?php if (count($pesan_baru) > 0): ?>
            <span class="badge-notif"><?= count($pesan_baru) ?></span>
            <?php endif; ?>
        </a>
    </nav>
    <div class="sidebar-footer">
        <a href="../" target="_blank">↗ Lihat Website</a>
        <a href="?logout=1">⏻ Keluar</a>
    </div>
</aside>

<main class="main">
    <?php if ($msg): ?>
    <div class="alert alert-success">✓ <?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- DASHBOARD -->
    <div id="tab-dashboard" class="tab-section active">
        <div class="page-title">Selamat Datang, Admin 👋</div>
        <p class="page-sub">Kelola seluruh konten website portofolio Anda dari sini.</p>
        <div class="stat-row">
            <div class="stat-box"><div class="stat-num"><?= count($pendidikan) ?></div><div class="stat-label">Pendidikan</div></div>
            <div class="stat-box"><div class="stat-num"><?= count($pengalaman) ?></div><div class="stat-label">Pengalaman</div></div>
            <div class="stat-box"><div class="stat-num"><?= count($keahlian) ?></div><div class="stat-label">Keahlian</div></div>
            <div class="stat-box"><div class="stat-num"><?= count($proyek) ?></div><div class="stat-label">Proyek</div></div>
            <div class="stat-box"><div class="stat-num" style="color:var(--error)"><?= count($pesan_baru) ?></div><div class="stat-label">Pesan Baru</div></div>
        </div>
        <div class="card">
            <div class="card-title">Pesan Terbaru</div>
            <?php foreach (array_slice($pesan, 0, 3) as $p): ?>
            <div class="pesan-item <?= !$p['dibaca'] ? 'unread' : '' ?>">
                <div class="pesan-header">
                    <span class="pesan-nama"><?= htmlspecialchars($p['nama']) ?> <?= !$p['dibaca'] ? '<span class="badge-new">Baru</span>' : '' ?></span>
                    <span class="pesan-time"><?= date('d M Y', strtotime($p['created_at'])) ?></span>
                </div>
                <div class="pesan-email"><?= htmlspecialchars($p['email']) ?></div>
                <p class="pesan-teks"><?= htmlspecialchars(substr($p['pesan'], 0, 120)) ?>...</p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- PROFIL -->
    <div id="tab-profil" class="tab-section">
        <div class="page-title">Profil Saya</div>
        <p class="page-sub">Informasi dasar yang tampil di halaman utama.</p>
        <div class="card">
            <form method="POST" class="form-grid">
                <input type="hidden" name="action" value="update_profil">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" value="<?= htmlspecialchars($profil['nama'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Jabatan / Profesi</label>
                        <input type="text" name="jabatan" value="<?= htmlspecialchars($profil['jabatan'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Bio / Tentang Saya</label>
                    <textarea name="bio"><?= htmlspecialchars($profil['bio'] ?? '') ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($profil['email'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Telepon / WhatsApp</label>
                        <input type="text" name="telepon" value="<?= htmlspecialchars($profil['telepon'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Lokasi</label>
                        <input type="text" name="lokasi" value="<?= htmlspecialchars($profil['lokasi'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>URL GitHub</label>
                        <input type="url" name="github" value="<?= htmlspecialchars($profil['github'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>URL LinkedIn</label>
                        <input type="url" name="linkedin" value="<?= htmlspecialchars($profil['linkedin'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Website Pribadi</label>
                        <input type="url" name="website" value="<?= htmlspecialchars($profil['website'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>URL Foto Profil</label>
                    <input type="text" name="foto" value="<?= htmlspecialchars($profil['foto'] ?? '') ?>" placeholder="https://...">
                </div>
                <div><button type="submit" class="btn btn-primary">💾 Simpan Profil</button></div>
            </form>
        </div>
    </div>

    <!-- PENDIDIKAN -->
    <div id="tab-pendidikan" class="tab-section">
        <div class="page-title">Pendidikan</div>
        <p class="page-sub">Riwayat pendidikan formal Anda.</p>
        <div class="card">
            <div class="card-title">Tambah Pendidikan</div>
            <form method="POST" class="form-grid">
                <input type="hidden" name="action" value="save_pendidikan">
                <input type="hidden" name="id" value="0">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Institusi</label>
                        <input type="text" name="institusi" required>
                    </div>
                    <div class="form-group">
                        <label>Jurusan / Program Studi</label>
                        <input type="text" name="jurusan">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Jenjang (S1, S2, SMA...)</label>
                        <input type="text" name="jenjang">
                    </div>
                    <div class="form-group">
                        <label>Urutan Tampil</label>
                        <input type="number" name="urutan" value="0">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Tahun Mulai</label>
                        <input type="number" name="tahun_mulai" min="1990" max="2030">
                    </div>
                    <div class="form-group">
                        <label>Tahun Selesai</label>
                        <input type="number" name="tahun_selesai" min="1990" max="2030">
                    </div>
                </div>
                <div><button type="submit" class="btn btn-primary">+ Tambah</button></div>
            </form>
        </div>
        <div class="card">
            <div class="card-title">Daftar Pendidikan</div>
            <table>
                <thead><tr><th>Institusi</th><th>Jurusan</th><th>Jenjang</th><th>Tahun</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($pendidikan as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['institusi']) ?></td>
                    <td><?= htmlspecialchars($p['jurusan']) ?></td>
                    <td><?= htmlspecialchars($p['jenjang']) ?></td>
                    <td><?= $p['tahun_mulai'] ?> – <?= $p['tahun_selesai'] ?></td>
                    <td>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Hapus data ini?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="tabel" value="pendidikan">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- PENGALAMAN -->
    <div id="tab-pengalaman" class="tab-section">
        <div class="page-title">Pengalaman Kerja</div>
        <p class="page-sub">Riwayat pekerjaan dan posisi Anda.</p>
        <div class="card">
            <div class="card-title">Tambah Pengalaman</div>
            <form method="POST" class="form-grid">
                <input type="hidden" name="action" value="save_pengalaman">
                <input type="hidden" name="id" value="0">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Perusahaan</label>
                        <input type="text" name="perusahaan" required>
                    </div>
                    <div class="form-group">
                        <label>Posisi / Jabatan</label>
                        <input type="text" name="posisi">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Tahun Mulai</label>
                        <input type="text" name="tahun_mulai" placeholder="2020">
                    </div>
                    <div class="form-group">
                        <label>Tahun Selesai</label>
                        <input type="text" name="tahun_selesai" placeholder="2022">
                    </div>
                </div>
                <div class="form-group">
                    <div class="check-row">
                        <input type="checkbox" name="masih_bekerja" id="masih_bekerja">
                        <label for="masih_bekerja">Masih bekerja di sini</label>
                    </div>
                </div>
                <div class="form-group">
                    <label>Deskripsi Pekerjaan</label>
                    <textarea name="deskripsi"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Urutan</label>
                        <input type="number" name="urutan" value="0">
                    </div>
                </div>
                <div><button type="submit" class="btn btn-primary">+ Tambah</button></div>
            </form>
        </div>
        <div class="card">
            <div class="card-title">Daftar Pengalaman</div>
            <table>
                <thead><tr><th>Perusahaan</th><th>Posisi</th><th>Periode</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($pengalaman as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['perusahaan']) ?></td>
                    <td><?= htmlspecialchars($p['posisi']) ?></td>
                    <td><?= $p['tahun_mulai'] ?> – <?= $p['masih_bekerja'] ? 'Sekarang' : $p['tahun_selesai'] ?></td>
                    <td>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Hapus data ini?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="tabel" value="pengalaman">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- KEAHLIAN -->
    <div id="tab-keahlian" class="tab-section">
        <div class="page-title">Keahlian</div>
        <p class="page-sub">Teknologi dan skill yang Anda kuasai.</p>
        <div class="card">
            <div class="card-title">Tambah Keahlian</div>
            <form method="POST" class="form-grid">
                <input type="hidden" name="action" value="save_keahlian">
                <input type="hidden" name="id" value="0">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Keahlian</label>
                        <input type="text" name="nama" required placeholder="Contoh: PHP / Laravel">
                    </div>
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="kategori">
                            <option>Backend</option>
                            <option>Frontend</option>
                            <option>Database</option>
                            <option>DevOps</option>
                            <option>Tools</option>
                            <option>Mobile</option>
                            <option>Lainnya</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Level (%) — <span id="lvl-val">50</span>%</label>
                        <input type="range" name="level" min="0" max="100" value="50"
                               oninput="document.getElementById('lvl-val').textContent=this.value">
                    </div>
                    <div class="form-group">
                        <label>Urutan</label>
                        <input type="number" name="urutan" value="0">
                    </div>
                </div>
                <div><button type="submit" class="btn btn-primary">+ Tambah</button></div>
            </form>
        </div>
        <div class="card">
            <div class="card-title">Daftar Keahlian</div>
            <table>
                <thead><tr><th>Nama</th><th>Kategori</th><th>Level</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($keahlian as $k): ?>
                <tr>
                    <td><?= htmlspecialchars($k['nama']) ?></td>
                    <td><?= htmlspecialchars($k['kategori']) ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.5rem">
                            <div style="width:80px;height:6px;background:var(--border);border-radius:3px;overflow:hidden">
                                <div style="width:<?= $k['level'] ?>%;height:100%;background:var(--accent);border-radius:3px"></div>
                            </div>
                            <span style="font-size:.75rem;color:var(--muted)"><?= $k['level'] ?>%</span>
                        </div>
                    </td>
                    <td>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Hapus?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="tabel" value="keahlian">
                            <input type="hidden" name="id" value="<?= $k['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- PROYEK -->
    <div id="tab-proyek" class="tab-section">
        <div class="page-title">Proyek</div>
        <p class="page-sub">Portofolio dan karya yang pernah dibuat.</p>
        <div class="card">
            <div class="card-title">Tambah Proyek</div>
            <form method="POST" class="form-grid">
                <input type="hidden" name="action" value="save_proyek">
                <input type="hidden" name="id" value="0">
                <div class="form-row">
                    <div class="form-group">
                        <label>Judul Proyek</label>
                        <input type="text" name="judul" required>
                    </div>
                    <div class="form-group">
                        <label>Tahun</label>
                        <input type="number" name="tahun" value="<?= date('Y') ?>" min="2000" max="2030">
                    </div>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi"></textarea>
                </div>
                <div class="form-group">
                    <label>Teknologi (pisah koma)</label>
                    <input type="text" name="teknologi" placeholder="PHP, MySQL, React">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>URL Demo</label>
                        <input type="text" name="url_demo">
                    </div>
                    <div class="form-group">
                        <label>URL GitHub</label>
                        <input type="text" name="url_github">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Urutan</label>
                        <input type="number" name="urutan" value="0">
                    </div>
                </div>
                <div><button type="submit" class="btn btn-primary">+ Tambah</button></div>
            </form>
        </div>
        <div class="card">
            <div class="card-title">Daftar Proyek</div>
            <table>
                <thead><tr><th>Judul</th><th>Teknologi</th><th>Tahun</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($proyek as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['judul']) ?></td>
                    <td style="font-size:.78rem;color:var(--muted)"><?= htmlspecialchars(substr($p['teknologi']??'', 0, 40)) ?></td>
                    <td><?= $p['tahun'] ?></td>
                    <td>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Hapus proyek ini?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="tabel" value="proyek">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- PESAN -->
    <div id="tab-pesan" class="tab-section">
        <div class="page-title">Kotak Pesan</div>
        <p class="page-sub"><?= count($pesan_baru) ?> pesan belum dibaca dari <?= count($pesan) ?> total.</p>
        <div class="card">
            <?php if (empty($pesan)): ?>
            <p style="color:var(--muted);text-align:center;padding:2rem">Belum ada pesan masuk.</p>
            <?php endif; ?>
            <?php foreach ($pesan as $p): ?>
            <div class="pesan-item <?= !$p['dibaca'] ? 'unread' : '' ?>">
                <div class="pesan-header">
                    <span class="pesan-nama">
                        <?= htmlspecialchars($p['nama']) ?>
                        <?= !$p['dibaca'] ? '<span class="badge-new">Baru</span>' : '' ?>
                    </span>
                    <span class="pesan-time"><?= date('d M Y H:i', strtotime($p['created_at'])) ?></span>
                </div>
                <div class="pesan-email"><?= htmlspecialchars($p['email']) ?></div>
                <?php if (!empty($p['subjek'])): ?>
                <div class="pesan-subjek">📌 <?= htmlspecialchars($p['subjek']) ?></div>
                <?php endif; ?>
                <p class="pesan-teks"><?= nl2br(htmlspecialchars($p['pesan'])) ?></p>
                <?php if (!$p['dibaca']): ?>
                <form method="POST" style="margin-top:.75rem">
                    <input type="hidden" name="action" value="baca_pesan">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-success">✓ Tandai Dibaca</button>
                </form>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</main>

<script>
function showTab(name, el) {
    document.querySelectorAll('.tab-section').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.sidebar-nav a').forEach(a => a.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    el.classList.add('active');
    return false;
}
</script>
</body>
</html>
