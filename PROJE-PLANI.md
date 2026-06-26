# QR Personel Giriş/Çıkış (Devam Takip) Sistemi — Proje Planı

> Durum: **Planlama aşaması.** Henüz kod yazılmadı. Bu doküman yol haritası ve karar listesidir.
> Teknik temel: CodeIgniter 4 (mevcut Docker kurulumu, port 8090). Veritabanı: hazır `db` profili (MariaDB) açılacak.

---

## 1. Amaç ve özet akış

Personelin işe **giriş** ve **çıkış** saatlerini QR ile kaydetmek; şirketin tanımladığı
mesai saatlerine göre **geç kalma / fazla mesai / erken çıkış** hesaplamak; **günlük, haftalık,
aylık, yıllık** özet raporlar üretmek. Ağırlıklı **mobil** kullanım.

Kullanıcı gözünden akış:

```
QR okut  ->  base URL'e gel  ->  sistem QR tipini ve lokasyonu tespit eder
         ->  kimlik doğrula (telefonda oturum / kart / PIN)
         ->  giriş mi çıkış mı belirle  ->  SUNUCU saatiyle kayıt
         ->  onay ekranı ("08:45'te giriş yapıldı, 45 dk gecikme")
```

---

## 2. En kritik karar: Sabit QR mı, Dinamik (dönen) QR mı?

Bu sistemin kalbi ve en çok düşünülmesi gereken yer. Çünkü asıl risk: **birinin işe gelmeden
giriş yapması** (QR'ın fotoğrafını çekip evden okutmak, arkadaşına okutturmak — "buddy punching").

| Model | Nasıl çalışır | Artı | Eksi |
|---|---|---|---|
| **Sabit QR** | Kapıya 1 adet QR asılır, hep aynı URL | En basit, donanım yok | Kopyalanır, evden okutulur (güvenliği zayıf) |
| **Dinamik QR** | Kapıdaki ekran/tablet QR'ı 30–60 sn'de bir yeniler (imzalı, süreli token) | Fotoğrafı işe yaramaz, fiziksel varlığı kanıtlar | Kapıda ekran/tablet gerekir |
| **Sabit + GPS (hibrit)** | Sabit QR, ama kayıtta telefonun konumu iş yerinde mi diye bakılır | Donanımsız, makul güvenli | Konum izni + GPS toleransı yönetimi gerekir |

**Senin "env'den sabit/değişken seçilebilsin" isteğin tam burada:** Global varsayılan `.env`'de
(`QR_MODE=fixed|dynamic`), istenirse her lokasyon için veritabanından ayrı ayrı ayarlanabilir.

**"Tespit" nasıl olur:** QR şu formatta URL taşır:
- Sabit: `https://.../q/KAPI1`
- Dinamik: `https://.../q/KAPI1?t=<imzalı-süreli-token>`

Backend `/q/{lokasyon}` ucuna gelince: lokasyonun modunu okur + token var/geçerli mi bakar →
ona göre yolu seçer. (Senin tarif ettiğin mantık birebir bu.)

> **KARAR:** Her iki mod da olacak, **şirket kendi kurulumunda seçecek** (env + ayar).
> - **Dinamik:** QR **30 sn'de bir** yenilenir (kapıda tablet/ekran gerekir) → uzaktan okutma imkânsız.
> - **Sabit:** Kapıdan okutulur; uzaktan okutmayı *engellemiyoruz* (kabul edildi). Bunun yerine
>   **çift-giriş engeli** koyuyoruz: bir kişinin açık (çıkış yapılmamış) girişi varken tekrar giriş
>   yapamaz; çıkış yaptıktan sonra yeni giriş açabilir.
>
> Not (önemli): Bu çift-giriş kuralı **IP'ye göre değil, kişiye/cihaza göre** olmalı — çünkü ofis
> WiFi'sinde herkes aynı dış IP'yi paylaşır (NAT); IP'ye bağlarsak ikinci personel giriş yapamaz.
> IP yine de sahtecilik sinyali olarak kaydedilir, kilit olarak kullanılmaz.

---

## 3. Kimlik doğrulama: Kişi nasıl tanınır?

Giriş kaydı iki şeyi birden kanıtlamalı: **kim** (kimlik) + **oradaydı** (varlık).
QR/konum "varlığı", giriş yöntemi "kimliği" kanıtlar.

**KARAR:** Kimlik **login** ile (CodeIgniter Shield). Akış: QR okut → sisteme gel → (gerekiyorsa) login
→ **Giriş / Çıkış** seç → sunucu saati + IP ile kaydet.

- Tek QR; **herkese özel QR yok**. Kim olduğunu **login** belirler.
- Personel telefonunda **oturum açık kalır** → ilk girişten sonra okutmalar tek dokunuşa iner (düşük sürtünme).
- Her personel **sadece kendi** giriş/çıkış geçmişini görebilir.
- **Çift-giriş engeli kişiye bağlı:** açık girişi varken yine "Giriş" yapamaz, önce "Çıkış" (IP'ye değil, hesaba bağlı).
- IP sadece **sahtecilik sinyali** olarak saklanır.

Yönetici/İK paneli aynı login altyapısını (Shield) **rol bazlı** kullanır.

---

## 4. Güvenlik / suistimal önleme (senin listende olmayan en önemli başlık)

Katmanlı yaklaşım; ihtiyaca göre aç/kapat:

1. **Dinamik token** — kısa ömürlü, imzalı, **tek kullanımlık** (anti-replay: aynı token tekrar kullanılamaz).
2. **GPS geofence** — kayıt anında konum iş yeri yarıçapında mı?
3. **Selfie / foto** — kayıtta opsiyonel fotoğraf (caydırıcı).
4. **IP / WiFi kontrolü** — sadece şirket ağından.
5. **Cihaz bağlama** — bir personel = bir kayıtlı cihaz.
6. **Sunucu saati esas** — telefon saati ASLA esas alınmaz (kolayca değiştirilir).
7. **Değişiklik logu (audit)** — manuel düzeltmeler kim/ne zaman yaptı kaydı.

---

## 5. Mesai saatleri ve hesaplama kuralları

> Önemli nüans: Çalışma saatleri **`.env`'de değil, veritabanında** tutulmalı ki şirket
> **admin panelinden** kendi saatlerini yazabilsin (senin isteğin buydu).
>
> **KARAR:** Her şirket kendi **çalışma modelini** seçer:
> - **Sabit günlük saat** (örn. her gün 08:00–18:00) — B şirketi gibi.
> - **Vardiyalı** (birden çok vardiya, gece dahil) — A şirketi gibi.
> İkisi de geç kalma / fazla mesai / erken çıkış hesaplar.

Tanımlanacaklar (vardiya bazlı):
- Başlangıç/bitiş saati (örn. 08:00 – 18:00)
- **Tolerans/grace** (örn. 8:05'e kadar geç sayılmaz)
- **Gece vardiyası** (bitiş < başlangıç → ertesi güne taşar)
- Mola düşülmesi (örn. 1 saat öğle)
- Çalışılan günler (hafta sonu/tatil)

Hesaplar (senin örneklerinle):
- **Geç kalma** = max(0, ilk_giriş − mesai_başı − tolerans). 08:45 giriş, 08:00 başı → **45 dk geç**.
- **Fazla mesai** = max(0, son_çıkış − mesai_bitiş). 21:00 çıkış, 18:00 bitiş → **3 saat fazla mesai**.
- **Erken çıkış** = max(0, mesai_bitiş − son_çıkış).
- **Net çalışma** = son_çıkış − ilk_giriş − molalar.

Kenar durumlar (atlanması kolay, sonradan baş ağrıtır):
- **Çıkışı unutma** (giriş var, çıkış yok) → işaretle, fazla mesai sayma, düzeltme iste.
- **Günde birden çok giriş/çıkış** (öğle, dışarı çıkma) → ilk giriş / son çıkış mı, eşleştirme mi?
- **Yön seçimi:** Tek QR; kişi login sonrası **Giriş / Çıkış** butonuyla seçer. (Açık girişi varken "Giriş" pasif → çift-giriş engeli.)
- **İzin / rapor / resmi tatil** → "geç/devamsız" sayılmamalı.

---

## 6. Veri modeli (taslak tablolar)

- `users` (Shield) + `employees` (ad, sicil no, departman, vardiya, aktif)
- `departments`
- `shifts` (başlangıç, bitiş, tolerans, gece bayrağı, çalışma günleri)
- `work_settings` (genel varsayılanlar, zaman dilimi)
- `locations` / kapılar (ad, qr_mode, gps lat/lng/yarıçap, token imza anahtarı)
- `attendance_logs` (personel, lokasyon, tip giriş/çıkış, **sunucu_zamanı**, kaynak qr/manuel, ip, konum, token_id, foto, not)
- `qr_tokens` (dinamik: token, üretim/son kullanım, kullanıldı mı)
- `holidays` (resmi tatiller), `leaves` (izinler)
- `daily_attendance` (hesaplanmış günlük özet: ilk giriş, son çıkış, geç dk, fazla mesai dk, durum)
- `audit_logs` (kayıt düzenleme geçmişi)

---

## 7. Raporlama

- **Günlük:** kim geldi/gelmedi, giriş-çıkış, geç/fazla mesai.
- **Haftalık / aylık / yıllık:** toplam geç dk, toplam fazla mesai, devamsızlık, devam %.
- Yönetici **dashboard** (Chart.js grafikler).
- **Excel (PhpSpreadsheet) / PDF (Dompdf) dışa aktarım** → bordro için.
- Günlük özetler gece otomatik üretilir (CI4 Tasks / cron).

---

## 8. Roller ve yetki

- **Admin / İK:** her şeyi görür, saat/vardiya tanımlar, düzeltme yapar, rapor alır.
- **Yönetici:** sadece kendi ekibini görür.
- **Personel:** sadece kendi kayıtlarını görür, giriş/çıkış yapar.

---

## 9. Mobil & PWA

- Mobile-first, responsive tasarım.
- **PWA** (ana ekrana eklenebilir, uygulama gibi açılır, push bildirim).
- QR okuma telefonun **kendi kamerasıyla** (QR bir URL taşıdığı için ayrı tarayıcıya gerek yok).
- İnternet kapıda şart (offline kayıt ileride opsiyonel).

---

## 10. Bildirimler

- Personele: "bugün geç kaldın" / "giriş kaydın alındı".
- Yöneticiye: günlük geç-gelen / devamsız özeti.
- Kanal: push (PWA) + e-posta.

---

## 11. KVKK / gizlilik (Türkiye için önemli)

Konum, fotoğraf, çalışma saati → **kişisel veri**. Gerekli: aydınlatma metni + açık rıza,
saklama süresi, erişim sınırı, veri güvenliği. Bordro kaydı niteliğinde olduğu için
**değiştirilemez (audit'li) kayıt** tutulmalı.

---

## 12. Teknik yığın (CI4)

- **Auth/yetki:** CodeIgniter Shield
- **QR üretimi:** chillerlan/php-qrcode
- **Dinamik token:** HMAC imzalı, kısa TTL, tek kullanımlık
- **Rapor dışa aktarım:** PhpSpreadsheet (Excel), Dompdf (PDF)
- **Grafik:** Chart.js
- **Zamanlanmış işler:** CI4 Tasks + cron
- **Veritabanı:** MariaDB (mevcut docker `db` profili) + phpMyAdmin

---

## 12.5 Dağıtım modeli (KARAR)

**Çok şirket kullanacak ama SaaS değil:** her şirkete **ayrı kurulum** (kendi bilgisayarına/sunucusuna),
kendi veritabanı, kendi Docker yığını. Veriler **fiziksel olarak ayrı** → kimsenin verisi karışmaz.

Sonuçları:
- Uygulama **tek-kiracılı (single-tenant)** kalır → kod basitleşir (her yere `tenant_id` gerekmez).
- **Docker bizim avantajımız:** kurulum ≈ "docker compose up". Mevcut iskelet birebir bu dağıtım için uygun.
- **İki satış paketi:** "Sabit QR" sürümü ve "Dinamik QR" sürümü → lisans/özellik bayrağıyla ayrılır.
- Düşünülecekler: ilk kurulum **sihirbazı** (şirket adı, logo, saatler, admin hesabı), **lisanslama/satış**
  modeli ve **güncelleme** (yeni sürüm imajını her kuruluma ulaştırma).

---

## 13. Yol haritası (fazlar) — MVP'den gelişmişe

- **Faz 0 — Temel:** `db` profilini aç, Shield kur, personel/departman CRUD, mesai ayarları ekranı.
- **Faz 1 — Çekirdek devam:** sabit QR akışı, giriş/çıkış kaydı (sunucu saati), günlük log.
- **Faz 2 — Hesaplama:** geç/fazla mesai/erken çıkış + günlük özet.
- **Faz 3 — Raporlar:** haftalık/aylık/yıllık + Excel/PDF.
- **Faz 4 — Güvenlik:** dinamik QR (anti-replay), GPS geofence, selfie.
- **Faz 5 — Olgunluk:** PWA, bildirimler, izin/tatil, çoklu lokasyon, roller, audit.
- **Faz 6 — Paketleme/dağıtım:** kurulum sihirbazı, lisanslama, güncelleme stratejisi (her şirkete ayrı kurulum).

---

## 14. Kararlar (tümü alındı)

- **QR modeli:** Hem sabit hem dinamik; şirket seçer. Dinamik = **30 sn**'de yenilenen QR. **İki satış paketi:** Sabit sürüm / Dinamik sürüm.
- **Sabit mod güvenliği:** Uzaktan okutma engellenmiyor; bunun yerine **kişiye bağlı çift-giriş engeli** (IP'ye değil; IP sadece sinyal).
- **Kimlik:** **Login** ile (Shield). Oturum telefonda açık kalır → düşük sürtünme. Personel **sadece kendi** geçmişini görür.
- **Yön:** Tek QR; login sonrası **Giriş / Çıkış** butonuyla seçilir.
- **Kapsam:** Çok şirket ama **SaaS değil** — her şirkete ayrı kurulum, ayrı veritabanı (tek-kiracılı).
- **Çalışma saati:** Şirket bazında **sabit saat / vardiyalı** seçimi.

**Sıradaki:** Faz 0 — `db` profilini aç, Shield kur, personel + mesai ayarları. Onayınla kod aşamasına geçilir.
