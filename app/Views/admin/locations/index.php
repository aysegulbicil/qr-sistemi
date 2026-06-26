<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<div class="card-head">
    <h1>Lokasyonlar <span class="count-pill"><?= count($locations) ?></span></h1>
    <a class="btn btn-primary" href="<?= site_url('admin/locations/new') ?>">+ Yeni lokasyon</a>
</div>
<div class="card">
    <?php if (empty($locations)): ?>
        <?= view('partials/empty', ['title' => 'Henüz lokasyon yok', 'message' => 'İlk lokasyonunu ekle.', 'actionUrl' => site_url('admin/locations/new'), 'actionLabel' => '+ Yeni lokasyon']) ?>
    <?php else: ?>
    <div class="table-scroll">
        <table class="data">
            <thead><tr><th>Lokasyon</th><th>Kod</th><th>QR modu</th><th>Konum</th><th>Durum</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($locations as $l): ?>
                <tr>
                    <td><span class="loc-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s7-5.6 7-11a7 7 0 1 0-14 0c0 5.4 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg></span><?= esc($l['name']) ?></td>
                    <td><code><?= esc($l['code']) ?></code></td>
                    <td><span class="badge <?= $l['qr_mode'] === 'dynamic' ? 'badge-blue' : 'badge-grey' ?>"><?= $l['qr_mode'] === 'dynamic' ? 'Dinamik' : 'Sabit' ?></span></td>
                    <td><?php if (! empty($l['enforce_geo'])): ?><span class="badge badge-green">GPS zorunlu</span><?php elseif ($l['geo_lat'] !== null): ?><span class="badge badge-grey">GPS var</span><?php else: ?><span class="muted">—</span><?php endif; ?></td>
                    <td><span class="badge <?= $l['is_active'] ? 'badge-green' : 'badge-grey' ?>"><?= $l['is_active'] ? 'Aktif' : 'Pasif' ?></span></td>
                    <td style="text-align:right;white-space:nowrap">
                        <a href="<?= site_url('admin/locations/' . $l['id'] . '/qr') ?>">QR</a>
                        <a href="<?= site_url('admin/locations/' . $l['id'] . '/edit') ?>" style="margin-left:10px">Düzenle</a>
                        <form method="post" action="<?= site_url('admin/locations/' . $l['id'] . '/delete') ?>" style="display:inline;margin-left:8px" onsubmit="return confirm('Bu lokasyon silinsin mi?')"><?= csrf_field() ?><button class="btn btn-danger-soft btn-sm">Sil</button></form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
