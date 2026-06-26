<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<?php
$trDays  = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'];
$trMon   = [1 => 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
$nowTs   = time();
$dateStr = $trDays[(int) date('w', $nowTs)] . ', ' . date('j', $nowTs) . ' ' . $trMon[(int) date('n', $nowTs)] . ' ' . date('Y', $nowTs);

// Katılım oranı (gerçek veriden) — donut + başlık
$rate = $activeCount > 0 ? (int) round($presentCount / $activeCount * 100) : 0;
$circ = 263.9;                 // 2π·42
$arc  = round($circ * $rate / 100, 1);
$ring = $rate >= 90 ? '#047857' : ($rate >= 70 ? '#2563eb' : '#b45309');
?>
<div class="dash-head">
    <div>
        <h1>Genel Bakış</h1>
        <p class="page-sub"><?= esc($dateStr) ?></p>
    </div>
</div>

<!-- ░░ Birincil: bugünün özeti ░░ -->
<div class="card summary-band">
    <div class="attn">
        <div class="attn-ring">
            <svg viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="42" fill="none" stroke="var(--line)" stroke-width="12"/>
                <?php if ($rate > 0): ?><circle cx="50" cy="50" r="42" fill="none" stroke="<?= $ring ?>" stroke-width="12" stroke-linecap="round" stroke-dasharray="<?= $arc ?> <?= round($circ - $arc, 1) ?>"/><?php endif; ?>
            </svg>
            <div class="pct" style="color:<?= $ring ?>"><?= $rate ?>%</div>
        </div>
        <div class="attn-meta">
            <div class="big"><b><?= (int) $presentCount ?></b> / <?= (int) $activeCount ?></div>
            <div class="lbl">çalışan bugün geldi</div>
            <div class="attn-legend">
                <span><i class="g"></i><?= (int) $presentCount ?> geldi</span>
                <span><i class="n"></i><?= (int) $absentCount ?> gelmeyen</span>
            </div>
        </div>
    </div>
    <div class="summary-divider"></div>
    <div class="summary-kpis">
        <div class="skpi">
            <span class="box ic late"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
            <div><div class="v"><?= (int) $lateCount ?></div><div class="l">Geç gelen</div></div>
        </div>
        <div class="skpi">
            <span class="box ic ot"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21V9"/><path d="M8 13l4-4 4 4"/><path d="M4 3h16"/></svg></span>
            <div><div class="v"><?= (int) $earlyCount ?></div><div class="l">Erken çıkan</div></div>
        </div>
        <a class="skpi" href="<?= site_url('admin/requests') ?>">
            <span class="box ic work"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 14l2 2 4-4"/></svg></span>
            <div><div class="v"><?= (int) $pendingTotal ?></div><div class="l">Bekleyen talep</div></div>
        </a>
    </div>
</div>

<!-- ░░ İkincil: haftalık hareket + bekleyen talepler ░░ -->
<div class="split-2">
    <div class="info-card">
        <div class="ic-head"><span class="box ic ot"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 14l3-3 3 3 4-5"/></svg></span><span class="ttl">Haftalık hareket</span></div>
        <div class="chart-box"><canvas id="wkChart"></canvas></div>
    </div>
    <div class="info-card">
        <div class="ic-head"><span class="box ic ot"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg></span><span class="ttl">Bekleyen talepler</span></div>
        <?php if ($pendingTotal === 0): ?>
            <div class="empty">Bekleyen talep yok.</div>
        <?php else: ?>
            <?php foreach (array_slice($pendingLeaves, 0, 4) as $l): ?>
                <div class="info-row"><span class="box ic ot"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4"/><path d="M16 2v4"/><path d="M3 10h18"/></svg></span><span class="txt"><b><?= esc($l['full_name']) ?></b><span>İzin · <?= (float) $l['days'] ?> gün</span></span></div>
            <?php endforeach; ?>
            <?php foreach (array_slice($pendingAdvances, 0, 4) as $a): ?>
                <div class="info-row"><span class="box ic ot"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 6H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></span><span class="txt"><b><?= esc($a['full_name']) ?></b><span>Avans · <?= esc(money((float) $a['amount'])) ?></span></span></div>
            <?php endforeach; ?>
            <a class="btn btn-outline btn-sm mt-12" href="<?= site_url('admin/requests') ?>">Tümünü gör</a>
        <?php endif; ?>
    </div>
</div>

<!-- ░░ Üçüncül: detaylar ░░ -->
<div class="section-label">Detaylar</div>
<div class="dash-grid">
    <div class="info-card">
        <div class="ic-head"><span class="box ic in"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 12h10"/><path d="M16 8l4 4-4 4"/><path d="M12 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h6"/></svg></span><span class="ttl">Bugünkü Girişler</span></div>
        <div class="info-row"><span class="box ic ot"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v7h7v-7h-4"/></svg></span><span class="txt"><b>QR ile giriş</b></span><span class="num"><?= (int) $inQr ?></span></div>
        <div class="info-row"><span class="box ic ot"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></span><span class="txt"><b>Manuel giriş</b></span><span class="num"><?= (int) $inManual ?></span></div>
        <div class="info-row"><span class="box ic late"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span><span class="txt"><b>Geç gelenler</b></span><span class="num"><?= (int) $lateCount ?></span></div>
        <div class="info-row total"><span class="box ic in"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12"/><path d="M8 11l4 4 4-4"/><path d="M4 21h16"/></svg></span><span class="txt"><b>Toplam giriş</b></span><span class="num"><?= (int) $totalIn ?></span></div>
    </div>

    <div class="info-card">
        <div class="ic-head"><span class="box ic dng"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 12H4"/><path d="M8 8l-4 4 4 4"/><path d="M12 4h6a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-6"/></svg></span><span class="ttl">Bugünkü Çıkışlar</span></div>
        <div class="info-row"><span class="box ic ot"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v7h7v-7h-4"/></svg></span><span class="txt"><b>QR ile çıkış</b></span><span class="num"><?= (int) $outQr ?></span></div>
        <div class="info-row"><span class="box ic ot"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></span><span class="txt"><b>Manuel çıkış</b></span><span class="num"><?= (int) $outManual ?></span></div>
        <div class="info-row"><span class="box ic late"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span><span class="txt"><b>Erken çıkanlar</b></span><span class="num"><?= (int) $earlyCount ?></span></div>
        <div class="info-row total"><span class="box ic dng"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21V9"/><path d="M8 13l4-4 4 4"/><path d="M4 3h16"/></svg></span><span class="txt"><b>Toplam çıkış</b></span><span class="num"><?= (int) $totalOut ?></span></div>
    </div>

    <div class="info-card">
        <div class="ic-head"><span class="box ic late"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span><span class="ttl">Geç Gelenler</span></div>
        <div class="people">
            <?php if (empty($lateList)): ?><div class="empty">Bugün geç gelen yok.</div>
            <?php else: foreach ($lateList as $p): ?>
                <div class="p"><span class="mini-avatar"><?= esc(initials($p['name'])) ?></span><span class="who"><b><?= esc($p['name']) ?></b><span><?= esc(minutes_to_hm((int) $p['mins'])) ?> geç</span></span><span class="time-chip red"><?= esc(hhmm($p['time'])) ?></span></div>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <div class="info-card">
        <div class="ic-head"><span class="box ic ot"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21V9"/><path d="M8 13l4-4 4 4"/><path d="M4 3h16"/></svg></span><span class="ttl">Erken Çıkanlar</span></div>
        <div class="people">
            <?php if (empty($earlyList)): ?><div class="empty">Bugün erken çıkan yok.</div>
            <?php else: foreach ($earlyList as $p): ?>
                <div class="p"><span class="mini-avatar"><?= esc(initials($p['name'])) ?></span><span class="who"><b><?= esc($p['name']) ?></b><span><?= esc(minutes_to_hm((int) $p['mins'])) ?> erken</span></span><span class="time-chip amber"><?= esc(hhmm($p['time'])) ?></span></div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>

<div class="card">
    <h2>Son hareketler</h2>
    <?php if (empty($recent)): ?>
        <p class="empty">Bugün hareket yok.</p>
    <?php else: ?>
        <?php foreach ($recent as $mv): ?>
            <div class="mv"><span class="mdot <?= $mv['type'] === 'in' ? 'in' : 'out' ?>"></span><span><strong><?= esc($mv['full_name'] ?: '—') ?></strong> · <?= $mv['type'] === 'in' ? 'Giriş' : 'Çıkış' ?></span><span class="mt"><?= esc(date('d.m H:i', strtotime($mv['event_at']))) ?></span></div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    var el = document.getElementById('wkChart');
    if (!el || !window.Chart) { return; }
    new Chart(el, {
        type: 'bar',
        data: { labels: <?= $chartLabels ?>, datasets: [
            { label: 'Giriş', data: <?= $chartIn ?>, backgroundColor: '#2563eb', borderRadius: 6, maxBarThickness: 22 },
            { label: 'Çıkış', data: <?= $chartOut ?>, backgroundColor: '#cbd5e1', borderRadius: 6, maxBarThickness: 22 }
        ]},
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } }, x: { grid: { display: false } } } }
    });
})();
</script>
<?= $this->endSection() ?>
