<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<?php
$statusOpts = ['' => 'Tüm durumlar', 'active' => 'Aktif', 'passive' => 'Pasif', 'terminated' => 'Ayrıldı'];
$base       = ['q' => $q, 'department_id' => $dept, 'status' => $status];
$sortUrl    = static function (string $col) use ($base, $sort, $dir) {
    $d = ($sort === $col && strtolower($dir) === 'asc') ? 'desc' : 'asc';
    $params = array_filter(array_merge($base, ['sort' => $col, 'dir' => $d]), static fn ($v) => $v !== null && $v !== '');
    return site_url('admin/employees') . '?' . http_build_query($params);
};
$arrow = static fn (string $col) => $sort === $col ? (strtolower($dir) === 'asc' ? '▲' : '▼') : '';
$hasFilter = ($q !== '' || $dept || $status !== '');
?>
<div class="card-head">
    <h1>Personeller <span class="count-pill"><?= count($rows) ?></span></h1>
    <a class="btn btn-primary" href="<?= site_url('admin/employees/new') ?>">+ Yeni personel</a>
</div>

<form method="get" action="<?= site_url('admin/employees') ?>" class="filters">
    <span class="search-box">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
        <input type="text" name="q" value="<?= esc($q) ?>" placeholder="Ad, kod veya kullanıcı adı ara...">
    </span>
    <select name="department_id" onchange="this.form.submit()">
        <option value="">Tüm departmanlar</option>
        <?php foreach ($departments as $d): ?>
            <option value="<?= $d['id'] ?>" <?= (string) $dept === (string) $d['id'] ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="status" onchange="this.form.submit()">
        <?php foreach ($statusOpts as $k => $v): ?>
            <option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= $v ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn-outline btn-sm" type="submit">Ara</button>
    <?php if ($hasFilter): ?><a class="btn btn-link btn-sm" href="<?= site_url('admin/employees') ?>">Temizle</a><?php endif; ?>
</form>

<div class="card">
    <?php if (empty($rows)): ?>
        <?= view('partials/empty', [
            'title'       => $hasFilter ? 'Sonuç bulunamadı' : 'Henüz personel yok',
            'message'     => $hasFilter ? 'Arama veya filtreleri değiştirmeyi dene.' : 'İlk personelini ekleyerek başla.',
            'actionUrl'   => $hasFilter ? null : site_url('admin/employees/new'),
            'actionLabel' => '+ Yeni personel',
        ]) ?>
    <?php else: ?>
    <div class="table-scroll">
        <table class="data">
            <thead><tr>
                <th><a class="sort-link" href="<?= $sortUrl('full_name') ?>">Personel <span class="arrow"><?= $arrow('full_name') ?></span></a></th>
                <th><a class="sort-link" href="<?= $sortUrl('department_name') ?>">Departman <span class="arrow"><?= $arrow('department_name') ?></span></a></th>
                <th><a class="sort-link" href="<?= $sortUrl('position_name') ?>">Pozisyon <span class="arrow"><?= $arrow('position_name') ?></span></a></th>
                <th><a class="sort-link" href="<?= $sortUrl('employment_status') ?>">Durum <span class="arrow"><?= $arrow('employment_status') ?></span></a></th>
                <th></th>
            </tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <?php [$slbl, $scls] = emp_status_badge($r['employment_status'] ?? 'active'); ?>
                <tr>
                    <td>
                        <a class="cell-user" href="<?= site_url('admin/employees/' . $r['id']) ?>" style="color:inherit">
                            <span class="mini-avatar"><?= esc(initials($r['full_name'])) ?></span>
                            <span><strong><?= esc($r['full_name']) ?></strong><br><span class="muted" style="font-size:.78rem"><?= esc($r['employee_code'] ?: $r['username']) ?></span></span>
                        </a>
                    </td>
                    <td><?= $r['department_name'] ? esc($r['department_name']) : '<span class="muted">—</span>' ?></td>
                    <td><?= $r['position_name'] ? esc($r['position_name']) : '<span class="muted">—</span>' ?></td>
                    <td><span class="badge <?= $scls ?>"><?= $slbl ?></span></td>
                    <td style="text-align:right;white-space:nowrap">
                        <a href="<?= site_url('admin/employees/' . $r['id']) ?>">Profil</a>
                        <a href="<?= site_url('admin/employees/' . $r['id'] . '/edit') ?>" style="margin-left:12px">Düzenle</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
