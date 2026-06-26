# QR Sistemi — Düzeltme Analizi + Kararlar (4 madde)

> Tarih: 26.06.2026 · Durum: **Kararlar kesinleşti — uygulama onayı bekleniyor**
> Kapsam: 1) mobil uyumluluk · 2) değişken QR "geçersiz" hatası · 3) QR ekranı güvenliği · 4) QR'sız URL erişimi

---

## Özet tablo

| # | Sorun | Kök neden (kodla doğrulandı) | Karar |
|---|-------|------------------------------|-------|
| 1 | Mobilde admin menüsü bozuk | Sidebar'da kaydırma yok; uzun yönetim menüsü ekrana sığmıyor, alt öğeler kesiliyor | `overflow-y:auto` + mobil rötuşlar |
| 2 | Değişken QR'da "süresi doldu" | (B1) her yenilemede önceki token anında iptal → sınır yarışı; (B2) tek-kullanımlık token link **önizlemesi** tarafından tüketiliyor | Token **POST**'ta tüketilsin; anında-iptal kalksın (TTL'e bağlı); başarısız taramada `scan_context` temizlensin |
| 3 | QR ekranı panelde açık kalıyor | QR gösterimi admin paneli **içinde**; kapı ekranında admin oturumu açık | **Panel-dışı display-URL** (admin oturumu taşımaz) |
| 4 | QR okutmadan erişim | `/dashboard` taramadan açılabiliyor; punch korunuyor ama giriş noktası açık | **Sadece giriş/çıkış işlemi** QR-gated; admin muaf |

> **Kritik gözlem:** 2 (2. kısım), 3 ve 4 aynı boşluğa çıkar — QR/işlem akışı panelle iç içe. Çözüm: QR → panelden ayrı, scan-gated sade işlem sayfası; token GET'te değil POST'ta tüketilir.

---

## 1) Mobil uyumluluk — admin menüsü

**Kök neden:** `app.css:32` `.sidebar{height:100vh}` ama **`overflow-y` yok**; `.spacer{flex:1}` (app.css:41) alt öğeleri dibe iter; mobilde (`@media max-width:960px`, app.css:172) off-canvas gelse de kaydırılamaz. Yönetici menüsü ~16 satır → küçük telefonda taşan alt öğeler (Ayarlar/Çıkış) erişilemez.

**Karar / yapılacaklar:**
- `.sidebar`'a `overflow-y:auto` (+ momentum scroll) — asıl düzeltme.
- `100vh` → `100dvh`; off-canvas panele kapatma (X) + aksiyon sonrası otomatik kapanma; dokunma hedefleri ~44px; topbar/scrim ikincil kontroller.

---

## 2) Değişken QR — "Bu QR kodunun süresi doldu"

**Akış:** `/q/{code}?t=…` → `Scan::location()` dinamik modda `DynamicQr::consume()` çağırır. Bu uç **auth dışıdır** (`Routes.php:8`) → doğrulama login'den ÖNCE, tarama anında. `consume` token yok/süre dolmuş/zaten kullanılmış ise başarısız. Display 25 sn'de bir yeniler; `issue()` her üretimde önceki tokenları **anında iptal eder**. TTL 45 sn.

**Kök neden B1 — rotasyon erken öldürüyor:** `issue()` → `invalidateForLocation()` önceki tokenı hemen `used_at` işaretler; 25 sn sınırında ekrandaki kod zaten ölü olabilir (render gecikmesi dahil).

**Kök neden B2 — önizleme tüketiyor (muhtemel asıl neden):** Tek-kullanımlık token + iOS Safari/QR uygulamalarının arka plan **önizleme GET**'i tokenı sen açmadan `consume` eder → gerçek açılışta "zaten kullanılmış". Apple cihazda sık → "ekrandakini okutmama rağmen geçersiz"i deterministik açıklar.

> **Latent not:** `AuthFilter` `current_url()` sorgu dizesini düşürür; ama `/q` auth dışı + consume login'den önce olduğu için **bu hatanın nedeni bu değil**. (Yanlış teori; doğrulayıp elendi.)

**"Panele dön" tutarsızlığı:** `scan_error.php:8` girişliyse "Panele dön → /dashboard" verir; başarısız `consume` önceki `scan_context`'i **temizlemez** (Scan.php 33–37 erken return) → eski bağlam 180 sn taze ise dashboard'da punch bile mümkün. "Hata verdi ama yine bıraktı" tutarsızlığının kaynağı.

**Karar / yapılacaklar:**
- Tokenı **GET'te tüketme**; `/q` yalnızca doğrula + sade işlem ekranı göstersin, tüketim kullanıcının bastığı **POST** ile olsun (önizlemeyi kökten çözer, #4 ile uyumlu).
- `issue()`'daki **anında-iptal davranışını kaldır**; geçerliliği TTL belirlesin (gerekirse kısa grace).
- Başarısız taramada **panele atma**, "tekrar okut" göster ve `scan_context`'i temizle.
- Uygulamadan önce: `consume` başarısızlığını nedenine göre **logla** (token yok/süre/kullanılmış + `used_at`) → kök nedeni teyit.
- ⚠️ Anti-buddy-punching korunur: tek-kullanım/tazelik **punch** anında garanti.

---

## 3) QR ekranı panel dışına + sabit/değişken + yenileme limiti

**Risk:** QR gösterimi (`admin/locations/{id}/qr`) panel içinde → kapı ekranında admin oturumu açık kalır; değişken QR'da ekran sürekli açık olduğundan kalıcı risk.

**Karar — Panel-dışı display-URL:**
- Lokasyon için panel-dışı, imzalı, salt-gösterim bir URL üretilir. Kapı ekranı yalnızca bunu açar; **admin oturumu taşımaz**. Rotasyon tokenları bu uçtan döner. Panele dönmek için yeniden giriş gerekir.
- Gerekçe: kiosk-modu alternatifi kapı ekranında **canlı admin oturumu** tutar (cihaz/çerez ele geçerse tüm yönetim açılır) → display-URL daha güvenli.
- Uzaktan izleme riski rotasyon + geofence ile sınırlı (kod saniyede yenilenir; fiziksel olarak orada olmak şart). Gerekirse **IP allowlist** ile LAN'a kısıtlanır.
- Sabit: tek sefer basılır, ekrana/panele gerek yok. Değişken: display-URL kullanılır.

**Karar — Sabit QR yenileme limiti: KURUM BAŞINA TOPLAM (lisans):**
- Tüm kurulum genelinde toplam sabit-QR yenileme sayısı lisansa/pakete bağlı bir üst sınırla sınırlanır (paket bazında satışa uygun). Sınır dolunca sabit kod değişimi engellenir.
- Saklama: `.env`/`settings` (lisans bayrağı yanında) + kullanım sayacı.
- **Açık kalan tek detay:** toplam sayı kaç olacak (lisans paketine göre) — uygulama sırasında netleşecek.

---

## 4) QR okutmadan erişim → hata

**Tespit:** `/dashboard` taramayı zorunlu kılmaz (sadece `canPunch=false`). Punch (`Attendance::punch`) zaten `scan_context` + `scan_is_fresh` (180 sn) ister → taramasız punch ENGELLİ; ama giriş noktası açık.

**Karar — Sadece giriş/çıkış İŞLEMİ QR-gated:**
- İşlem (punch): yalnızca **taze, geçerli tarama** ile. Tarama → menüsüz, sade, scan-gated işlem sayfası; taze tarama yoksa **hata**. Token bu sayfanın POST'unda tüketilir (#2).
- **Personel bilgi sayfaları** (Geçmişim/Taleplerim/Bildirimler): girişle erişilebilir, taramasız (evden bakılabilir).
- **Admin yönetim paneli: QR-gated DEĞİL** (zorunlu — QR'ı üreten/basan yer orası; gate'lersek döngü olur). Normal giriş ile erişilir.
- Sonuç: taramasız işlem imkânsız (#4) · QR ekranı panelden ayrı (#3) · başarısız taramada panele sızma yok (#2-2).

---

## Birleşik mimari (özet)

```
QR okut ──► [scan-gated, menüsüz işlem sayfası]
   │            • taze tarama yoksa: HATA
   │            • Giriş/Çıkış → POST → token burada tüketilir → onay
   └─ geçerli tarama yoksa panele DÜŞMEZ

Personel paneli (geçmiş/talep/bildirim) ──► girişle açık, taramasız (bilgi amaçlı)
Admin paneli (yönetim) ──────────────────► girişle açık, QR-gated DEĞİL
QR gösterimi ────────────────────────────► panel-dışı display-URL (admin oturumu taşımaz)
```

## Kesinleşen kararlar (26.06.2026)
1. **#1** sidebar `overflow-y:auto` + mobil rötuşlar.
2. **#2** token tüketimi **GET→POST**; anında-iptal kalkar (TTL'e bağlı); başarısız taramada `scan_context` temizlenir + panele atılmaz; consume hataları loglanır.
3. **#3** kapı ekranı **panel-dışı display-URL** (admin oturumsuz; opsiyonel IP allowlist).
4. **#3** sabit QR yenileme limiti: **kurum başına toplam** (lisans); sayı uygulamada netleşir.
5. **#4** yalnızca **giriş/çıkış işlemi** QR-gated; personel bilgi sayfaları girişle açık; **admin muaf**.

## Önerilen uygulama sırası
1. **Hızlı kazanç:** #1 sidebar `overflow-y` (tek satır, anında iyileşme).
2. **#2** GET→POST tüketim + rotasyon/TTL + `scan_error` "tekrar okut" + `scan_context` temizliği.
3. **#4** işlem akışını panelden ayır (scan-gated sade sayfa).
4. **#3** display-URL + sabit QR kurum-bazlı yenileme limiti.
