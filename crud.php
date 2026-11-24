<?php
require 'config.php';

$tables = [
    'pasien' => [
        'title' => 'Data Pasien', 'single' => 'Pasien',
        'fields' => [
            'nama' => ['label' => 'Nama Lengkap', 'type' => 'text', 'required' => true],
            'nik' => ['label' => 'NIK', 'type' => 'text', 'required' => true],
            'jenis_kelamin' => ['label' => 'Jenis Kelamin', 'type' => 'select', 'options' => ['Laki-laki', 'Perempuan'], 'required' => true],
            'tanggal_lahir' => ['label' => 'Tanggal Lahir', 'type' => 'date', 'required' => true],
            'alamat' => ['label' => 'Alamat', 'type' => 'textarea', 'required' => true],
            'no_telp' => ['label' => 'No. Telepon', 'type' => 'text'],
        ],
    ],
    'dokter' => [
        'title' => 'Data Dokter', 'single' => 'Dokter',
        'fields' => [
            'nama' => ['label' => 'Nama Dokter', 'type' => 'text', 'required' => true],
            'spesialis' => ['label' => 'Spesialis', 'type' => 'text', 'required' => true],
            'no_sip' => ['label' => 'Nomor SIP', 'type' => 'text', 'required' => true],
            'no_telp' => ['label' => 'No. Telepon', 'type' => 'text'],
        ],
    ],
    'ruangan' => [
        'title' => 'Data Ruangan', 'single' => 'Ruangan',
        'fields' => [
            'nama_ruangan' => ['label' => 'Nama Ruangan', 'type' => 'text', 'required' => true],
            'lantai' => ['label' => 'Lantai', 'type' => 'text', 'required' => true],
            'kelas' => ['label' => 'Kelas', 'type' => 'text', 'required' => true],
            'kapasitas' => ['label' => 'Kapasitas', 'type' => 'number', 'required' => true],
            'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['Tersedia', 'Terisi', 'Perawatan'], 'required' => true],
        ],
    ],
    'obat' => [
        'title' => 'Data Obat', 'single' => 'Obat',
        'fields' => [
            'nama_obat' => ['label' => 'Nama Obat', 'type' => 'text', 'required' => true],
            'jenis' => ['label' => 'Jenis', 'type' => 'text', 'required' => true],
            'satuan' => ['label' => 'Satuan', 'type' => 'text', 'required' => true],
            'stok' => ['label' => 'Stok', 'type' => 'number', 'required' => true],
            'harga' => ['label' => 'Harga', 'type' => 'number', 'required' => true, 'step' => '0.01'],
        ],
    ],
    'janji_temu' => [
        'title' => 'Data Janji Temu', 'single' => 'Janji Temu',
        'fields' => [
            'pasien_id' => ['label' => 'Pasien', 'type' => 'relation', 'relation' => ['pasien', 'nama'], 'required' => true],
            'dokter_id' => ['label' => 'Dokter', 'type' => 'relation', 'relation' => ['dokter', 'nama'], 'required' => true],
            'tanggal_appointment' => ['label' => 'Tanggal', 'type' => 'date', 'required' => true],
            'jam_appointment' => ['label' => 'Jam', 'type' => 'time', 'required' => true],
            'keluhan' => ['label' => 'Keluhan', 'type' => 'textarea'],
            'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['Menunggu', 'Diperiksa', 'Selesai', 'Batal'], 'required' => true],
        ],
    ],
];

$table = $_GET['table'] ?? 'pasien';
if (!isset($tables[$table])) {
    header('Location: index.php');
    exit;
}
$meta = $tables[$table];
$message = '';
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isEdit = (int)($_POST['id'] ?? 0) > 0;
    $columns = array_keys($meta['fields']);
    $values = [];
    foreach ($columns as $column) {
        $values[$column] = trim($_POST[$column] ?? '');
    }

    try {
        if ($isEdit) {
            $set = implode(', ', array_map(fn($c) => "`$c` = :$c", $columns));
            $stmt = $pdo->prepare("UPDATE `$table` SET $set WHERE id = :id");
            $stmt->execute(array_merge($values, ['id' => (int)$_POST['id']]));
            $message = $meta['single'] . ' berhasil diperbarui.';
        } else {
            $placeholders = implode(', ', array_map(fn($c) => ":$c", $columns));
            $columnNames = implode(', ', array_map(fn($c) => "`$c`", $columns));
            $stmt = $pdo->prepare("INSERT INTO `$table` ($columnNames) VALUES ($placeholders)");
            $stmt->execute($values);
            $message = $meta['single'] . ' berhasil ditambahkan.';
        }
    } catch (PDOException $e) {
        $message = 'Gagal menyimpan data: ' . $e->getMessage();
    }
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM `$table` WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header('Location: crud.php?table=' . urlencode($table) . '&deleted=1');
    exit;
}
if (isset($_GET['deleted'])) {
    $message = $meta['single'] . ' berhasil dihapus.';
}

$editRow = null;
if ($editId) {
    $stmt = $pdo->prepare("SELECT * FROM `$table` WHERE id = ?");
    $stmt->execute([$editId]);
    $editRow = $stmt->fetch();
}

$search = trim($_GET['q'] ?? '');
$where = '';
$params = [];
if ($search !== '') {
    $columns = array_keys($meta['fields']);
    $textColumns = array_filter($columns, fn($c) => !str_ends_with($c, '_id'));
    $like = [];
    foreach ($textColumns as $column) {
        $like[] = "`$column` LIKE ?";
        $params[] = "%$search%";
    }
    if ($like) $where = ' WHERE ' . implode(' OR ', $like);
}
$rows = $pdo->prepare("SELECT * FROM `$table`$where ORDER BY id DESC");
$rows->execute($params);
$rows = $rows->fetchAll();

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($meta['title']) ?> - Rumah Sakit Sehat</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <aside class="sidebar">
    <div class="brand">
      <div class="brand-icon">+</div>
      <div><strong>Sehat</strong><span>Rumah Sakit</span></div>
    </div>
    <nav>
      <a href="index.php"><span class="nav-icon">&#9638;</span> Dashboard</a>
      <?php foreach ($tables as $key => $item): ?>
        <a class="<?= $table === $key ? 'active' : '' ?>" href="crud.php?table=<?= h($key) ?>"><span class="nav-icon">&#9679;</span> <?= h($item['title']) ?></a>
      <?php endforeach; ?>
    </nav>
  </aside>
  <main>
    <header>
      <div>
        <p class="eyebrow">DATA RUMAH SAKIT</p>
        <h1><?= h($meta['title']) ?></h1>
      </div>
      <a class="button secondary" href="index.php">Kembali</a>
    </header>

    <?php if ($message): ?><div class="alert"><?= h($message) ?></div><?php endif; ?>

    <section class="crud-layout">
      <div class="panel form-panel">
        <h2><?= $editRow ? 'Ubah ' : 'Tambah ' ?><?= h($meta['single']) ?></h2>
        <form method="post">
          <input type="hidden" name="id" value="<?= $editRow['id'] ?? 0 ?>">
          <?php foreach ($meta['fields'] as $name => $field): ?>
            <label>
              <?= h($field['label']) ?>
              <?php
              $value = $editRow[$name] ?? '';
              $required = !empty($field['required']) ? 'required' : '';
              ?>
              <?php if ($field['type'] === 'textarea'): ?>
                <textarea name="<?= h($name) ?>" <?= $required ?>><?= h($value) ?></textarea>
              <?php elseif ($field['type'] === 'select'): ?>
                <select name="<?= h($name) ?>" <?= $required ?>>
                  <?php foreach ($field['options'] as $option): ?>
                    <option <?= $value === $option ? 'selected' : '' ?>><?= h($option) ?></option>
                  <?php endforeach; ?>
                </select>
              <?php elseif ($field['type'] === 'relation'): ?>
                <select name="<?= h($name) ?>" <?= $required ?>>
                  <option value="">-- Pilih <?= h($field['label']) ?> --</option>
                  <?php
                  [$relationTable, $relationName] = $field['relation'];
                  $relationRows = $pdo->query("SELECT id, `$relationName` FROM `$relationTable` ORDER BY `$relationName`")->fetchAll();
                  foreach ($relationRows as $relation):
                  ?>
                    <option value="<?= h($relation['id']) ?>" <?= (string)$value === (string)$relation['id'] ? 'selected' : '' ?>><?= h($relation[$relationName]) ?></option>
                  <?php endforeach; ?>
                </select>
              <?php else: ?>
                <input type="<?= h($field['type']) ?>" name="<?= h($name) ?>" value="<?= h($value) ?>" <?= $required ?> <?= isset($field['step']) ? 'step="' . h($field['step']) . '"' : '' ?>>
              <?php endif; ?>
            </label>
          <?php endforeach; ?>
          <button class="button" type="submit"><?= $editRow ? 'Simpan Perubahan' : 'Tambah Data' ?></button>
        </form>
      </div>

      <div class="panel table-panel">
        <div class="table-toolbar">
          <h2>Daftar <?= h($meta['single']) ?></h2>
          <form method="get">
            <input type="hidden" name="table" value="<?= h($table) ?>">
            <input class="search" type="search" name="q" value="<?= h($search) ?>" placeholder="Cari data...">
          </form>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <?php foreach ($meta['fields'] as $field): ?><th><?= h($field['label']) ?></th><?php endforeach; ?>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <?php foreach ($meta['fields'] as $name => $field): ?>
                    <td>
                      <?php
                      $display = $row[$name];
                      if (($field['type'] ?? '') === 'relation') {
                          [$relationTable, $relationName] = $field['relation'];
                          $stmt = $pdo->prepare("SELECT `$relationName` FROM `$relationTable` WHERE id = ?");
                          $stmt->execute([$display]);
                          $display = $stmt->fetchColumn() ?: '-';
                      } elseif (($field['type'] ?? '') === 'number' && $name === 'harga') {
                          $display = 'Rp ' . number_format((float)$display, 0, ',', '.');
                      }
                      echo h($display);
                      ?>
                    </td>
                  <?php endforeach; ?>
                  <td class="actions">
                    <a class="action edit" href="?table=<?= h($table) ?>&edit=<?= (int)$row['id'] ?>">Edit</a>
                    <a class="action delete" href="?table=<?= h($table) ?>&delete=<?= (int)$row['id'] ?>" onclick="return confirm('Hapus data ini?')">Hapus</a>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$rows): ?><tr><td colspan="<?= count($meta['fields']) + 1 ?>" class="empty">Belum ada data.</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </main>
</body>
</html>
