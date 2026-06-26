<div class="empty-state">
    <span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18"/><path d="M8 15h4"/></svg></span>
    <h3><?= esc($title ?? 'Kayıt yok') ?></h3>
    <?php if (! empty($message)): ?><p><?= esc($message) ?></p><?php endif; ?>
    <?php if (! empty($actionUrl)): ?><a class="btn btn-primary btn-sm" href="<?= esc($actionUrl) ?>"><?= esc($actionLabel ?? 'Ekle') ?></a><?php endif; ?>
</div>
