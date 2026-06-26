# Personel Devam Takip & Yönetim Sistemi — Geliştirme Planı

> Durum: **Planlama.** Henüz yeni kod yazılmadı. Bu doküman: (1) modül bazlı geliştirme planı, (2) veritabanı migration planı.
> Teknik temel: CodeIgniter 4 · MariaDB (Docker) · 8090 · Türkçe arayüz / İngilizce kod.

---

## 0. Yaklaşım

- **Faz faz ilerleyeceğiz.** Her faz kendi içinde çalışır halde teslim edilir (migration → model → controller → view → cila).
- **Tek-kiracılı / on-prem** mimari korunuyor (her şirkete ayrı kurulum). SaaS *kalitesi* hedef, SaaS *altyapısı* değil.
- Mevcut çekirdek korunur ve **genişletilir** (yıkıp baştan yazmıyoruz).
- Her modülde standart: **boş durum ekranı + loading + başarı/hata bildirimi (toast) + doğrulama**.

---

## 1. Mevcut durum (eldekiler)

| Var olan | Durum |
|---|---|
| CI4 + Docker + MariaDB | ✅ |
| Oturum tabanlı auth + rol (admin/personel) | ✅ |
| `users`, `shifts`, `locations`, `attendance_logs`, `qr_tokens`, `settings` | ✅ |
| Personel CRUD (temel) | ✅ → genişletilecek |
| QR giriş/çıkış (sabit + dinamik 30 sn), çift-giriş engeli | ✅ → konum + otomatik yön eklenecek |
| Geçmiş, Raporlar (temel), Genel Bakış paneli | ✅ → büyütülecek |
| Yumuşak premium tasarım sistemi (sidebar, kartlar, tiles) | ✅ → premium+ |

---

## 2. Modüller ve geliştirme fazları

### Faz A — Personel Yönetimi (temel genişletme)
**Yeni:** `departments`, `positions`; `users` tablosuna İK alanları (departman, pozisyon, maaş tipi/tutarı, çalışma durumu, telefon, adres, işe giriş tarihi, doğum tarihi, foto).
**Ekranlar:** zenginleştirilmiş personel listesi (arama + sıralama + filtre), personel ekle/düzenle (sekmeli form), **personel profil sayfası** (özet + devam + izin + maaş sekmeleri).
**Çapraz:** tablo arama/sıralama altyapısı, boş durum, loading iskeletleri.

### Faz B — Vardiya & Lokasyon
**Vardiya:** `shift_assignments` (takvim bazlı atama), haftalık/aylık vardiya görünümü, tolerans alanları (mevcut `shifts`'te var).
**Lokasyon:** `locations`'a **GPS** (enlem/boylam/yarıçap + zorunluluk bayrağı); QR okutmada **konum kontrolü** (yarıçap dışında engel) ve **şüpheli işlem kaydı**.
**Ekranlar:** vardiya takvimi, lokasyon formuna harita/koordinat alanları, şüpheli işlemler listesi.

### Faz C — Puantaj & Maaş
**Mantık:** günlük çalışma, geç/erken/fazla mesai/eksik, aylık toplam (motor mevcut, genişletilecek).
**Yeni:** maaş parametreleri (`settings`: mesai katsayısı, para birimi…), `payroll_runs` (aylık snapshot), avans/kesinti yansıması.
**Ekranlar:** **personel bazlı maaş detay ekranı**, aylık puantaj tablosu, hesaplama özeti kartları.

### Faz D — İzin & Avans Talepleri
**Yeni:** `leave_types`, `leave_requests`, `advance_requests`.
**Akış:** personel talep oluşturur → yönetici onay/red → onaylı avans **maaş hesabına** düşer.
**Ekranlar:** personel "Taleplerim", yönetici "Bekleyen talepler" (onay/red), bildirimler.

### Faz E — Raporlar & Dışa Aktarma
**Raporlar:** günlük giriş-çıkış, personel bazlı, aylık puantaj, geç kalanlar, fazla mesai.
**Yeni teknik:** **Excel (PhpSpreadsheet)** + **PDF (Dompdf)** dışa aktarma; filtre çubuğu (tarih/departman/personel).

### Faz F — Dashboard & Grafikler
**Genel Bakış'a eklenecek:** bugün içeride / geç kalan / izinli sayıları, aylık toplam mesai, **bekleyen izin/avans**, **son hareketler** akışı, **haftalık çalışma grafiği (Chart.js)**.

### Faz G — Tasarım cilası & mobil
Premium sidebar (alt menüler/ikonlar), daha canlı kartlar, **toast bildirimleri**, **loading/skeleton**, tüm modüllerde **boş durum & hata ekranları**, **mobil QR okutma** ve personel paneli optimizasyonu.

> Sıra önerisi: **A → B → C → D → E → F → G** (her faz bir öncekinin verisine dayanıyor). F ve G parçaları her fazda kısmen serpiştirilir.

---

## 3. Çapraz kesen gereksinimler (her fazda)

- **Boş durum** bileşeni (ikon + mesaj + aksiyon).
- **Loading**: buton spinner + tablo skeleton + sayfa geçiş barı.
- **Bildirim**: toast (başarı/hata/uyarı) — flash mesajların yerini alır.
- **Tablolar**: arama kutusu, sütun sıralama, sayfalama, filtre.
- **Form doğrulama**: CI4 validation + alan altı hata mesajları + Türkçe metinler.
- **Mobil**: özellikle QR okutma ve personel paneli; dokunma hedefleri büyük.
- **Yetki**: admin / yönetici / personel rol ayrımı netleştirilecek.

---

## 4. Teknik eklentiler (composer)

| Paket | Amaç | Faz |
|---|---|---|
| `chillerlan/php-qrcode` (ops.) | QR'ı sunucuda üret (CDN bağımlılığını kaldır) | B/G |
| `phpoffice/phpspreadsheet` | Excel dışa aktarma | E |
| `dompdf/dompdf` | PDF dışa aktarma | E |
| Chart.js (CDN/yerel) | Dashboard grafikleri | F |

> Konteynerde `docker compose exec web composer require ...` ile kurulur (ağ erişimi var).

---

## 5. Veritabanı migration planı

### 5.1 Mevcut tablolar (korunur; bazıları ALTER)
`users` · `shifts` · `locations` · `attendance_logs` · `qr_tokens` · `settings`

### 5.2 Yeni / değişen tablolar

**departments**
`id` · `name` · `description?` · timestamps

**positions**
`id` · `name` · `department_id?` (→departments) · `description?` · timestamps

**users (ALTER — İK alanları ekle)**
`department_id?` · `position_id?` · `salary_type` (monthly|daily|hourly) · `salary_amount` (decimal) · `employment_status` (active|passive|terminated) · `phone?` · `address?` · `hire_date?` · `birth_date?` · `photo_path?` · `national_id?`

**shift_assignments** (takvim bazlı vardiya)
`id` · `user_id` (→users) · `work_date` (date) · `shift_id` (→shifts) · `note?` · timestamps · *unique(user_id, work_date)*

**locations (ALTER — coğrafi sınır)**
`geo_lat?` (decimal 10,7) · `geo_lng?` (decimal 10,7) · `geo_radius_m?` (int) · `enforce_geo` (bool)

**attendance_logs (ALTER — konum & şüphe)**
`geo_lat?` · `geo_lng?` · `distance_m?` (int) · `is_suspicious` (bool) · `suspicious_reason?`

**leave_types**
`id` · `name` · `is_paid` (bool) · timestamps

**leave_requests**
`id` · `user_id` (→users) · `leave_type_id` (→leave_types) · `start_date` · `end_date` · `days` (decimal) · `reason?` · `status` (pending|approved|rejected) · `approver_id?` (→users) · `decided_at?` · `note?` · timestamps

**advance_requests**
`id` · `user_id` (→users) · `amount` (decimal) · `reason?` · `status` (pending|approved|rejected) · `approver_id?` · `decided_at?` · `period_year?` · `period_month?` · timestamps

**payroll_runs** (aylık puantaj/maaş snapshot)
`id` · `user_id` (→users) · `period_year` · `period_month` · `worked_minutes` · `late_minutes` · `overtime_minutes` · `missing_minutes` · `base_salary` · `overtime_pay` · `advances_total` · `deductions_total` · `net_pay` · `generated_at` · timestamps · *unique(user_id, year, month)*

**notifications** (ops. — in-app bildirim)
`id` · `user_id` (→users) · `type` · `message` · `read_at?` · timestamps

**settings** (yeni anahtarlar — şema değişmez, key/value):
`currency` · `overtime_multiplier` · `workdays_per_month` · `daily_hours` …

### 5.3 Migration sırası (FK bağımlılıklarına göre)

```
2026-06-26-000001_CreateDepartmentsAndPositions
2026-06-26-000002_ExtendUsersHrFields
2026-06-26-000003_CreateShiftAssignments
2026-06-26-000004_ExtendLocationsGeo
2026-06-26-000005_ExtendAttendanceGeoSuspicious
2026-06-26-000006_CreateLeaveTypesAndRequests
2026-06-26-000007_CreateAdvanceRequests
2026-06-26-000008_CreatePayrollRuns
2026-06-26-000009_CreateNotifications   (opsiyonel)
```

> Kural: her migration **eklemeli** (mevcut `CreateInitialSchema`'ya dokunulmaz). Böylece senin ve tüm kurulumların DB'si `php spark migrate` ile aynı şekilde ilerler.

### 5.4 Mimari kararı: `users` vs ayrı `employees`
Öneri: şimdilik **`users` tablosunu genişletmek** (auth + İK aynı satırda) — en az kırılım, mevcut kod korunur. İleride ölçek gerekirse `users` (giriş) / `employees` (İK) ayrımına geçilebilir. (Senin tercihini bekliyorum.)

---

## 6. Tasarım yükseltmeleri (G ile paralel)

- Mor/lacivert tema korunur, **daha premium**: yumuşak gölgeler, ince degradeler, tutarlı boşluk ritmi.
- **Sidebar**: gruplu menü + alt öğeler + aktif vurgusu + (ops.) daraltma.
- **Kartlar**: ikon + başlık + değer + trend/mini-grafik; daha açıklayıcı.
- **Dashboard**: grafik + bekleyen talepler + son hareketler ile **dolu** görünüm.
- **Toast + skeleton + boş durum** standart bileşenler.
- **Mobil**: QR okutma tam ekran/odaklı; personel paneli tek el kullanımına uygun.

---

## 7. Senden onay bekleyen kararlar

1. **Veri modeli:** `users`'ı genişlet (önerilen) mi, `users`+`employees` ayrımı mı?
2. **Başlangıç fazı:** A (Personel Yönetimi) ile mi başlayalım? (Önerilen)
3. **Maaş kapsamı:** sadeden başlayıp (aylık baz + mesai + avans/kesinti) ilerleyelim mi, yoksa SGK/vergi gibi detaylar da olacak mı?
4. **Export & grafik:** Excel/PDF için belirtilen paketleri ve Chart.js'i kurmaya başlayalım mı?

---

## 8. Sıradaki adım

Onayını verdiğinde **Faz A** ile başlıyoruz: migration (departments/positions + users İK alanları) → modeller → personel CRUD + profil sayfası → arama/sıralama + boş/loading durumları.
