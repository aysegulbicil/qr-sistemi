<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<div class="form-narrow" style="max-width:1150px;margin:0 auto">
    <h1>Ayarlar</h1>
    <p class="page-sub">Şirket çalışma kuralları ve maaş parametreleri</p>
    <div class="card pad-lg">
        <form method="post" action="<?= site_url('admin/settings') ?>">
            <?= csrf_field() ?>
            <div class="form-section">
                <div class="sec-title">Genel</div>
                <div class="field"><label>Şirket adı</label><input type="text" name="company_name" value="<?= esc($settings['company_name']) ?>"></div>
                <div class="field"><label>Çalışma modeli</label>
                    <select name="work_mode">
                        <option value="fixed" <?= $settings['work_mode'] === 'fixed' ? 'selected' : '' ?>>Sabit günlük saat</option>
                        <option value="shift" <?= $settings['work_mode'] === 'shift' ? 'selected' : '' ?>>Vardiyalı</option>
                    </select>
                </div>
                <div class="grid2">
                    <div class="field"><label>Başlangıç (sabit)</label><input type="time" name="fixed_start" value="<?= esc($settings['fixed_start']) ?>"></div>
                    <div class="field"><label>Bitiş (sabit)</label><input type="time" name="fixed_end" value="<?= esc($settings['fixed_end']) ?>"></div>
                    <div class="field"><label>Giriş toleransı (dk)</label><input type="number" name="grace_in" value="<?= esc($settings['grace_in']) ?>" min="0"></div>
                    <div class="field"><label>Çıkış toleransı (dk)</label><input type="number" name="grace_out" value="<?= esc($settings['grace_out']) ?>" min="0"></div>
                </div>
            </div>

            <div class="form-section">
                <div class="sec-title">Maaş &amp; puantaj</div>
                <div class="grid2">
                    <div class="field"><label>Para birimi</label><input type="text" name="currency" value="<?= esc($settings['currency']) ?>" maxlength="4"></div>
                    <div class="field"><label>Günlük çalışma saati</label><input type="number" step="0.5" min="1" name="daily_hours" value="<?= esc($settings['daily_hours']) ?>"></div>
                    <div class="field"><label>Aylık çalışma günü</label><input type="number" min="1" name="workdays_per_month" value="<?= esc($settings['workdays_per_month']) ?>"></div>
                    <div class="field"><label>Fazla mesai katsayısı</label><input type="number" step="0.1" min="1" name="overtime_multiplier" value="<?= esc($settings['overtime_multiplier']) ?>"></div>
                </div>
                <p class="muted" style="font-size:.84rem">Saatlik ücret = aylık maaş ÷ (aylık gün × günlük saat). Fazla mesai bu ücret × katsayı ile hesaplanır.</p>
            </div>

            <button class="btn btn-primary">Ayarları kaydet</button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
