<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<?php $dayNames = [1 => 'Pazartesi', 2 => 'Salı', 3 => 'Çarşamba', 4 => 'Perşembe', 5 => 'Cuma', 6 => 'Cumartesi', 7 => 'Pazar']; ?>
<div class="card-head">
    <h1>Haftalık vardiya planı</h1>
    <a class="btn btn-outline" href="<?= site_url('admin/shifts') ?>">Vardiyalar</a>
</div>

<form method="get" action="<?= site_url('admin/shift-schedule') ?>" class="filters">
    <select name="user_id" onchange="this.form.submit()">
        <option value="">Personel seç...</option>
        <?php foreach ($employees as $e): ?><option value="<?= $e['id'] ?>" <?= $userId === (int) $e['id'] ? 'selected' : '' ?>><?= esc($e['full_name']) ?></option><?php endforeach; ?>
    </select>
    <input type="hidden" name="week" value="<?= esc($weekStart) ?>">
    <a class="btn btn-outline btn-sm" href="<?= site_url('admin/shift-schedule') . '?user_id=' . $userId . '&week=' . $prevWeek ?>">‹ Önceki</a>
    <span class="muted"><?= esc(date('d.m.Y', strtotime($weekStart))) ?> haftası</span>
    <a class="btn btn-outline btn-sm" href="<?= site_url('admin/shift-schedule') . '?user_id=' . $userId . '&week=' . $nextWeek ?>">Sonraki ›</a>
</form>

<?php if (! $userId): ?>
    <div class="card"><?= view('partials/empty', ['title' => 'Personel seç', 'message' => 'Plan görüntülemek için yukarıdan bir personel seç.']) ?></div>
<?php elseif (empty($shifts)): ?>
    <div class="card"><?= view('partials/empty', ['title' => 'Önce vardiya tanımla', 'message' => 'Plan yapabilmek için en az bir vardiya gerekli.', 'actionUrl' => site_url('admin/shifts/new'), 'actionLabel' => '+ Yeni vardiya', 'actionModal' => true, 'actionTitle' => 'Yeni vardiya']) ?></div>
<?php else: ?>
<form method="post" action="<?= site_url('admin/shift-schedule') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="user_id" value="<?= $userId ?>">
    <input type="hidden" name="week" value="<?= esc($weekStart) ?>">
    <div class="card">
        <div class="table-scroll">
            <table class="data">
                <thead><tr><th>Gün</th><th>Tarih</th><th>Vardiya</th></tr></thead>
                <tbody>
                <?php foreach ($days as $i => $date): $cur = $assignments[$date]['shift_id'] ?? ''; ?>
                    <tr>
                        <td><strong><?= $dayNames[$i + 1] ?></strong></td>
                        <td class="muted"><?= esc(date('d.m.Y', strtotime($date))) ?></td>
                        <td>
                            <select name="shift[<?= $date ?>]" style="min-width:220px">
                                <option value="">— atama yok —</option>
                                <?php foreach ($shifts as $s): ?><option value="<?= $s['id'] ?>" <?= (string) $cur === (string) $s['id'] ? 'selected' : '' ?>><?= esc($s['name']) ?> (<?= substr($s['start_time'], 0, 5) ?>–<?= substr($s['end_time'], 0, 5) ?>)</option><?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <button class="btn btn-primary">Planı kaydet</button>
</form>
<?php endif; ?>
<?= $this->endSection() ?>
