<?php
/**
 * Yeniden kullanılabilir liste araç çubuğu: arama + filtre selectleri + Ara/Temizle.
 * @var string $action            Form GET hedefi
 * @var string $q                 Mevcut arama metni
 * @var string $searchName        Arama input adı (vars. 'q')
 * @var string $searchPlaceholder
 * @var array  $filters           Her biri: ['name'=>, 'value'=>, 'options'=>[val=>label]]
 * @var array  $hidden            name=>value gizli inputlar (örn. sort/dir korunur)
 * @var bool   $hasFilter
 * @var string $clearUrl
 */
$searchName = $searchName ?? 'q';
?>
<form method="get" action="<?= esc($action, 'attr') ?>" class="filters">
    <?php foreach (($hidden ?? []) as $hk => $hv): ?>
        <?php if ($hv !== null && $hv !== ''): ?><input type="hidden" name="<?= esc($hk, 'attr') ?>" value="<?= esc((string) $hv, 'attr') ?>"><?php endif; ?>
    <?php endforeach; ?>
    <span class="search-box">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
        <input type="text" name="<?= esc($searchName, 'attr') ?>" value="<?= esc($q ?? '') ?>" placeholder="<?= esc($searchPlaceholder ?? 'Ara...') ?>">
    </span>
    <?php foreach (($filters ?? []) as $f): ?>
        <select name="<?= esc($f['name'], 'attr') ?>" onchange="this.form.submit()">
            <?php foreach (($f['options'] ?? []) as $val => $lbl): ?>
                <option value="<?= esc((string) $val, 'attr') ?>" <?= (string) ($f['value'] ?? '') === (string) $val ? 'selected' : '' ?>><?= esc($lbl) ?></option>
            <?php endforeach; ?>
        </select>
    <?php endforeach; ?>
    <button class="btn btn-outline btn-sm" type="submit">Ara</button>
    <?php if (! empty($hasFilter)): ?><a class="btn btn-link btn-sm" href="<?= esc($clearUrl, 'attr') ?>">Temizle</a><?php endif; ?>
</form>
