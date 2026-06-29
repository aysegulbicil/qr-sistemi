<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<h1>Bildirimler</h1>
<p class="page-sub">Son 50 bildirim</p>
<div class="card">
    <?php if (empty($list)): ?>
        <?= view('partials/empty', ['title' => 'Bildirim yok', 'message' => 'Henüz bir bildirimin yok.']) ?>
    <?php else: ?>
        <?php foreach ($list as $n): ?>
            <div class="mv" style="<?= $n['read_at'] ? '' : 'background:var(--brand-tint);margin:0 -12px;padding:10px 12px;border-radius:9px' ?>">
                <span class="mdot <?= $n['read_at'] ? 'out' : 'in' ?>"></span>
                <span><?= esc($n['message']) ?></span><?php if ($n['url']): ?><a class="btn btn-outline btn-sm" href="<?= esc($n['url']) ?>">Git</a><?php endif; ?>
                <span class="mt"><?= esc(date('d.m H:i', strtotime($n['created_at']))) ?></span>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
