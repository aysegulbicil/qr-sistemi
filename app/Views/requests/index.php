<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<?php $rb = static fn (string $s) => match ($s) { 'approved' => ['Onaylandı', 'badge-green'], 'rejected' => ['Reddedildi', 'badge-grey'], default => ['Bekliyor', 'badge-amber'] }; ?>
<h1>Taleplerim</h1>
<p class="muted" style="margin-top:-2px;margin-bottom:18px">İzin ve avans taleplerin</p>

<div class="profile-grid">
    <div class="card">
        <h2>İzin talebi</h2>
        <form method="post" action="<?= site_url('requests/leave') ?>">
            <?= csrf_field() ?>
            <div class="field"><label>İzin türü</label><select name="leave_type_id"><?php foreach ($types as $t): ?><option value="<?= $t['id'] ?>"><?= esc($t['name']) ?></option><?php endforeach; ?></select></div>
            <div class="grid2">
                <div class="field"><label>Başlangıç</label><input type="date" name="start_date" required></div>
                <div class="field"><label>Bitiş</label><input type="date" name="end_date" required></div>
            </div>
            <div class="field"><label>Açıklama</label><input type="text" name="reason"></div>
            <button class="btn btn-primary btn-sm">Talep oluştur</button>
        </form>
        <div style="margin-top:18px;border-top:1px solid var(--line);padding-top:6px">
            <?php if (empty($leaves)): ?><p class="empty">Henüz izin talebin yok.</p>
            <?php else: foreach ($leaves as $l): [$lbl, $cls] = $rb($l['status']); ?>
                <div class="info-row"><span class="txt"><b><?= esc($l['type_name'] ?: 'İzin') ?></b><span><?= esc(date('d.m.Y', strtotime($l['start_date']))) ?> – <?= esc(date('d.m.Y', strtotime($l['end_date']))) ?> · <?= (float) $l['days'] ?> gün</span></span><span class="badge <?= $cls ?>"><?= $lbl ?></span></div>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <div class="card">
        <h2>Avans talebi</h2>
        <form method="post" action="<?= site_url('requests/advance') ?>">
            <?= csrf_field() ?>
            <div class="field"><label>Tutar (₺)</label><input type="number" step="0.01" min="0" name="amount" required></div>
            <div class="field"><label>Açıklama</label><input type="text" name="reason"></div>
            <button class="btn btn-primary btn-sm">Talep oluştur</button>
        </form>
        <div style="margin-top:18px;border-top:1px solid var(--line);padding-top:6px">
            <?php if (empty($advances)): ?><p class="empty">Henüz avans talebin yok.</p>
            <?php else: foreach ($advances as $a): [$lbl, $cls] = $rb($a['status']); ?>
                <div class="info-row"><span class="txt"><b><?= esc(money((float) $a['amount'])) ?></b><span><?= esc($a['reason'] ?: '—') ?> · <?= esc(date('d.m.Y', strtotime($a['created_at']))) ?></span></span><span class="badge <?= $cls ?>"><?= $lbl ?></span></div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
