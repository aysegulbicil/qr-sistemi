<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title><?= esc($report['title']) ?></title>
    <style>
        * { font-family: Arial, Helvetica, sans-serif; }
        body { margin: 26px; color: #111; }
        .bar { margin-bottom: 16px; }
        h1 { font-size: 19px; margin: 0 0 2px; }
        p.sub { color: #666; margin: 0; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 14px; }
        th, td { border: 1px solid #ccc; padding: 6px 9px; text-align: left; }
        th { background: #f3f4f6; }
        .foot { margin-top: 18px; color: #999; font-size: 11px; }
        .pbtn { background: #2563eb; color: #fff; border: 0; padding: 8px 14px; border-radius: 8px; font-weight: 700; cursor: pointer; }
        @media print { .noprint { display: none; } body { margin: 0; } }
    </style>
</head>
<body onload="window.print()">
    <div class="bar">
        <button class="pbtn noprint" onclick="window.print()">Yazdır</button>
        <h1 style="margin-top:10px"><?= esc($report['title']) ?></h1>
        <p class="sub"><?= esc($report['subtitle']) ?></p>
    </div>
    <table>
        <thead><tr><?php foreach ($report['columns'] as $c): ?><th><?= esc($c) ?></th><?php endforeach; ?></tr></thead>
        <tbody>
        <?php foreach ($report['rows'] as $r): ?>
            <tr><?php foreach ($r as $cell): ?><td><?= esc((string) $cell) ?></td><?php endforeach; ?></tr>
        <?php endforeach; ?>
        <?php if (empty($report['rows'])): ?><tr><td colspan="<?= count($report['columns']) ?>">Kayıt yok.</td></tr><?php endif; ?>
        </tbody>
    </table>
    <p class="foot">Devam Takip · <?= date('d.m.Y H:i') ?></p>
</body>
</html>
