<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<?php $qs = http_build_query(['type' => $type, 'start' => $start, 'end' => $end, 'dept' => $dept]); ?>
<div class="card-head">
    <h1>Raporlar</h1>
    <div style="display:flex;gap:8px">
        <a class="btn btn-outline btn-sm" href="<?= site_url('admin/reports/export') . '?' . $qs ?>">Excel (CSV)</a>
        <a class="btn btn-outline btn-sm" href="<?= site_url('admin/reports/print') . '?' . $qs ?>" target="_blank" rel="noopener">Yazdır / PDF</a>
    </div>
</div>

<form method="get" action="<?= site_url('admin/reports') ?>" class="filters">
    <select name="type" onchange="this.form.submit()">
        <?php foreach ($types as $k => $v): ?><option value="<?= $k ?>" <?= $type === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
    </select>
    <?php if ($type === 'daily'): ?>
        <input type="date" name="start" value="<?= esc($start) ?>" onchange="this.form.submit()">
        <input type="hidden" name="end" value="<?= esc($end) ?>">
    <?php else: ?>
        <input type="date" name="start" value="<?= esc($start) ?>"><span class="muted">—</span><input type="date" name="end" value="<?= esc($end) ?>">
    <?php endif; ?>
    <select name="dept" onchange="this.form.submit()">
        <option value="">Tüm departmanlar</option>
        <?php foreach ($departments as $d): ?><option value="<?= $d['id'] ?>" <?= (string) $dept === (string) $d['id'] ? 'selected' : '' ?>><?= esc($d['name']) ?></option><?php endforeach; ?>
    </select>
    <button class="btn btn-outline btn-sm" type="submit">Uygula</button>
</form>

<div class="card">
    <div class="card-head"><h2><?= esc($report['title']) ?> <span class="muted" style="font-weight:500;font-size:.85rem"><?= esc($report['subtitle']) ?></span></h2><span class="count-pill"><?= count($report['rows']) ?></span></div>
    <?php if (empty($report['rows'])): ?>
        <p class="empty">Bu kriterlerde kayıt bulunamadı.</p>
    <?php else: ?>
    <div class="table-scroll">
        <table class="data">
            <thead><tr><?php foreach ($report['columns'] as $c): ?><th><?= esc($c) ?></th><?php endforeach; ?></tr></thead>
            <tbody>
            <?php foreach ($report['rows'] as $r): ?>
                <tr>
                    <?php foreach ($r as $i => $cell): ?>
                        <?php if ($i === 1): ?>
                            <td><span class="cell-user"><span class="mini-avatar"><?= esc(initials((string) $cell)) ?></span> <?= esc((string) $cell) ?></span></td>
                        <?php else: ?>
                            <td><?= esc((string) $cell) ?></td>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
