<?php
// ======================
// KONEKSI DATABASE
// ======================
$host = "localhost";
$user = "root";
$pass = "";
$db   = "kas_database";
$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// ======================
// TAMBAH TRANSAKSI
// ======================
if (isset($_POST['simpan_transaksi'])) {
    $tanggal = $_POST['tanggal'];
    $id_anggota = $_POST['id_anggota'];
    $jenis = $_POST['jenis'];
    $jumlah = $_POST['jumlah'];
    $keterangan = $_POST['keterangan'];

    mysqli_query($conn, "INSERT INTO kas (tanggal, id_anggota, jenis, jumlah, keterangan)
                         VALUES ('$tanggal', '$id_anggota', '$jenis', '$jumlah', '$keterangan')");
    header("Location: kas_database.php?page=kas");
    exit;
}

// ======================
// EDIT TRANSAKSI
// ======================
if (isset($_POST['update_transaksi'])) {
    $id_transaksi = $_POST['id_transaksi'];
    $tanggal = $_POST['tanggal'];
    $id_anggota = $_POST['id_anggota'];
    $jenis = $_POST['jenis'];
    $jumlah = $_POST['jumlah'];
    $keterangan = $_POST['keterangan'];

    mysqli_query($conn, "UPDATE kas SET tanggal='$tanggal', id_anggota='$id_anggota', jenis='$jenis', jumlah='$jumlah', keterangan='$keterangan' WHERE id_transaksi='$id_transaksi'");
    header("Location: kas_database.php?page=kas");
    exit;
}

// ======================
// TAMBAH ANGGOTA
// ======================
if (isset($_POST['simpan_anggota'])) {
    $nama = trim($_POST['nama']);
    if (!empty($nama)) {
        mysqli_query($conn, "INSERT INTO anggota (nama) VALUES ('$nama')");
    }
    header("Location: kas_database.php?page=anggota");
    exit;
}

// ======================
// EDIT ANGGOTA
// ======================
if (isset($_POST['update_anggota'])) {
    $id_anggota = $_POST['id_anggota'];
    $nama = trim($_POST['nama']);
    if (!empty($nama)) {
        mysqli_query($conn, "UPDATE anggota SET nama='$nama' WHERE id_anggota='$id_anggota'");
    }
    header("Location: kas_database.php?page=anggota");
    exit;
}

// ======================
// HAPUS DATA
// ======================
if (isset($_GET['hapus_kas'])) {
    $id = $_GET['hapus_kas'];
    mysqli_query($conn, "DELETE FROM kas WHERE id_transaksi='$id'");
    header("Location: kas_database.php?page=kas");
    exit;
}

if (isset($_GET['hapus_anggota'])) {
    $id = $_GET['hapus_anggota'];
    mysqli_query($conn, "DELETE FROM anggota WHERE id_anggota='$id'");
    header("Location: kas_database.php?page=anggota");
    exit;
}

// ======================
// AMBIL DATA
// ======================
$anggota = mysqli_query($conn, "SELECT * FROM anggota ORDER BY nama ASC");
$result = mysqli_query($conn, "SELECT kas.*, anggota.nama FROM kas 
          JOIN anggota ON kas.id_anggota = anggota.id_anggota 
          ORDER BY tanggal DESC");

$totalMasuk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah) AS total FROM kas WHERE jenis='masuk'"))['total'] ?? 0;
$totalKeluar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah) AS total FROM kas WHERE jenis='keluar'"))['total'] ?? 0;
$saldo = $totalMasuk - $totalKeluar;

$page = $_GET['page'] ?? 'kas';

// ======================
// DATA UNTUK EDIT
// ======================
$editTransaksi = null;
if (isset($_GET['edit_kas'])) {
    $id_edit = $_GET['edit_kas'];
    $editTransaksi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM kas WHERE id_transaksi='$id_edit'"));
}

$editAnggota = null;
if (isset($_GET['edit_anggota'])) {
    $id_edit = $_GET['edit_anggota'];
    $editAnggota = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM anggota WHERE id_anggota='$id_edit'"));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>💸 Kas Kelas </title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
<style>
    body { background: linear-gradient(135deg, #f8efff, #e0f7fa); font-family: 'Poppins', sans-serif; }
    .container { max-width: 950px; }
    .header { text-align: center; margin: 40px 0 20px; }
    .header h1 { font-weight: 700; color: #5a4fcf; }
    .saldo-box { background: linear-gradient(120deg, #a18cd1 0%, #fbc2eb 100%); color: white; border-radius: 20px; padding: 35px; text-align: center; box-shadow: 0 6px 25px rgba(0,0,0,0.1); }
    .saldo-box h4 { font-family: 'Playfair Display', serif; font-size: 1.5rem; }
    .saldo-box h2 { font-size: 2.6rem; font-weight: 700; }
    .saldo-detail { font-size: 1rem; background-color: rgba(255,255,255,0.25); display: inline-block; padding: 6px 15px; border-radius: 12px; font-weight: 500; }
    .nav-tabs .nav-link { font-weight: 600; color: #6c63ff; border-radius: 10px 10px 0 0; }
    .nav-tabs .nav-link.active { background-color: #6c63ff; color: white; }
    .card { border: none; border-radius: 18px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); background-color: white; }
    .form-label { font-weight: 600; color: #555; }
    .btn-custom { border-radius: 12px; font-weight: 600; }
    .table thead { background-color: #f3f1ff; color: #555; }
</style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1> 💸 Kas Kelas </h1>
        <p>Sistem Manajemen Keuangan Informatika kelas B</p>
    </div>

    <!-- MENU TAB -->
    <ul class="nav nav-tabs mb-4 justify-content-center">
        <li class="nav-item">
            <a class="nav-link <?= $page == 'kas' ? 'active' : '' ?>" href="?page=kas">📜 Kas Kelas</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $page == 'anggota' ? 'active' : '' ?>" href="?page=anggota">👥 Data Anggota</a>
        </li>
    </ul>

    <?php if ($page == 'kas') { ?>
        <!-- SALDO -->
        <div class="saldo-box mb-5">
            <h4>💰 Saldo Saat Ini</h4>
            <h2>Rp <?= number_format($saldo, 0, ',', '.') ?></h2>
            <div class="saldo-detail">
                Masuk: <b>Rp <?= number_format($totalMasuk, 0, ',', '.') ?></b> &nbsp; | &nbsp;
                Keluar: <b>Rp <?= number_format($totalKeluar, 0, ',', '.') ?></b>
            </div>
        </div>

        <!-- FORM TRANSAKSI -->
        <div class="card p-4 mb-4">
            <h5 class="mb-3 text-secondary fw-semibold"><?= $editTransaksi ? '✏️ Edit Transaksi' : 'Tambah Transaksi ' ?></h5>
            <form method="POST">
                <?php if ($editTransaksi) { ?>
                    <input type="hidden" name="id_transaksi" value="<?= $editTransaksi['id_transaksi'] ?>">
                <?php } ?>
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" value="<?= $editTransaksi['tanggal'] ?? '' ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Anggota</label>
                        <select name="id_anggota" class="form-select" required>
                            <option value="">-- Pilih Anggota --</option>
                            <?php mysqli_data_seek($anggota, 0);
                            while ($a = mysqli_fetch_assoc($anggota)) { ?>
                                <option value="<?= $a['id_anggota'] ?>" <?= ($editTransaksi && $editTransaksi['id_anggota']==$a['id_anggota'])?'selected':'' ?>>
                                    <?= htmlspecialchars($a['nama']) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Jenis</label>
                        <select name="jenis" class="form-select" required>
                            <option value="masuk" <?= ($editTransaksi && $editTransaksi['jenis']=='masuk')?'selected':'' ?>>Masuk</option>
                            <option value="keluar" <?= ($editTransaksi && $editTransaksi['jenis']=='keluar')?'selected':'' ?>>Keluar</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Jumlah (Rp)</label>
                        <input type="number" name="jumlah" class="form-control" value="<?= $editTransaksi['jumlah'] ?? '' ?>" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Keterangan</label>
                        <input type="text" name="keterangan" class="form-control" value="<?= $editTransaksi['keterangan'] ?? '' ?>">
                    </div>
                </div>
                <div class="text-end mt-3">
                    <?php if ($editTransaksi) { ?>
                        <button type="submit" name="update_transaksi" class="btn btn-warning btn-custom">Simpan Perubahan</button>
                        <a href="kas_database.php?page=kas" class="btn btn-secondary btn-custom"> Batal</a>
                    <?php } else { ?>
                        <button type="submit" name="simpan_transaksi" class="btn btn-success btn-custom">Simpan Transaksi</button>
                    <?php } ?>
                </div>
            </form>
        </div>

        <!-- TABEL TRANSAKSI -->
        <div class="card p-4">
            <h5 class="mb-3 text-secondary fw-semibold">Riwayat Transaksi 📜</h5>
            <table class="table table-hover align-middle text-center">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Jenis</th>
                        <th>Jumlah</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (mysqli_num_rows($result) > 0) {
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($result)) { ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['tanggal']) ?></td>
                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                <td><span class="badge <?= $row['jenis']=='masuk'?'bg-success':'bg-danger' ?>"><?= ucfirst($row['jenis']) ?></span></td>
                                <td>Rp <?= number_format($row['jumlah'], 0, ',', '.') ?></td>
                                <td><?= htmlspecialchars($row['keterangan']) ?></td>
                                <td>
                                    <a href="?edit_kas=<?= $row['id_transaksi'] ?>" class="btn btn-outline-primary btn-sm">Edit</a>
                                    <a href="?hapus_kas=<?= $row['id_transaksi'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Yakin ingin hapus?')">Hapus</a>
                                </td>
                            </tr>
                        <?php }
                    } else {
                        echo "<tr><td colspan='7' class='text-muted py-3'>Belum ada transaksi 🌷</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    <?php } else { ?>
        <!-- HALAMAN ANGGOTA -->
        <div class="card p-4 mb-4">
            <h5 class="mb-3 text-secondary fw-semibold"><?= $editAnggota ? '✏️ Edit Anggota' : 'Tambah Anggota Baru 👤' ?></h5>
            <form method="POST">
                <?php if ($editAnggota) { ?>
                    <input type="hidden" name="id_anggota" value="<?= $editAnggota['id_anggota'] ?>">
                <?php } ?>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Nama Anggota</label>
                        <input type="text" name="nama" class="form-control" placeholder="Masukkan nama anggota..." required value="<?= $editAnggota['nama'] ?? '' ?>">
                    </div>
                    <div class="col-md-4 text-end align-self-end">
                        <?php if ($editAnggota) { ?>
                            <button type="submit" name="update_anggota" class="btn btn-warning btn-custom"> Simpan Perubahan</button>
                            <a href="kas_database.php?page=anggota" class="btn btn-secondary btn-custom"> Batal</a>
                        <?php } else { ?>
                            <button type="submit" name="simpan_anggota" class="btn btn-primary btn-custom">➕ Tambah Anggota</button>
                        <?php } ?>
                    </div>
                </div>
            </form>
        </div>

        <div class="card p-4">
            <h5 class="mb-3 text-secondary fw-semibold">Daftar Anggota 👥</h5>
            <table class="table table-hover align-middle text-center">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    mysqli_data_seek($anggota, 0);
                    if (mysqli_num_rows($anggota) > 0) {
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($anggota)) { ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                <td>
                                    <a href="?edit_anggota=<?= $row['id_anggota'] ?>" class="btn btn-outline-primary btn-sm">Edit</a>
                                    <a href="?hapus_anggota=<?= $row['id_anggota'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Yakin ingin hapus anggota ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php }
                    } else {
                        echo "<tr><td colspan='3' class='text-muted py-3'>Belum ada anggota </td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    <?php } ?>

    <p class="text-center mt-4 text-muted"> <?= date('Y') ?> | Dibuat dengan cinta oleh <b>Hesyaa</b> 💜</p>
</div>
</body>
</html>
