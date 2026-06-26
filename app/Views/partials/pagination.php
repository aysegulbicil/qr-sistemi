<?php
/**
 * Hafif sunucu taraflı sayfalama.
 * @var int    $page @var int $pages @var int $total @var int $perPage
 * @var string $baseUrl @var array $baseParams  (page hariç mevcut sorgu)
 */
$pages = max(1, (int) ($pages ?? 1));
if ($pages <= 1) {
    return;
}
$page       = min(max(1, (int) ($page ?? 1)), $pages);
$perPage    = (int) ($perPage ?? 25);
$total      = (int) ($total ?? 0);
$baseParams = $baseParams ?? [];
$mk         = static function (int $p) use ($baseUrl, $baseParams) {
    return esc($baseUrl . '?' . http_build_query(array_merge($baseParams, ['page' => $p])), 'attr');
};
$from  = ($page - 1) * $perPage + 1;
$to    = min($total, $page * $perPage);
$start = max(1, $page - 2);
$end   = min($pages, $page + 2);
?>
<nav class="pagination" aria-label="Sayfalama">
    <span class="summary"><?= $from ?>–<?= $to ?> / <?= $total ?> kayıt</span>
    <span class="pages">
        <?php if ($page > 1): ?><a href="<?= $mk($page - 1) ?>" rel="prev">‹</a><?php else: ?><span class="pg disabled">‹</span><?php endif; ?>
        <?php if ($start > 1): ?>
            <a href="<?= $mk(1) ?>">1</a>
            <?php if ($start > 2): ?><span class="pg disabled">…</span><?php endif; ?>
        <?php endif; ?>
        <?php for ($i = $start; $i <= $end; $i++): ?>
            <?php if ($i === $page): ?><span class="pg active"><?= $i ?></span><?php else: ?><a href="<?= $mk($i) ?>"><?= $i ?></a><?php endif; ?>
        <?php endfor; ?>
        <?php if ($end < $pages): ?>
            <?php if ($end < $pages - 1): ?><span class="pg disabled">…</span><?php endif; ?>
            <a href="<?= $mk($pages) ?>"><?= $pages ?></a>
        <?php endif; ?>
        <?php if ($page < $pages): ?><a href="<?= $mk($page + 1) ?>" rel="next">›</a><?php else: ?><span class="pg disabled">›</span><?php endif; ?>
    </span>
</nav>
