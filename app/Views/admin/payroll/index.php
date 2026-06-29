<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<div class="card-head">
    <h1>Puantaj &amp; Maaş</h1>
    <form method="get" action="<?= site_url('admin/payroll') ?>" class="m0">
        <input type="month" name="month" value="<?= esc($ym) ?>" onchange="this.form.submit()">
    </form>
</div>
<p class="page-sub"><?= esc($monthStr) ?></p>

<div class="tiles page-tiles">
    <div class="tile"><span class="box ic in"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.2"/><path d="M3.5 20a5.5 5.5 0 0 1 11 0"/><path d="M16 5.2a3 3 0 0 1 0 5.6"/><path d="M19.8 20a5 5 0 0 0-3-4.6"/></svg></span><div><div class="v"><?= count($rows) ?></div><div class="l">Personel</div></div></div>
    <div class="tile"><span class="box ic work"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 6H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></span><div><div class="v"><?= esc(money($total, $currency)) ?></div><div class="l">Toplam net maaş</div></div></div>
</div>

<div class="card">
    <?php if (empty($rows)): ?>
        <?= view('partials/empty', ['title' => 'Personel yok', 'message' => 'Puantaj için önce personel ekle.', 'actionUrl' => site_url('admin/employees/new'), 'actionLabel' => '+ Yeni personel', 'actionModal' => true, 'actionTitle' => 'Yeni personel', 'actionSize' => 'lg']) ?>
    <?php else: ?>
    <div class="table-scroll">
        <table class="data">
            <thead><tr><th>Personel</th><th class="num">Gün</th><th class="num">Geç</th><th class="num">Fazla mesai</th><th class="num">Eksik</th><th class="num">Net maaş</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($rows as $row): $u = $row['user']; $p = $row['p']; ?>
                <tr>
                    <td>
                        <a class="cell-user" href="<?= site_url('admin/payroll/' . $u['id']) . '?month=' . esc($ym) ?>">
                            <span class="mini-avatar"><?= esc(initials($u['full_name'])) ?></span>
                            <span><strong><?= esc($u['full_name']) ?></strong><br><span class="muted-sm"><?= esc(salary_type_label($p['salary_type'])) ?> · <?= esc(money($p['salary_amount'], $p['currency'])) ?></span></span>
                        </a>
                    </td>
                    <td class="num"><?= (int) $p['present_days'] ?> / <?= (int) $p['expected_days'] ?></td>
                    <td class="num"><?= $p['late_minutes'] ? esc(minutes_to_hm((int) $p['late_minutes'])) : '—' ?></td>
                    <td class="num"><?= $p['overtime_minutes'] ? esc(minutes_to_hm((int) $p['overtime_minutes'])) : '—' ?></td>
                    <td class="num"><?= $p['missing_days'] ? '<span class="badge badge-amber">' . (int) $p['missing_days'] . ' gün</span>' : '—' ?></td>
                    <td class="num"><strong><?= esc(money($p['net'], $p['currency'])) ?></strong></td>
                    <td class="text-right"><a class="btn btn-outline btn-sm" href="<?= site_url('admin/payroll/' . $u['id']) . '?month=' . esc($ym) ?>">Detay</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
