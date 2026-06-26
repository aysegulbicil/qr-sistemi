<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<div class="auth-brand">
    <span class="logo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="12" r="8"/><path d="M12 8v4l2.5 1.5"/></svg></span>
    Devam Takip
</div>
<div class="card pad-lg">
    <h1 class="center">Giriş Yap</h1>
    <p class="muted center" style="margin:-2px 0 18px">Hesabınla devam et</p>
    <form method="post" action="<?= site_url('login') ?>">
        <?= csrf_field() ?>
        <div class="field"><label>Kullanıcı adı</label><input type="text" name="username" value="<?= esc(old('username')) ?>" autocomplete="username" autofocus required></div>
        <div class="field"><label>Şifre</label><input type="password" name="password" autocomplete="current-password" required></div>
        <button type="submit" class="btn btn-primary btn-block btn-lg">Giriş Yap</button>
    </form>
    <p class="muted center" style="margin:16px 0 0">Demo: <strong>admin</strong> / <strong>password</strong></p>
</div>
<?= $this->endSection() ?>
