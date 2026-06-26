# Arayüz Revizyon Planı — Devam Takip & Yönetim Sistemi

> Durum: **Planlama.** Henüz kod değişikliği yapılmadı.
> Amaç: (1) mor/indigo yerine **kurumsal lacivert + mavi** palet, (2) daha temiz ve tutarlı yapı/layout, (3) tam **mobil uyumluluk**, (4) **tüm tablo ve listelerde** standart yönetim araçları (arama, filtre, sıralama, sayfalama, toplu işlem, satır içi düzenle/sil).
> Teknik temel: CodeIgniter 4 · tek CSS tasarım sistemi (`public/assets/css/app.css`) · tek layout (`app/Views/layout/app.php`) · Türkçe arayüz.
> İlke: **mevcut çekirdek korunur ve iyileştirilir** — yıkıp baştan yazmıyoruz.

---

## 0. Yaklaşım

- Faz faz ilerlenir; her faz kendi içinde çalışır ve test edilebilir halde teslim edilir.
- Tasarım **token tabanlı** kalır: renkler CSS değişkenlerinde tek noktadan yönetilir. Marka rengini değiştirmek = birkaç değişkeni değiştirmek.
- Çok az sayıda satır-içi (inline) `style=` ve sabit (hardcoded) renk var; bunlar token'a taşınır, böylece gelecekte renk değişimi tek dosyadan yapılır.
- Her listede **aynı dil**: aynı araç çubuğu, aynı boş durum, aynı sayfalama, aynı satır aksiyonları.

---

## 1. Mevcut Durum Tespiti (envanter)

| Alan | Bugün | Hedef |
|---|---|---|
| Tasarım sistemi | Tek dosya `app.css` (277 satır), token'lı, premium yumuşak stil ✅ | Korunur; palet + tablo/mobil katmanı eklenir |
| Marka rengi | İndigo/mor `#4f46e5` + mor aksanlar (`--pur`, `#8b5cf6`) | Lacivert+mavi palete dönüştürülür |
| Layout | Tek shell: koyu sidebar + topbar + hamburger ✅ | Sadeleştirilir, mobilde iyileştirilir |
| Veri tabloları | 11 görünümde `table.data` | Hepsi ortak "yönetilebilir liste" standardına geçer |
| Araç çubuğu/filtre | Yalnızca 3 görünümde (personel, rapor, vardiya takvimi) | **Tüm** listelere yayılır |
| Sayfalama | Hiçbir listede yok (Pager config var ama kullanılmıyor) | Tüm uzun listelere eklenir |
| Toplu işlem | Yok | Eklenir (seç → toplu sil/durum değiştir/dışa aktar) |
| Satır içi düzenle/sil | Kısmî, tutarsız | Standart "⋯ aksiyon" sütunu |
| Mobil | Sidebar 960px'te gizleniyor, birkaç kırılma noktası var | Tablolar kart'a dönüşür, dokunma hedefleri büyür |

**Veri tablosu içeren 11 görünüm:**
`admin/employees/index` · `admin/departments/index` · `admin/positions/index` · `admin/locations/index` · `admin/shifts/index` · `admin/shifts/schedule` · `admin/payroll/index` · `admin/requests/index` · `admin/suspicious/index` · `admin/reports/index` · `history`

---

## 2. Renk Sistemi — Kurumsal Lacivert + Mavi

### 2.1 Yeni palet (öneri token değerleri)

| Token | Eski (mor/indigo) | Yeni (lacivert+mavi) | Kullanım |
|---|---|---|---|
| `--brand` | `#4f46e5` | `#2563eb` | Birincil buton, aktif menü, vurgu |
| `--brand-600` | `#4338ca` | `#1d4ed8` | Hover/koyu vurgu |
| `--brand-700` | `#3730a3` | `#1e40af` | Link, koyu metin vurgusu |
| `--brand-tint` | `#eef2ff` | `#eff6ff` | Açık ton arka plan, avatar |
| `--sidebar` | `#0f1729` | `#0f172a` | Sidebar üst (lacivert) |
| `--sidebar-2` | `#131c33` | `#162033` | Sidebar alt (gradyan) |
| `--ot-ink` / `--ot-bg` | `#1d4ed8` / `#eff6ff` | korunur (zaten mavi) | "Bilgi" rozet/ikon |

> Lacivert sidebar zaten `#0f1729` ile çok yakın; küçük bir ayarla kurumsal tona oturur. Asıl iş **mor marka + mor aksanların** maviye dönmesi.

### 2.2 Mor aksanın elenmesi

- `--pur-ink` / `--pur-bg` (mor) → maviye eşitlenir **veya** kullanımları nötr/mavi sınıflarla değiştirilir.
- `home.php` içindeki **5 adet `box ic pur`** sınıfı → `ic ot` (mavi) veya `ic work` ile değiştirilir.

### 2.3 Sabit (hardcoded) renkler — token'a taşınacak noktalar

Plan uygulanırken birebir değiştirilecek yerler (tespit edildi):

| Dosya | Satır | İçerik | Aksiyon |
|---|---|---|---|
| `public/assets/css/app.css` | 7 | `--brand*` tanımları | Yeni palet değerleri |
| `public/assets/css/app.css` | 15 | `--pur-ink/--pur-bg` | Maviye eşitle |
| `public/assets/css/app.css` | 34, 39, 43, 73 | `rgba(79,70,229,…)` gölgeler | `rgba(37,99,235,…)` |
| `public/assets/css/app.css` | 134 | focus halkası `rgba(79,70,229,.14)` | Mavi rgba |
| `public/assets/css/app.css` | 256 | `#progress` gradyanında `#8b5cf6` (mor) | Mavi tona çevir |
| `app/Views/admin/home.php` | 16, 25, 33, 44, 52 | `box ic pur` | `box ic ot` |
| `app/Views/admin/home.php` | 97 | Chart `backgroundColor:'#4f46e5'` | `#2563eb` |
| `app/Views/admin/reports/print.php` | 16 | `.pbtn background:#4f46e5` | `#2563eb` |

> Not: CSS'teki `rgba(79,70,229,…)` gölge değerleri sabit yazıldığı için token'dan otomatik güncellenmez; bu satırlar elle güncellenmeli. İleride bunları da bir gölge token'ına bağlamak temizlik açısından önerilir.

---

## 3. Yapısal / Layout Temizliği

1. **Satır içi `style=` temizliği.** En yoğun dosyalar: `admin/requests/index` (7), `admin/payroll/detail` (7), `admin/shifts/form` (5), `admin/payroll/index` (5), `home` (4). Tekrarlayan stiller yardımcı sınıflara taşınır (`.stack`, `.row-actions`, `.muted-sm`, `.text-right` gibi). Sonuç: görünümler okunur, renk/spacing tek yerden yönetilir.
2. **Başlık/araç-çubuğu kalıbı tekleştirilir.** Her liste sayfası aynı `card-head` + `filters` iskeletini kullanır (bkz. §5). Şu an her sayfa kendi başlık düzenini kuruyor.
3. **İçerik genişliği ve nefes alanı.** `.content` (max 1160px) korunur; kart içi padding ve başlık boşlukları tek ölçeğe oturtulur.
4. **İkon tutarlılığı.** Inline SVG'ler korunur; ancak boyut/çizgi kalınlığı için ortak sınıf netleştirilir (zaten `.nav-link svg`, `.btn svg` var).
5. **Sidebar gruplaması.** "Menü" (personel) ve "Yönetim" (admin) ayrımı korunur; uzun admin menüsü için ileride alt-gruplama opsiyonu (örn. İK / Operasyon / Raporlar) değerlendirilir — bu fazda zorunlu değil.

---

## 4. Mobil Uyumluluk

Mevcut kırılma noktaları: 960px (sidebar gizleme), 760px (split-2), 560px (kart başlık), 520px (grid2). Üstüne eklenecekler:

1. **Tablolar → kart görünümü (kritik).** `≤720px` altında geniş veri tabloları yatay kaydırma yerine "etiketli kart" düzenine dönüşür. Yöntem: her `<td>`'ye `data-label` verip CSS ile `display:block` + `::before` etiket. Tek bir `.table-cards` yardımcı sınıfı tüm tablolara uygulanır. (Alternatif: kritik olmayan sütunları mobilde gizlemek.)
2. **Dokunma hedefleri.** Buton/aksiyon min. 44px yükseklik; satır içi "Profil / Düzenle" linkleri mobilde daha büyük tıklama alanı.
3. **Araç çubuğu mobilde tam genişlik.** `.filters` öğeleri alt alta, arama kutusu %100 (kısmen var; tüm filtrelere yayılır).
4. **Topbar.** Başlık taşmasına karşı kısaltma; sağdaki isim küçük ekranda gizli (mevcut), bildirim zili kalır.
5. **Sticky araç çubuğu (opsiyonel).** Uzun listelerde filtre çubuğu üstte sabit kalır.
6. **Yatay scroll güvenliği.** Kart düzenine geçmeyen tablolarda `.table-scroll` gölgeli kenar ipucu ("daha var" göstergesi).

---

## 5. Tablo & Liste Standardı  ← *projenin ağırlık merkezi*

Hedef: **her listede aynı, tahmin edilebilir yönetim deneyimi.** Tek bir yeniden kullanılabilir kalıp tasarlanır ve 11 görünüme uygulanır.

### 5.1 Standart "yönetilebilir liste" anatomisi

```
┌─ Başlık + sayaç ──────────────────────────── [ + Yeni ] ┐
│  [ Ara… ]   [Filtre ▾]  [Durum ▾]      [Temizle]         │  ← araç çubuğu (.filters)
├──────────────────────────────────────────────────────────┤
│ ☐  Sütun ▲   Sütun     Durum            ⋯ (aksiyon)      │  ← sıralanabilir başlık
│ ☐  …          …         ●               Düzenle · Sil     │
├──────────────────────────────────────────────────────────┤
│ Seçili: 0  [Toplu işlem ▾]        ‹ 1 2 3 ›   (sayfalama) │  ← alt bar
└──────────────────────────────────────────────────────────┘
```

### 5.2 Bileşenler

1. **Arama** — serbest metin, debounce'lu; `GET ?q=` ile sunucu tarafı (mevcut personel kalıbı baz alınır).
2. **Filtreler** — listeye göre (durum, departman, tarih aralığı vb.); değişince otomatik submit.
3. **Sıralama** — tıklanabilir başlık + ▲/▼ ok (personel listesinde mevcut kalıp standartlaşır).
4. **Sayfalama** — CI4 Pager ile sunucu taraflı; sayfa başı 25/50 seçimi. (Şu an hiç yok.)
5. **Toplu seçim & işlem** — başlıkta "tümünü seç", satırda onay kutusu; seçim olunca alt bar belirir: toplu sil / durum değiştir / dışa aktar (CSV). Tehlikeli işlemde onay modalı.
6. **Satır aksiyonları** — sağda tutarlı: Düzenle, Sil (yumuşak kırmızı), gerekirse "⋯" menüsü. Tıklama hedefleri büyütülür.
7. **Boş durum** — mevcut `partials/empty` standardı tüm listelere (bazıları düz `<p class="empty">` kullanıyor; birleştirilir).
8. **Satır içi durum rozetleri** — mevcut `.badge` sistemi korunur, renkleri palete uyarlanır.

### 5.3 Uygulama yöntemi (tekrarsız)

- **Bir kez yaz, her yerde kullan:** araç çubuğu + tablo başlığı + sayfalama için ortak **view partial**'ları oluşturulur (`partials/list_toolbar.php`, `partials/pagination.php`, `partials/bulk_bar.php`).
- Sunucu tarafında ortak bir **liste yardımcı** mantığı (arama/sıralama/sayfalama parametre işleme) tekrar kullanılır — personel controller'ındaki kalıp baz alınır.
- CSS'e tek seferlik eklenecekler: `.bulk-bar`, `.checkbox-cell`, `.table-cards` (mobil), `.pagination`.

### 5.4 Liste bazında ne eklenecek (özet)

| Liste | Arama | Filtre | Sıralama | Sayfalama | Toplu işlem |
|---|---|---|---|---|---|
| Personeller | ✅ var | ✅ var | ✅ var | ➕ ekle | ➕ ekle (durum/sil/dışa aktar) |
| Departmanlar | ➕ | – | ➕ | ➕ | ➕ |
| Pozisyonlar | ➕ | departman | ➕ | ➕ | ➕ |
| Lokasyonlar | ➕ | – | ➕ | ➕ | ➕ |
| Vardiyalar | ➕ | – | ➕ | ➕ | ➕ |
| Puantaj | ➕ | dönem/dept | ➕ | ➕ | dışa aktar |
| Talepler (admin) | ➕ | tür/durum | ➕ | ➕ | toplu onay/red |
| Şüpheli işlemler | ➕ | tarih/tür | ➕ | ➕ | – |
| Raporlar | ✅ filtre | ✅ var | ➕ | ➕ | ✅ CSV var |
| Geçmiş (personel) | ➕ | tarih | ✅/➕ | ➕ | – |

> Not: "düzenleme = satır içi hücre düzenleme" değil, **liste yönetim araçları** olarak kararlaştırıldı. İleride istenirse uygun sütunlara (örn. pozisyon, durum) satır içi hızlı düzenleme ayrı faz olarak eklenebilir.

---

## 6. Diğer Bileşen İyileştirmeleri

- **Formlar.** Sekmeli/secdeli form yapısı (`.form-section`) korunur; odak halkası mavi olur; hata durumları için tutarlı `input.is-invalid` + alan altı mesaj.
- **Butonlar.** Palet güncellemesi dışında değişiklik yok; `btn-primary` mavi olur, `btn-light` lacivert metin.
- **Grafikler (Chart.js).** Bar renkleri palete bağlanır (giriş = `#2563eb`, çıkış = nötr gri korunur).
- **Hero / kartlar.** Gradyan lacivert-mavi olur; `--shadow*` token'ları korunur.
- **Toast / progress / skeleton.** Davranış aynı; sadece mor → mavi renk uyarlaması.

---

## 7. Faz Planı (sıralı, teslim edilebilir)

| Faz | Kapsam | Sonuç |
|---|---|---|
| **F1 — Palet** | §2: token'lar + sabit renkler + mor aksanların elenmesi (CSS + home.php + reports/print.php) | Tüm sistem maviye döner, hiçbir işlevsel değişiklik yok. Düşük risk, hızlı kazanç. |
| **F2 — Yapı temizliği** | §3: inline style → yardımcı sınıflar, başlık/araç-çubuğu kalıbının tekleştirilmesi | Görünümler sadeleşir, sonraki fazların zemini hazırlanır. |
| **F3 — Liste standardı altyapısı** | §5.3: ortak partial'lar + liste yardımcı mantığı + CSS (`.bulk-bar`, `.pagination`, `.table-cards`) | Tek referans liste (personel) tam standarda kavuşur. |
| **F4 — Listelerin yayılması** | §5.4: kalan 10 listeye arama/filtre/sıralama/sayfalama/toplu işlem | Tüm listeler aynı dili konuşur. |
| **F5 — Mobil** | §4: tablo→kart dönüşümü, dokunma hedefleri, araç çubuğu mobil | Telefon/tablet tam uyumlu. |
| **F6 — Cila** | §6: form hata durumları, grafik renkleri, küçük tutarlılıklar | Bitiş kalitesi. |

> Öneri sıra: F1 → F2 → F3 → F4 → F5 → F6. F1 tek başına bile görünür "kurumsal" dönüşümü sağlar; istenirse önce sadece F1 uygulanıp onay alınabilir.

---

## 8. Dosya Değişiklik Matrisi

| Dosya | F1 | F2 | F3 | F4 | F5 | F6 |
|---|:--:|:--:|:--:|:--:|:--:|:--:|
| `public/assets/css/app.css` | ✓ | ✓ | ✓ | | ✓ | ✓ |
| `app/Views/layout/app.php` | | ✓ | | | ✓ | |
| `app/Views/admin/home.php` | ✓ | ✓ | | | | ✓ |
| `app/Views/admin/reports/print.php` | ✓ | | | | | |
| `app/Views/partials/` (yeni: toolbar, pagination, bulk_bar) | | | ✓ | | | |
| `app/Views/admin/employees/index.php` | | ✓ | ✓ | ✓ | ✓ | |
| `app/Views/admin/departments/index.php` | | | | ✓ | ✓ | |
| `app/Views/admin/positions/index.php` | | | | ✓ | ✓ | |
| `app/Views/admin/locations/index.php` | | | | ✓ | ✓ | |
| `app/Views/admin/shifts/index.php` | | | | ✓ | ✓ | |
| `app/Views/admin/payroll/index.php` | | ✓ | | ✓ | ✓ | |
| `app/Views/admin/requests/index.php` | | ✓ | | ✓ | ✓ | |
| `app/Views/admin/suspicious/index.php` | | | | ✓ | ✓ | |
| `app/Views/admin/reports/index.php` | | | | ✓ | ✓ | |
| `app/Views/history.php` | | | | ✓ | ✓ | |
| İlgili Controller'lar (arama/sıralama/sayfalama) | | | ✓ | ✓ | | |

---

## 9. Riskler & Notlar

- **Yıkıcı değişiklik yok.** Tüm iş mevcut tasarım sistemi üzerine ek katman; HTML iskeleti büyük ölçüde korunur.
- **Sabit renkler.** `rgba(79,70,229,…)` gölgeleri ve birkaç `#4f46e5`/`#8b5cf6` token dışıdır; F1'de elle taranıp değiştirilmeli (liste §2.3'te hazır).
- **Sayfalama davranışı.** Sunucu taraflı sayfalama eklenince mevcut "tümünü çek" sorguları sayfalı sorgulara dönmeli; controller tarafında küçük ama yaygın dokunuş.
- **Toplu işlem güvenliği.** Toplu sil/durum değiştir için CSRF + onay modalı + yetki kontrolü şart.
- **Test.** Her faz sonunda: masaüstü + mobil (≤720px) görünüm kontrolü, en az 3 listede arama/sıralama/sayfalama/toplu işlem denemesi.

---

## 10. Onay Bekleyen / Sonraki Adım

- ✅ **Palet:** kurumsal lacivert + mavi (`--brand #2563eb`, sidebar `#0f172a`) — onaylandı.
- ✅ **Tablo yaklaşımı:** tutarlı yönetim araçları (satır içi hücre düzenleme değil) — onaylandı.
- ⏳ **Karar:** Önce sadece **F1 (palet)** uygulanıp görsel onay mı alınsın, yoksa F1–F3 tek seferde mi ilerlensin?
- ⏳ **Sayfa başı kayıt:** varsayılan 25 mi 50 mi?
- ⏳ **Toplu işlem kapsamı:** hangi listelerde "toplu sil" riskli görülüp kapatılsın (örn. puantaj, şüpheli işlemler)?

> Onay verirsen F1'den başlayıp paleti uygularım; tek dosyada (CSS) + 2 görünümde net, hızlı ve geri alınabilir bir değişiklik olur.
