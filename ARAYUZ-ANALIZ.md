# Arayüz Analizi — Mevcut Durum Değerlendirmesi

> Durum: **Analiz.** Kod değişikliği yapılmadı.
> Amaç: Mevcut ekranları yıkmadan "profesyonel ürün" seviyesine taşımak için zemin tespiti.
> Kapsam: Tüm view'lar, tek CSS tasarım sistemi (`app.css`), DataTables teması, layout.

---

## 0. Yönetici Özeti

Sistem **sıfırdan yazılmaya muhtaç değil.** Sağlam bir temel var: token tabanlı tek CSS tasarım sistemi, kurumsal lacivert/mavi palet, koyu sidebar + topbar kabuğu, inline SVG ikon seti, bileşen kütüphanesi (kart, stat, tile, badge, toast, boş durum, skeleton) ve DataTables entegrasyonu. Görünüm **Bootstrap kalıbı değil**, kendi dili var.

"Profesyonel ürün" ile bugünkü hal arasındaki fark bir **yeniden yazım değil, bir cila ve tutarlılık katmanı.** Asıl açıklar:

1. **Görsel hiyerarşi zayıf** — dashboard'da her kart aynı ağırlıkta; "5 saniyede anla" hedefi risk altında.
2. **İki ayrı liste paradigması** yan yana yaşıyor (DataTables'lı personel vs. düz tablo diğerleri) → deneyim tahmin edilemez.
3. **Inline style kayması** — onlarca `style="…"` tek tasarım sistemi ilkesini deliyor.
4. **Form ve aksiyon affordance'ı** — uzun tek sütun formlar, metin-link aksiyonlar, native `confirm()`.
5. **Fazla animasyon** — "gereksiz animasyondan kaçın" ilkesine rağmen her yüklemede fadeUp, tile hover-lift, shimmer mevcut.
6. **Kurumsal bağlam eksik** — KPI'larda oran/karşılaştırma yok, sayısal sütunlar sola yaslı, profesyonel yazdırma/PDF zayıf.

Aşağıdaki bölümler bunları kategorik ve sayfa sayfa açıyor; §5 wireframe fazına öncelik temeli verir.

---

## 1. Mevcut Tasarım Sistemi (Envanter)

| Katman | Durum |
|---|---|
| CSS tasarım sistemi | Tek dosya `app.css` (322 satır), tam token'lı (renk/gölge/yarıçap CSS değişkenlerinde) ✅ |
| Palet | Kurumsal lacivert (`--sidebar #0f172a`) + mavi (`--brand #2563eb`) ✅ (mor aksanlar elenmiş) |
| Layout | Tek kabuk: koyu sidebar + sticky topbar + hamburger, 960px'te mobil ✅ |
| Bileşenler | `card`, `stat`, `tile`, `info-card`, `badge`, `toast`, `empty-state`, `skeleton`, `bulk-bar` ✅ |
| Listeler | DataTables teması (`datatables.brand.css`) — arama/sıralama/sayfalama/mobil-kart; global auto-init |
| İkonlar | Inline SVG (ikon-font bağımlılığı yok) ✅ |
| Tipografi | Inter; h1 1.5rem/800, h2 1.1rem/700, gövde 15px |
| Dil | Türkçe, baştan sona ✅ |
| Hareket | fadeUp (her `.content > *`), tile hover-lift, progress bar, buton spinner, skeleton shimmer |

**Genel kanı:** İskelet ve bileşen seti profesyonel ürün için yeterli. Eksik olan *disiplin* (tutarlılık) ve *hiyerarşi* (öncelik).

---

## 2. Güçlü Yönler (Korunacak)

- **Token tabanlı tek kaynak** — renk/gölge/yarıçap tek yerden. Marka değişimi birkaç değişken.
- **Kurumsal palet** — lacivert+mavi olgun, "uzun yıllar eskimez" hedefine uygun.
- **Semantik renk niyeti** — giriş/geç/mesai/tehlike için ayrı ton çiftleri (`--in`, `--late`, `--ot`, `--dng`).
- **İnline SVG ikonlar** — tutarlı, hafif, ölçeklenebilir.
- **Boş durum + toast + skeleton** bileşenleri zaten var — çoğu üründe sonradan eklenir.
- **DataTables temeli** — liste standardının çekirdeği kurulmuş.
- **Mobil kabuk** — sidebar off-canvas + scrim doğru kurulmuş.

---

## 3. Eksikler — Kategorik Değerlendirme

### 3.1 Görsel Hiyerarşi

- **H1 — Kart çorbası / düz hiyerarşi.** Admin "Genel Bakış": 4 KPI tile → 60/40 split (grafik+talepler) → 4'lü kart grid (giriş/çıkış/geç/erken) → son hareketler. Hepsi **aynı görsel ağırlıkta**. Göz nereye bakacağını bilmiyor; "ilk 5 saniyede anla" hedefi zayıf. Birincil (bugünün özeti) ve ikincil (detay) ayrımı yok.
- **H2 — Sığ tipografi ölçeği.** h1↔h2↔gövde arası ritim az. Bir devam-takip sisteminde **en önemli içerik sayılar**; ama sayılar (1.5rem/800) her şeyle yarışıyor. Tabular-figure (eş genişlikli rakam) kullanılmıyor → sütunlarda rakamlar hizasız.
- **H3 — Sayfa başlığı kalıbı tutarsız.** Kimi sayfa `card-head`+h1, kimi h1+`page-sub`, kimi h1+inline-style'lı `<p>`. Üç farklı başlık dili.
- **H4 — Renk yer yer dekoratif.** İkon tonları güzel ama tutarsız uygulanıyor (ör. "Bekleyen talepler" zil ikonu `ic ot`/mavi; KPI'da `pur` kalıntı niyeti). Semantik harita netleşmeli: yeşil=geldi/giriş, amber=geç, kırmızı=tehlike/erken çıkış, mavi=bilgi.

### 3.2 Kullanıcı Deneyimi (UX)

- **U1 — İki liste paradigması.** `employees/index` tam donanımlı (DataTables + sütun filtresi + toplu işlem); `departments/positions/locations/shifts/requests/suspicious/payroll/history/reports` çıplak `table.data`. Kullanıcı her listede farklı yetenek görüyor → öğrenilebilirlik düşük.
- **U2 — Aksiyon sütunu zayıf affordance.** "Profil / Düzenle / Sil" metin-link; "Sil" buton, "Düzenle" link → görsel tutarsızlık + küçük dokunma hedefi. Standart bir satır-aksiyon grubu yok.
- **U3 — Yıkıcı işlem native `confirm()`.** Marka dışı, kazara tıklamaya açık. Puantaj/personel gibi maliyetli verilerde profesyonel bir onay diyaloğu gerekir.
- **U4 — Formlar uzun tek duvar.** Personel formu 4 bölüm ~18 alan; sticky kaydet çubuğu yok, alan-içi hata gösterimi yok, zorunlu alan işareti sadece "*". Gün boyu veri girişi yapan İK için yorucu.
- **U5 — Geri/konum affordance'ı silik.** "← Personeller" muted metin. Personel → profil → düzenle derinliğinde breadcrumb yok.
- **U6 — Boş durum tutarsız.** Kimi sayfa `partials/empty` (güzel), kimi düz `<p class="empty">`. Tek standarda inmeli.
- **U7 — Inline style kayması.** `reports`, `departments`, `profile`, `settings`, `home`, formlar… onlarca `style="…"`. Tek-tasarım-sistemi ilkesini deler, tutarlılığı kırılgan yapar.

### 3.3 Kurumsal Yazılım Standartları

- **K1 — KPI'larda bağlam yok.** "8 bugün gelen", "12 aktif" ham sayı. Yönetici **oran ve sapma** ister: "Gelen 42/50 (%84)", "düne göre +3". Karşılaştırma olmadan dashboard durum bildirmiyor, sadece sayı sayıyor.
- **K2 — Global arama yok.** Gün boyu kullanılan, çok personelli sistemde topbar'da "isim/kod ile personel ara" beklenir. Topbar şu an neredeyse boş (zil + isim).
- **K3 — Sayısal hizalama.** Tablolarda geç/mesai/çalışılan/maaş sütunları **sola yaslı**. Kurumsal tablolarda sayılar sağa yaslı + tabular figür ile taranır.
- **K4 — Yoğunluk seçeneği yok.** Güç kullanıcısı (gün boyu giriş yapan) için kompakt/rahat satır yoğunluğu anahtarı yardımcı olur.
- **K5 — Yazdırma/PDF.** Rapor CSV + print view var; profesyonel çıktı için antet (şirket adı, dönem, üretim tarihi, sayfa no) ve baskı tipografisi netleşmeli.
- **K6 — Erişilebilirlik.** `--faint #94a3b8` beyaz üzerinde küçük metinde kontrast sınırda; sidebar etiketi `#6b7794` koyu üzerinde düşük. `:focus-visible` halkası tutarlı tanımlanmalı.

### 3.4 Tutarlılık & Sistem

- **C1 — İkon çizgi kalınlığı** çoğu 2, kimi 1.7/2.2 → küçük ama birikir.
- **C2 — Yarıçap/gölge tonu** 18px kart + yumuşak gölge "tüketici/dostane" hisle; istenen "ciddi ERP" için biraz daha sıkı yarıçap (12–14px) ve daha düz gölge daha dayanıklı/kurumsal okunur. *(Zevk kararı — onay gerek.)*
- **C3 — Fazla animasyon.** Her yüklemede `fadeUp`, tile hover-lift, shimmer — "gereksiz animasyondan kaçın" ilkesiyle çelişiyor. Mikro-geri bildirim (buton/sayfa geçişi) kalsın, dekoratif olanlar kırpılsın.

---

## 4. Sayfa Sayfa Bulgular

**Giriş (`auth/login`)** — Temiz ve yeterli. Geliştirme: demo kimlik bilgisi metni canlı kurulumda gizlenmeli; logo+marka kilidi ortalı kart iyi. Düşük öncelik.

**Personel paneli (`dashboard`)** — Hero saat + giriş/çıkış butonu güçlü. Açıklar: inline style'lar (`style="margin…"`), 5'li stat grid eşit ağırlık; "bugünkü durum" ile "aksiyon" ayrımı netleşebilir. Saat/tarih JS iyi.

**Admin Genel Bakış (`admin/home`)** — En kritik ekran. Açıklar: H1 (hiyerarşi), K1 (oran/sapma yok), 4 farklı kart bloğu eşit ağırlık. Hedef: üstte "bugünün özeti" şeridi (oranlı KPI), altta ikincil detay. Grafik iyi konumda.

**Personel listesi (`employees/index`)** — Referans liste: DataTables + sütun filtresi + toplu işlem + CSV. Standart **bu** olmalı. Açık: inline `style`, aksiyon metin-link (U2).

**Personel formu (`employees/form`)** — Kapsam iyi (kimlik/iletişim/iş/giriş). Açıklar: U4 (sticky kaydet, alan-içi hata, zorunlu işaret), `max-width` inline.

**Personel profili (`employees/profile`)** — Bilgi mimarisi iyi (özet + 4 bilgi kartı). Açıklar: inline style bloğu (badge satırı), "Son hareketler" zaman hizası, başlık aksiyonu tek (Düzenle) — derin işlemler (şifre sıfırla, pasifleştir) menüye alınabilir.

**Basit CRUD listeleri (`departments`/`positions`/`locations`/`shifts`)** — Aynı çıplak kalıp: başlık+sayaç, tablo, satır-içi `style="text-align:right"`, "Düzenle / Sil". Açıklar: U1 (standart liste), U2, U7. Tek partial'a inmeli.

**Vardiya planı (`shifts/schedule`)** — Haftalık satır + select kalıbı işlevsel. Açıklar: `style="min-width:220px"` inline; hafta gezinme + personel seçimi araç çubuğu netleşebilir; kaydet butonu sayfa altında (sticky olabilir).

**Puantaj & Maaş (`payroll/index`)** — İçerik yoğun ve değerli. Açıklar: K3 (Gün/Geç/Mesai/Net maaş sayısal sütunlar sola yaslı), liste standardı yok (arama/sıralama/filtre), dönem seçici sağ üstte iyi.

**Talepler (`admin/requests`)** — İki kart (izin/avans), onayla/reddet formları. Açıklar: aksiyon butonları satırda iki ayrı form → toplu onay/red yok; liste standardı yok; durum/tür filtresi yok.

**Şüpheli işlemler (`suspicious`)** — Net tablo, amber rozet. Açıklar: tarih/neden filtresi, sıralama; mesafe sütunu sağa yaslı olmalı (K3).

**Raporlar (`reports/index`)** — Filtre + CSV + yazdır iyi kurulmuş. Açıklar: `style="display:flex"` inline; sonuç tablosu dinamik sütun — sayısal hizalama ve sıralama eklenmeli; K5 (print anteti).

**Lokasyon QR (`locations/qr`)** — Dinamik/sabit rozet + otomatik yenileme şık. Açık: "yazdır / tam ekran kiosk" modu kurulumda işe yarar; düşük öncelik.

**Ayarlar (`admin/settings`)** — Mantıklı gruplama, açıklama notu iyi. Açıklar: inline `max-width`/`style`, kaydet butonu sticky değil; bölüm sayısı artarsa sekme/yan-menü gerekebilir.

**Geçmişim (`history`)** — 4 KPI tile + 14 günlük tablo, durum rozeti iyi. Açıklar: tarih aralığı filtresi yok, sayısal sütun hizası (K3), inline style.

**Bildirimler / kabuk (`layout/app`)** — Sidebar gruplama (Menü/Yönetim) doğru; rozetler iyi. Açıklar: topbar boş (K2 global arama buraya), uzun admin menüsü için alt-gruplama (İK / Operasyon / Rapor) ileride; `--faint` kontrast (K6).

---

## 5. Öncelik Sıralaması (Wireframe Temeli)

**P0 — En yüksek etki (kurumsal algıyı en çok değiştirir):**
- Admin Genel Bakış'ı yeniden düzenle: oranlı KPI özeti + birincil/ikincil hiyerarşi (H1, K1).
- Tek **liste standardı**: tüm listeler personel listesi seviyesine (U1, U2, U6).
- Sayısal hizalama + tabular figür (H2, K3).

**P1 — Tutarlılık ve güven:**
- Inline style temizliği → yardımcı sınıflar (U7).
- Aksiyon affordance + profesyonel onay diyaloğu (U2, U3).
- Form cilası: sticky kaydet, alan-içi hata, zorunlu işaret (U4).
- Sayfa başlığı/breadcrumb kalıbını tekleştir (H3, U5).

**P2 — İnce ayar:**
- Topbar global arama (K2).
- Animasyon kırpma (C3).
- Yarıçap/gölge tonunu "ciddi ERP"ye çekme (C2) — *onay gerek*.
- Yoğunluk anahtarı (K4), yazdırma anteti (K5), kontrast (K6).

---

## 6. Sonraki Adım

Bu analiz onaylanınca **wireframe** fazına geçilir (kod yok):
1. Önce P0 ekranların yerleşim taslakları — özellikle Admin Genel Bakış ve "tek liste standardı" anatomisi.
2. Onay sonrası tek design system üzerinde uygulama; hiçbir fonksiyon kaldırılmadan.

**Wireframe öncesi netleşmesi iyi olacak iki yön kararı:**
- Görsel ton: mevcut yumuşak/dostane mi kalsın, yoksa biraz daha sıkı yarıçap + düz gölge ile "ağır kurumsal ERP"ye mi çekilsin? (C2)
- Animasyon: dekoratif hareketler (fadeUp/hover-lift) kırpılsın mı, yoksa korunsun mu? (C3)
