<?php
$loggedIn = (bool) session()->get('user_id');
$cur      = function_exists('uri_string') ? uri_string() : '';
$role     = session()->get('role');
$name     = (string) session()->get('full_name');
$ini      = function_exists('initials') ? initials($name) : 'K';
$uid        = (int) session()->get('user_id');
$unread     = $loggedIn ? (new \App\Models\NotificationModel())->unreadCount($uid) : 0;
$pendingReq = ($loggedIn && $role === 'admin') ? (count((new \App\Models\LeaveRequestModel())->pending()) + count((new \App\Models\AdvanceRequestModel())->pending())) : 0;
$openTasks  = 0;
if ($loggedIn && $role !== 'admin') {
    try { $openTasks = (new \App\Models\TaskModel())->countOpenForUser($uid); } catch (\Throwable $e) { $openTasks = 0; }
}
$asset      = static fn (string $p): string => base_url($p) . '?v=' . (@filemtime(FCPATH . $p) ?: 1);
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Devam Takip') ?></title>
    <link rel="stylesheet" href="<?= $asset('assets/css/app.css') ?>">
    <!-- Kurumsal Klasik DataTable teması (app.css'ten SONRA) -->
    <link rel="stylesheet" href="<?= $asset('assets/css/datatables.brand.css') ?>">
    <link rel="stylesheet" href="<?= $asset('assets/css/modal.css') ?>">
</head>
<body>

<div id="progress"></div>

<div class="toasts" id="toasts">
    <?php if ($m = session()->getFlashdata('message')): ?>
        <div class="toast toast-success"><span class="tdot"></span><span><?= esc($m) ?></span><button class="x" type="button" onclick="this.parentElement.remove()">&times;</button></div>
    <?php endif; ?>
    <?php if ($e = session()->getFlashdata('error')): ?>
        <div class="toast toast-error"><span class="tdot"></span><span><?= esc($e) ?></span><button class="x" type="button" onclick="this.parentElement.remove()">&times;</button></div>
    <?php endif; ?>
</div>

<?php if ($loggedIn): ?>
<div class="app-shell">
    <aside class="sidebar">
        <a class="brand" href="<?= site_url('dashboard') ?>">
            <span class="logo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="12" r="8"/><path d="M12 8v4l2.5 1.5"/></svg></span>
            Devam Takip
        </a>

        <nav class="nav-group">
            <div class="label">Menü</div>
            <a class="nav-link <?= $cur === 'dashboard' ? 'active' : '' ?>" href="<?= site_url('dashboard') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg>
                Panelim
            </a>
            <a class="nav-link <?= $cur === 'history' ? 'active' : '' ?>" href="<?= site_url('history') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                Geçmişim
            </a>
            <a class="nav-link <?= $cur === 'requests' ? 'active' : '' ?>" href="<?= site_url('requests') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3 8-8"/><path d="M20 12v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h9"/></svg>
                Taleplerim
            </a>
            <a class="nav-link <?= $cur === 'gorevlerim' ? 'active' : '' ?>" href="<?= site_url('gorevlerim') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                Görevlerim
                <?php if ($openTasks): ?><span class="nav-badge"><?= $openTasks ?></span><?php endif; ?>
            </a>
            <a class="nav-link <?= $cur === 'notifications' ? 'active' : '' ?>" href="<?= site_url('notifications') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
                Bildirimler
                <?php if ($unread): ?><span class="nav-badge"><?= $unread ?></span><?php endif; ?>
            </a>
        </nav>

        <?php if ($role === 'admin'): ?>
        <nav class="nav-group">
            <div class="label">Yönetim</div>
            <a class="nav-link <?= $cur === 'admin' ? 'active' : '' ?>" href="<?= site_url('admin') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12l9-8 9 8"/><path d="M5 10v10h14V10"/></svg>
                Genel Bakış
            </a>
            <a class="nav-link <?= str_starts_with($cur, 'admin/requests') ? 'active' : '' ?>" href="<?= site_url('admin/requests') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 14l2 2 4-4"/></svg>
                Talepler
                <?php if ($pendingReq): ?><span class="nav-badge"><?= $pendingReq ?></span><?php endif; ?>
            </a>
            <a class="nav-link <?= str_starts_with($cur, 'admin/tasks') ? 'active' : '' ?>" href="<?= site_url('admin/tasks') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                Görevler
            </a>
            <a class="nav-link <?= str_starts_with($cur, 'admin/employees') ? 'active' : '' ?>" href="<?= site_url('admin/employees') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.2"/><path d="M3.5 20a5.5 5.5 0 0 1 11 0"/><path d="M16 5.2a3 3 0 0 1 0 5.6"/><path d="M19.8 20a5 5 0 0 0-3-4.6"/></svg>
                Personeller
            </a>
            <a class="nav-link <?= str_starts_with($cur, 'admin/departments') ? 'active' : '' ?>" href="<?= site_url('admin/departments') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M6 21V4a1 1 0 0 1 1-1h7a1 1 0 0 1 1 1v17"/><path d="M15 9h3a1 1 0 0 1 1 1v11"/><path d="M9 7h2"/><path d="M9 11h2"/><path d="M9 15h2"/></svg>
                Departmanlar
            </a>
            <a class="nav-link <?= str_starts_with($cur, 'admin/positions') ? 'active' : '' ?>" href="<?= site_url('admin/positions') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="M14 10h4"/><path d="M14 14h2"/></svg>
                Pozisyonlar
            </a>
            <a class="nav-link <?= (str_starts_with($cur, 'admin/shifts') || str_starts_with($cur, 'admin/shift-schedule')) ? 'active' : '' ?>" href="<?= site_url('admin/shifts') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M3 9h18"/><path d="M8 2v4"/><path d="M16 2v4"/><path d="M12 13v3l1.8 1"/></svg>
                Vardiyalar
            </a>
            <a class="nav-link <?= str_starts_with($cur, 'admin/locations') ? 'active' : '' ?>" href="<?= site_url('admin/locations') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s7-5.6 7-11a7 7 0 1 0-14 0c0 5.4 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                Lokasyonlar
            </a>
            <a class="nav-link <?= str_starts_with($cur, 'admin/suspicious') ? 'active' : '' ?>" href="<?= site_url('admin/suspicious') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l8 3v6c0 4.5-3.2 7.7-8 9-4.8-1.3-8-4.5-8-9V6z"/><path d="M12 8.5v4"/><path d="M12 16h.01"/></svg>
                Şüpheli işlemler
            </a>
            <a class="nav-link <?= str_starts_with($cur, 'admin/attendance') ? 'active' : '' ?>" href="<?= site_url('admin/attendance') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3 8-8"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                Devam Kayıtları
            </a>
            <a class="nav-link <?= str_starts_with($cur, 'admin/payroll') ? 'active' : '' ?>" href="<?= site_url('admin/payroll') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 7V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/><path d="M21 12h-6a2 2 0 0 0 0 4h6z"/></svg>
                Puantaj &amp; Maaş
            </a>
            <a class="nav-link <?= str_starts_with($cur, 'admin/reports') ? 'active' : '' ?>" href="<?= site_url('admin/reports') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 16v-4"/><path d="M12 16V8"/><path d="M17 16v-6"/></svg>
                Raporlar
            </a>
            <a class="nav-link <?= str_starts_with($cur, 'admin/settings') ? 'active' : '' ?>" href="<?= site_url('admin/settings') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 7h14"/><path d="M5 12h14"/><path d="M5 17h14"/><circle cx="9" cy="7" r="2.2" fill="currentColor" stroke="none"/><circle cx="15" cy="12" r="2.2" fill="currentColor" stroke="none"/><circle cx="8" cy="17" r="2.2" fill="currentColor" stroke="none"/></svg>
                Ayarlar
            </a>
        </nav>
        <?php endif; ?>

        <div class="spacer"></div>
        <div class="user">
            <span class="avatar"><?= esc($ini) ?></span>
            <span class="who"><b><?= esc($name) ?></b><span><?= $role === 'admin' ? 'Yönetici' : 'Personel' ?></span></span>
        </div>
        <a class="nav-link" href="<?= site_url('logout') ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 12H4"/><path d="M8 8l-4 4 4 4"/><path d="M12 4h6a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-6"/></svg>
            Çıkış
        </a>
    </aside>

    <div class="main">
        <header class="topbar">
            <button class="hamburger" type="button" aria-label="Menü" onclick="document.body.classList.toggle('nav-open')">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h16"/></svg>
            </button>
            <span class="page-title"><?= esc($title ?? 'Panel') ?></span>
            <div class="right">
                <a class="bell" href="<?= site_url('notifications') ?>" aria-label="Bildirimler"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg><?php if ($unread): ?><span class="bell-dot"><?= $unread ?></span><?php endif; ?></a>
                <span><?= esc($name) ?></span>
            </div>
        </header>
        <main class="content"><?= $this->renderSection('content') ?></main>
    </div>
    <div class="scrim" onclick="document.body.classList.remove('nav-open')"></div>
</div>
<!-- Ortak ekleme/düzenleme modalı -->
<div class="modal-overlay" id="appModal" hidden>
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="appModalTitle">
        <div class="modal-head">
            <h2 class="modal-title" id="appModalTitle"></h2>
            <button type="button" class="modal-x" data-modal-close aria-label="Kapat">&times;</button>
        </div>
        <div class="modal-body"></div>
    </div>
</div>
<?php else: ?>
<div class="auth-wrap"><div class="auth-card"><?= $this->renderSection('content') ?></div></div>
<?php endif; ?>

<?php if ($loggedIn): ?>
<!-- Liste standardı: jQuery + DataTables + Responsive + Kurumsal Klasik başlatıcı -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="<?= $asset('assets/js/datatables.init.js') ?>"></script>
<script src="<?= $asset('assets/js/modal.js') ?>"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="<?= $asset('assets/js/confirm.js') ?>"></script>
<?php endif; ?>

<script>
(function () {
    document.querySelectorAll('#toasts .toast').forEach(function (t) {
        setTimeout(function () { t.style.transition = 'opacity .3s, transform .3s'; t.style.opacity = '0'; t.style.transform = 'translateX(18px)'; setTimeout(function () { t.remove(); }, 320); }, 4200);
    });
})();
</script>
<script>
(function () {
    var bar = document.getElementById('progress');
    var timer = null, w = 0;
    function start() {
        if (! bar) { return; }
        document.body.classList.add('loading');
        w = 10; bar.style.width = w + '%';
        clearInterval(timer);
        timer = setInterval(function () { w = Math.min(w + Math.random() * 12, 92); bar.style.width = w + '%'; }, 220);
    }
    document.addEventListener('click', function (e) {
        var a = e.target.closest ? e.target.closest('a') : null;
        if (! a) { return; }
        var href = a.getAttribute('href');
        if (! href || a.target === '_blank' || href.charAt(0) === '#' || href.indexOf('javascript:') === 0) { return; }
        if (a.hostname && a.hostname !== location.hostname) { return; }
        start();
    });
    document.addEventListener('submit', function (e) {
        start();
        var b = e.target.querySelector('button[type=submit], button:not([type])');
        if (b) { b.classList.add('is-loading'); }
    });
    window.addEventListener('pageshow', function () {
        document.body.classList.remove('loading');
        if (bar) { bar.style.width = '0'; }
        clearInterval(timer);
        document.querySelectorAll('.btn.is-loading').forEach(function (x) { x.classList.remove('is-loading'); });
    });
})();
</script>
<script>
(function(){
    // Mobilde menü linkine dokununca off-canvas paneli kapat (anlık his + güvenli kapanma)
    document.querySelectorAll('.sidebar a').forEach(function(a){
        a.addEventListener('click', function(){ document.body.classList.remove('nav-open'); });
    });
})();
</script>
</body>
</html>
