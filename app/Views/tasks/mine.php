<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<?php
$today   = date('Y-m-d');
$prLabel = ['low' => 'Düşük', 'normal' => 'Normal', 'high' => 'Yüksek'];
$prBadge = ['low' => 'badge-grey', 'normal' => 'badge-blue', 'high' => 'badge-red'];
$stLabel = ['pending' => 'Bekliyor', 'in_progress' => 'Yapılıyor', 'done' => 'Tamamlandı', 'cancelled' => 'İptal'];
$stBadge = ['pending' => 'badge-amber', 'in_progress' => 'badge-blue', 'done' => 'badge-green', 'cancelled' => 'badge-grey'];
?>
<h1>Görevlerim</h1>
<p class="page-sub">Sana atanan görevler</p>
<div class="card">
    <?php if (empty($tasks)): ?>
        <?= view('partials/empty', ['title' => 'Görev yok', 'message' => 'Sana atanmış bir görev bulunmuyor.']) ?>
    <?php else: ?>
        <?php foreach ($tasks as $t): ?>
            <?php $overdue = $t['due_date'] && $t['due_date'] < $today && ! in_array($t['status'], ['done', 'cancelled'], true); ?>
            <div style="display:flex;gap:14px;align-items:flex-start;padding:14px 0;border-bottom:1px solid var(--line)">
                <div style="flex:1;min-width:0">
                    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                        <strong><?= esc($t['title']) ?></strong>
                        <span class="badge <?= $prBadge[$t['priority']] ?? 'badge-grey' ?>"><?= $prLabel[$t['priority']] ?? esc($t['priority']) ?></span>
                        <span class="badge <?= $stBadge[$t['status']] ?? 'badge-grey' ?>"><?= $stLabel[$t['status']] ?? esc($t['status']) ?></span>
                        <?php if ($overdue): ?><span class="badge badge-red">Gecikti</span><?php endif; ?>
                    </div>
                    <?php if (! empty($t['description'])): ?><p class="muted" style="margin:6px 0 0"><?= nl2br(esc((string) $t['description'])) ?></p><?php endif; ?>
                    <?php if ($t['due_date']): ?><p class="muted-sm" style="margin:6px 0 0">Son tarih: <?= esc(date('d.m.Y', strtotime($t['due_date']))) ?></p><?php endif; ?>
                </div>
                <div style="display:flex;gap:6px;flex-shrink:0">
                    <?php if (! in_array($t['status'], ['done', 'cancelled'], true)): ?>
                        <?php if ($t['status'] === 'pending'): ?>
                        <form method="post" action="<?= site_url('gorevlerim/' . $t['id'] . '/durum') ?>"><?= csrf_field() ?><input type="hidden" name="status" value="in_progress"><button class="btn btn-outline btn-sm">Başla</button></form>
                        <?php endif; ?>
                        <form method="post" action="<?= site_url('gorevlerim/' . $t['id'] . '/durum') ?>"><?= csrf_field() ?><input type="hidden" name="status" value="done"><button class="btn btn-primary btn-sm">Tamamla</button></form>
                    <?php elseif ($t['status'] === 'done' && $t['completed_at']): ?>
                        <span class="muted-sm"><?= esc(date('d.m.Y H:i', strtotime($t['completed_at']))) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
