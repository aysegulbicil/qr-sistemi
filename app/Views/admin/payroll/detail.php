<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<?php $cur = $p['currency']; ?>
<p class="muted" style="margin-bottom:12px"><a href="<?= site_url('admin/payroll') . '?month=' . esc($ym) ?>">&larr; Puantaj</a></p>

<div class="card pad-lg">
    <div class="profile-head">
        <span class="avatar-lg"><?= esc(initials($u['full_name'])) ?></span>
        <div>
            <div class="pname"><?= esc($u['full_name']) ?></div>
            <div class="pmeta"><?= esc($monthStr) ?> · <?= esc(salary_type_label($p['salary_type'])) ?> maaş · <?= esc(money($p['salary_amount'], $cur)) ?></div>
        </div>
        <div class="pactions">
            <form method="get" action="<?= site_url('admin/payroll/' . $u['id']) ?>" style="margin:0">
                <input type="month" name="month" value="<?= esc($ym) ?>" onchange="this.form.submit()">
            </form>
        </div>
    </div>
</div>

<div class="stat-grid" style="margin-bottom:22px">
    <div class="stat"><div class="ic in"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4"/><path d="M16 2v4"/><path d="M3 10h18"/></svg></div><div class="v"><?= (int) $p['present_days'] ?>/<?= (int) $p['expected_days'] ?></div><div class="l">Gelinen gün</div></div>
    <div class="stat"><div class="ic late"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></div><div class="v"><?= esc(minutes_to_hm((int) $p['late_minutes'])) ?></div><div class="l">Toplam geç</div></div>
    <div class="stat"><div class="ic ot"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v8"/><path d="M8 12h8"/></svg></div><div class="v"><?= esc(minutes_to_hm((int) $p['overtime_minutes'])) ?></div><div class="l">Fazla mesai</div></div>
    <div class="stat"><div class="ic dng"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6"/><path d="M9 9l6 6"/></svg></div><div class="v"><?= (int) $p['missing_days'] ?></div><div class="l">Eksik gün</div></div>
</div>

<div class="profile-grid">
    <div class="card">
        <h2>Maaş kırılımı</h2>
        <div class="info-list">
            <div class="r"><span class="k">Baz ücret</span><span class="v"><?= esc(money($p['base'], $cur)) ?></span></div>
            <div class="r"><span class="k">Fazla mesai (<?= esc(minutes_to_hm((int) $p['overtime_minutes'])) ?> × <?= esc($p['overtime_mult']) ?>)</span><span class="v">+ <?= esc(money($p['overtime_pay'], $cur)) ?></span></div>
            <div class="r"><span class="k">Avans</span><span class="v">− <?= esc(money($p['advances_total'], $cur)) ?></span></div>
            <div class="r"><span class="k">Kesinti</span><span class="v">− <?= esc(money($p['deductions_total'], $cur)) ?></span></div>
            <div class="r" style="border-top:2px solid var(--line);margin-top:4px;padding-top:12px"><span class="k" style="color:var(--ink);font-weight:700">Net maaş</span><span class="v" style="font-size:1.15rem;color:var(--brand-700)"><?= esc(money($p['net'], $cur)) ?></span></div>
        </div>
    </div>

    <div class="card">
        <h2>Avans &amp; Kesinti</h2>
        <?php if (empty($advances)): ?>
            <p class="empty" style="padding:14px 0">Bu ay kayıt yok.</p>
        <?php else: ?>
            <?php foreach ($advances as $a): ?>
                <div class="info-row">
                    <span class="box ic <?= $a['type'] === 'advance' ? 'ot' : 'dng' ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 6H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></span>
                    <span class="txt"><b><?= $a['type'] === 'advance' ? 'Avans' : 'Kesinti' ?></b><span><?= esc($a['reason'] ?: '—') ?></span></span>
                    <span class="num">− <?= esc(money((float) $a['amount'], $cur)) ?></span>
                    <form method="post" action="<?= site_url('admin/payroll/' . $u['id'] . '/advance/' . $a['id'] . '/delete') ?>" style="margin-left:10px" onsubmit="return confirm('Silinsin mi?')"><?= csrf_field() ?><input type="hidden" name="month" value="<?= esc($ym) ?>"><button class="btn btn-link btn-sm" style="color:var(--dng-ink)">Sil</button></form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <form method="post" action="<?= site_url('admin/payroll/' . $u['id'] . '/advance') ?>" style="margin-top:16px;border-top:1px solid var(--line);padding-top:16px">
            <?= csrf_field() ?>
            <input type="hidden" name="month" value="<?= esc($ym) ?>">
            <div class="grid2">
                <div class="field"><label>Tür</label><select name="type"><option value="advance">Avans</option><option value="deduction">Kesinti</option></select></div>
                <div class="field"><label>Tutar (<?= esc($cur) ?>)</label><input type="number" step="0.01" min="0" name="amount" required></div>
            </div>
            <div class="field"><label>Açıklama</label><input type="text" name="reason"></div>
            <button class="btn btn-primary btn-sm">Ekle</button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
