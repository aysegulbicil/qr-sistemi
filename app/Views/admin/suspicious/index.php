<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<h1>Şüpheli işlemler</h1>
<p class="page-sub">Konum dışı veya doğrulanamayan giriş/çıkış denemeleri</p>
<div class="card">
    <?php if (empty($events)): ?>
        <?= view('partials/empty', ['title' => 'Şüpheli işlem yok', 'message' => 'Kayıtlı şüpheli işlem bulunmuyor.']) ?>
    <?php else: ?>
    <div class="table-scroll">
        <table class="data">
            <thead><tr><th>Tarih</th><th>Personel</th><th>Lokasyon</th><th>Neden</th><th class="num">Mesafe</th><th>Yön</th></tr></thead>
            <tbody>
            <?php foreach ($events as $e): ?>
                <tr>
                    <td class="muted"><?= esc(date('d.m.Y H:i', strtotime($e['created_at']))) ?></td>
                    <td><?= esc($e['full_name'] ?: '—') ?></td>
                    <td><?= esc($e['location_name'] ?: '—') ?></td>
                    <td><span class="badge badge-amber"><?= esc($e['reason']) ?></span></td>
                    <td class="num"><?= $e['distance_m'] !== null ? esc($e['distance_m']) . ' m' : '—' ?></td>
                    <td><?= $e['type'] === 'in' ? 'Giriş' : ($e['type'] === 'out' ? 'Çıkış' : '—') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
