<?php
$isEdit = $employee !== null;
$val = static fn (string $k, $default = '') => esc($employee[$k] ?? old($k) ?? $default);
$sel = static fn (string $k, $v) => (string) ($employee[$k] ?? old($k) ?? '') === (string) $v ? 'selected' : '';
?>
<form method="post" action="<?= $isEdit ? site_url('admin/employees/' . $employee['id']) : site_url('admin/employees') ?>">
    <?= csrf_field() ?>

    <div class="form-section">
        <div class="sec-title">Kimlik</div>
        <div class="field"><label>Ad Soyad *</label><input type="text" name="full_name" value="<?= $val('full_name') ?>" required autofocus></div>
        <div class="grid2">
            <div class="field"><label>Personel kodu</label><input type="text" name="employee_code" value="<?= $val('employee_code') ?>"></div>
            <div class="field"><label>T.C. Kimlik No</label><input type="text" name="national_id" value="<?= $val('national_id') ?>"></div>
            <div class="field"><label>Doğum tarihi</label><input type="date" name="birth_date" value="<?= $val('birth_date') ?>"></div>
        </div>
    </div>

    <div class="form-section">
        <div class="sec-title">İletişim</div>
        <div class="grid2">
            <div class="field"><label>Telefon</label><input type="text" name="phone" value="<?= $val('phone') ?>"></div>
            <div class="field"><label>E-posta</label><input type="email" name="contact_email" value="<?= $val('contact_email') ?>"></div>
        </div>
        <div class="field"><label>Adres</label><input type="text" name="address" value="<?= $val('address') ?>"></div>
    </div>

    <div class="form-section">
        <div class="sec-title">İş bilgileri</div>
        <div class="grid2">
            <div class="field"><label>Departman</label>
                <select name="department_id"><option value="">— yok —</option>
                    <?php foreach ($departments as $d): ?><option value="<?= $d['id'] ?>" <?= $sel('department_id', $d['id']) ?>><?= esc($d['name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label>Pozisyon</label>
                <select name="position_id"><option value="">— yok —</option>
                    <?php foreach ($positions as $p): ?><option value="<?= $p['id'] ?>" <?= $sel('position_id', $p['id']) ?>><?= esc($p['name']) ?><?= $p['department_name'] ? ' (' . esc($p['department_name']) . ')' : '' ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label>Vardiya</label>
                <select name="shift_id"><option value="">— yok —</option>
                    <?php foreach ($shifts as $s): ?><option value="<?= $s['id'] ?>" <?= $sel('shift_id', $s['id']) ?>><?= esc($s['name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label>Çalışma durumu</label>
                <select name="employment_status">
                    <option value="active" <?= $sel('employment_status', 'active') ?>>Aktif</option>
                    <option value="passive" <?= $sel('employment_status', 'passive') ?>>Pasif</option>
                    <option value="terminated" <?= $sel('employment_status', 'terminated') ?>>Ayrıldı</option>
                </select>
            </div>
            <div class="field"><label>İşe giriş tarihi</label><input type="date" name="hire_date" value="<?= $val('hire_date') ?>"></div>
            <div class="field"><label>Maaş tipi</label>
                <select name="salary_type">
                    <option value="monthly" <?= $sel('salary_type', 'monthly') ?>>Aylık</option>
                    <option value="daily" <?= $sel('salary_type', 'daily') ?>>Günlük</option>
                    <option value="hourly" <?= $sel('salary_type', 'hourly') ?>>Saatlik</option>
                </select>
            </div>
            <div class="field"><label>Maaş tutarı (₺)</label><input type="number" step="0.01" min="0" name="salary_amount" value="<?= $val('salary_amount') ?>"></div>
            <div class="field"><label>IBAN</label><input type="text" name="iban" value="<?= $val('iban') ?>"></div>
        </div>
    </div>

    <div class="form-section">
        <div class="sec-title">Giriş bilgileri</div>
        <div class="grid2">
            <div class="field"><label>Kullanıcı adı *</label><input type="text" name="username" value="<?= $val('username') ?>" required></div>
            <div class="field"><label>Şifre <span class="hint"><?= $isEdit ? '(boş = değişmez)' : '' ?></span></label><input type="password" name="password" <?= $isEdit ? '' : 'required' ?>></div>
            <div class="field"><label>Rol</label>
                <select name="role">
                    <option value="employee" <?= $sel('role', 'employee') ?>>Personel</option>
                    <option value="admin" <?= $sel('role', 'admin') ?>>Yönetici</option>
                </select>
            </div>
        </div>
        <div class="field"><label class="check"><input type="checkbox" name="is_active" value="1" <?= ($employee['is_active'] ?? 1) ? 'checked' : '' ?>> Giriş yapabilir (aktif hesap)</label></div>
    </div>

    <div class="form-actions">
        <button class="btn btn-primary">Kaydet</button>
        <a class="btn btn-link" href="<?= site_url('admin/employees') ?>">İptal</a>
    </div>
</form>
