<?php
/**
 * Toplu işlem çubuğu. Seçim yapılınca JS ile görünür olur.
 * @var array $actions  value=>label seçenekleri
 */
?>
<div class="bulk-bar" id="bulkBar" hidden>
    <span class="sel-count"><strong id="selCount">0</strong> seçili</span>
    <span class="spacer"></span>
    <select name="bulk_action" aria-label="Toplu işlem">
        <option value="">Toplu işlem seç…</option>
        <?php foreach (($actions ?? []) as $val => $lbl): ?>
            <option value="<?= esc((string) $val, 'attr') ?>"><?= esc($lbl) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary btn-sm">Uygula</button>
    <button type="button" class="btn btn-link btn-sm" id="bulkClear">Seçimi temizle</button>
</div>
