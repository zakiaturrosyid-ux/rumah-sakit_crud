<?php
require 'config.php';

$counts = [];
foreach (['pasien', 'dokter', 'ruangan', 'obat', 'janji_temu'] as $table) {
    $counts[$table] = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
}

$cards = [
    ['pasien', 'Pasien', 'Kelola data pasien', 'users', $counts['pasien']],
    ['dokter', 'Dokter', 'Kelola data dokter', 'doctor', $counts['dokter']],
    ['ruangan', 'Ruangan', 'Kelola ruangan rumah sakit', 'room', $counts['ruangan']],
    ['obat', 'Obat', 'Kelola stok dan data obat', 'medication', $counts['obat']],
    ['janji_temu', 'Janji Temu', 'Kelola jadwal kunjungan', 'calendar', $counts['janji_temu']],
];
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Rumah Sakit Sehat - Dashboard</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <aside class="sidebar">
    <div class="brand">
      <div class="brand-icon">+</div>
      <div><strong>Sehat</strong><span>Rumah Sakit</span></div>
    </div>
    <nav>
      <a class="active" href="index.php"><span class="nav-icon">&#9638;</span> Dashboard</a>
      <?php foreach ($cards as $card): ?>
        <a href="crud.php?table=<?= $card[0] ?>"><span class="nav-icon">&#9679;</span> <?= htmlspecialchars($card[1]) ?></a>
      <?php endforeach; ?>
    </nav>
  </aside>
  <main>
    <header>
      <div>
        <p class="eyebrow">SISTEM INFORMASI</p>
        <h1>Dashboard Rumah Sakit</h1>
      </div>
      <div class="date"><?= date('d F Y') ?></div>
    </header>

    <section class="stats">
      <?php foreach ($cards as $card): ?>
        <a class="stat-card" href="crud.php?table=<?= $card[0] ?>">
          <div class="stat-icon <?= $card[0] ?>"><?= strtoupper(substr($card[1], 0, 1)) ?></div>
          <div>
            <span><?= htmlspecialchars($card[1]) ?></span>
            <strong><?= (int)$card[4] ?></strong>
            <small><?= htmlspecialchars($card[2]) ?></small>
          </div>
        </a>
      <?php endforeach; ?>
    </section>

    <section class="panel">
      <h2>Janji Temu Terbaru</h2>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Pasien</th><th>Dokter</th><th>Tanggal</th><th>Jam</th><th>Status</th></tr></thead>
          <tbody>
          <?php
          $appointments = $pdo->query("
            SELECT j.*, p.nama AS nama_pasien, d.nama AS nama_dokter
            FROM janji_temu j
            JOIN pasien p ON p.id = j.pasien_id
            JOIN dokter d ON d.id = j.dokter_id
            ORDER BY j.tanggal_appointment DESC, j.jam_appointment DESC
            LIMIT 5
          ");
          foreach ($appointments as $item):
          ?>
            <tr>
              <td><?= htmlspecialchars($item['nama_pasien']) ?></td>
              <td><?= htmlspecialchars($item['nama_dokter']) ?></td>
              <td><?= date('d/m/Y', strtotime($item['tanggal_appointment'])) ?></td>
              <td><?= substr($item['jam_appointment'], 0, 5) ?></td>
              <td><span class="badge"><?= htmlspecialchars($item['status']) ?></span></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</body>
</html>
