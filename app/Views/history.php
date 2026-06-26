<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<?php
$tLate = 0; $tOt = 0; $tWorked = 0; $present = 0;
foreach ($days as $d) {
    $tLate += $d['late_minutes']; $tOt += $d['overtime_minutes']; $tWorked += $d['worked_minutes'];
    if (in_array($d['status'], ['present', 'incomplete'], true)) { $present++; }
}
?>
<h1>Geçmişim</h1>
<p class="muted" style="margin-top:-2px;margin-bottom:18px">Son 14 gün</p>

<div class="tiles page-tiles">
    <div class="tile"><span class="box ic in"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4"/><path d="M16 2v4"/><path d="M3 10h18"/></svg></span><div><div class="v"><?= (int) $present ?></div><div class="l">Gelinen gün</div></div></div>
    <div class="tile"><span class="box ic late"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span><div><div class="v"><?= esc(minutes_to_hm((int) $tLate)) ?></div><div class="l">Toplam geç</div></div></div>
    <div class="tile"><span class="box ic ot"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v8"/><path d="M8 12h8"/></svg></span><div><div class="v"><?= esc(minutes_to_hm((int) $tOt)) ?></div><div class="l">Toplam fazla mesai</div></div></div>
    <div class="tile"><span class="box ic work"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M3 13h18"/></svg></span><div><div class="v"><?= esc(minutes_to_hm((int) $tWorked)) ?></div><div class="l">Toplam çalışılan</div></div></div>
</div>

<div class="card">
    <div class="table-scroll">
        <table class="data">
            <thead><tr><th>Tarih</th><th>Giriş</th><th>Çıkış</th><th>Geç</th><th>Fazla mesai</th><th>Çalışılan</th><th>Durum</th></tr></thead>
            <tbody>
            <?php foreach ($days as $date => $row): ?>
                <?php [$lbl, $cls] = status_label($row['status']); ?>
                <tr>
                    <td><?= esc(date('d.m.Y', strtotime($date))) ?></td>
                    <td><?= esc(hhmm($row['first_in'])) ?></td>
                    <td><?= esc(hhmm($row['last_out'])) ?></td>
                    <td><?= $row['late_minutes'] ? esc(minutes_to_hm((int) $row['late_minutes'])) : '—' ?></td>
                    <td><?= $row['overtime_minutes'] ? esc(minutes_to_hm((int) $row['overtime_minutes'])) : '—' ?></td>
                    <td><?= $row['worked_minutes'] ? esc(minutes_to_hm((int) $row['worked_minutes'])) : '—' ?></td>
                    <td><span class="badge <?= $cls ?>"><?= esc($lbl) ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
