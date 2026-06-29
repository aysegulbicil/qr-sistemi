<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>

<h1>Merhaba, <?= esc(session()->get('full_name')) ?></h1>
<p class="page-sub">Bugünkü durumun aşağıda.</p>

<div class="card hero pad-lg">
    <div class="clock" id="clock">--:--:--</div>
    <div class="date" id="date"></div>
    <div class="status-row">
        <span class="dot <?= $checkedIn ? 'in' : 'out' ?>"></span>
        <span><?= $checkedIn ? 'Şu an içeridesin' : 'Şu an dışarıdasın' ?></span>
    </div>
    <?php if ($canPunch): ?>
    <form method="post" action="<?= site_url('attendance/punch') ?>" style="position:relative;z-index:1">
        <?= csrf_field() ?>
        <input type="hidden" name="geo_lat" id="dash_lat">
        <input type="hidden" name="geo_lng" id="dash_lng">
        <?php if (! $checkedIn): ?>
            <button name="type" value="in" class="btn btn-light btn-lg btn-block">Giriş Yap</button>
        <?php else: ?>
            <button name="type" value="out" class="btn btn-danger btn-lg btn-block">Çıkış Yap</button>
        <?php endif; ?>
    </form>
    <?php else: ?>
    <p style="opacity:.92;font-size:.95rem;margin:6px 0 0;position:relative;z-index:1">Giriş/çıkış için kapıdaki <strong>QR kodunu</strong> telefonunla okut.</p>
    <?php endif; ?>
</div>

<h2>Bugün</h2>
<div class="stat-grid">
    <div class="stat"><div class="ic in"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12"/><path d="M8 11l4 4 4-4"/><path d="M4 21h16"/></svg></div><div class="v"><?= esc(hhmm($today['first_in'])) ?></div><div class="l">İlk giriş</div></div>
    <div class="stat"><div class="ic out"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21V9"/><path d="M8 13l4-4 4 4"/><path d="M4 3h16"/></svg></div><div class="v"><?= esc(hhmm($today['last_out'])) ?></div><div class="l">Son çıkış</div></div>
    <div class="stat"><div class="ic late"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></div><div class="v"><?= esc(minutes_to_hm((int) $today['late_minutes'])) ?></div><div class="l">Geç kalma</div></div>
    <div class="stat"><div class="ic ot"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v8"/><path d="M8 12h8"/></svg></div><div class="v"><?= esc(minutes_to_hm((int) $today['overtime_minutes'])) ?></div><div class="l">Fazla mesai</div></div>
    <div class="stat"><div class="ic work"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M3 13h18"/></svg></div><div class="v"><?= esc(minutes_to_hm((int) $today['worked_minutes'])) ?></div><div class="l">Çalışılan</div></div>
</div>

<script>
(function () {
    var days   = ['Pazar','Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi'];
    var months = ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];
    function pad(n){return String(n).padStart(2,'0');}
    function tick(){
        var n=new Date();
        document.getElementById('clock').textContent=pad(n.getHours())+':'+pad(n.getMinutes())+':'+pad(n.getSeconds());
        document.getElementById('date').textContent=days[n.getDay()]+', '+n.getDate()+' '+months[n.getMonth()]+' '+n.getFullYear();
    }
    tick(); setInterval(tick,1000);
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (p) {
            var la=document.getElementById('dash_lat'), lo=document.getElementById('dash_lng');
            if (la && lo) { la.value=p.coords.latitude; lo.value=p.coords.longitude; }
        });
    }
})();
</script>
<?= $this->endSection() ?>
