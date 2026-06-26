<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<?php
[$slbl, $scls] = emp_status_badge($emp['employment_status'] ?? 'active');
$pos  = $emp['position_name'] ?? null;
$dept = $emp['department_name'] ?? null;
$meta = ($pos ?: 'Pozisyon atanmamış') . ($dept ? ' · ' . $dept : '');
?>
<p class="back-link"><a href="<?= site_url('admin/employees') ?>">&larr; Personeller</a></p>

<div class="card pad-lg">
    <div class="profile-head">
        <span class="avatar-lg"><?= esc(initials($emp['full_name'])) ?></span>
        <div>
            <div class="pname"><?= esc($emp['full_name']) ?></div>
            <div class="pmeta"><?= esc($meta) ?><?= $emp['employee_code'] ? ' · ' . esc($emp['employee_code']) : '' ?></div>
            <div style="margin-top:9px;display:flex;gap:7px;flex-wrap:wrap">
                <span class="badge <?= $scls ?>"><?= $slbl ?></span>
                <span class="badge <?= ($emp['role'] ?? '') === 'admin' ? 'badge-blue' : 'badge-grey' ?>"><?= ($emp['role'] ?? '') === 'admin' ? 'Yönetici' : 'Personel' ?></span>
            </div>
        </div>
        <div class="pactions"><a class="btn btn-outline" href="<?= site_url('admin/employees/' . $emp['id'] . '/edit') ?>">Düzenle</a></div>
    </div>
</div>

<h2>Bu ay</h2>
<div class="stat-grid" style="margin-bottom:22px">
    <div class="stat"><div class="ic in"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4"/><path d="M16 2v4"/><path d="M3 10h18"/></svg></div><div class="v"><?= (int) $present ?></div><div class="l">Gelinen gün</div></div>
    <div class="stat"><div class="ic late"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></div><div class="v"><?= esc(minutes_to_hm((int) $tLate)) ?></div><div class="l">Toplam geç</div></div>
    <div class="stat"><div class="ic ot"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v8"/><path d="M8 12h8"/></svg></div><div class="v"><?= esc(minutes_to_hm((int) $tOt)) ?></div><div class="l">Fazla mesai</div></div>
    <div class="stat"><div class="ic work"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M3 13h18"/></svg></div><div class="v"><?= esc(minutes_to_hm((int) $tWorked)) ?></div><div class="l">Çalışılan</div></div>
</div>

<div class="profile-grid">
    <div class="card">
        <h2>İletişim</h2>
        <div class="info-list">
            <div class="r"><span class="k">Telefon</span><span class="v"><?= esc($emp['phone'] ?: '—') ?></span></div>
            <div class="r"><span class="k">E-posta</span><span class="v"><?= esc($emp['contact_email'] ?: '—') ?></span></div>
            <div class="r"><span class="k">Adres</span><span class="v"><?= esc($emp['address'] ?: '—') ?></span></div>
        </div>
    </div>
    <div class="card">
        <h2>İş bilgileri</h2>
        <div class="info-list">
            <div class="r"><span class="k">Departman</span><span class="v"><?= esc($dept ?: '—') ?></span></div>
            <div class="r"><span class="k">Pozisyon</span><span class="v"><?= esc($pos ?: '—') ?></span></div>
            <div class="r"><span class="k">Çalışma durumu</span><span class="v"><?= $slbl ?></span></div>
            <div class="r"><span class="k">İşe giriş</span><span class="v"><?= esc(tr_date($emp['hire_date'] ?? null)) ?></span></div>
            <div class="r"><span class="k">Kullanıcı adı</span><span class="v"><?= esc($emp['username']) ?></span></div>
        </div>
    </div>
    <div class="card">
        <h2>Maaş</h2>
        <div class="info-list">
            <div class="r"><span class="k">Maaş tipi</span><span class="v"><?= esc(salary_type_label($emp['salary_type'] ?? 'monthly')) ?></span></div>
            <div class="r"><span class="k">Tutar</span><span class="v"><?= number_format((float) ($emp['salary_amount'] ?? 0), 2, ',', '.') ?> ₺</span></div>
            <div class="r"><span class="k">IBAN</span><span class="v"><?= esc($emp['iban'] ?: '—') ?></span></div>
            <div class="r"><span class="k">T.C. Kimlik</span><span class="v"><?= esc($emp['national_id'] ?: '—') ?></span></div>
        </div>
    </div>
    <div class="card">
        <h2>Son hareketler</h2>
        <?php if (empty($recent)): ?>
            <p class="empty">Bu ay hareket yok.</p>
        <?php else: ?>
            <?php foreach ($recent as $mv): ?>
                <div class="mv"><span class="mdot <?= $mv['type'] === 'in' ? 'in' : 'out' ?>"></span><span><?= $mv['type'] === 'in' ? 'Giriş' : 'Çıkış' ?></span><span class="mt"><?= esc(date('d.m H:i', strtotime($mv['event_at']))) ?></span></div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
