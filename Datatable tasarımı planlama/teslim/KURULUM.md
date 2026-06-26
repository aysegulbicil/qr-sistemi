# Kurumsal Klasik — DataTable Kurulumu

Tüm listeleri klasik DataTable yapısına çevirir: **Göster 10/25/50/100 kayıt · arama · sütun sıralama · "X kayıttan Y–Z arası" + numaralı sayfalama · mobil responsive · Türkçe.**
Tasarım dili: mevcut sistemin lacivert+mavi (`--brand #2563eb`) paleti.

> Bağlı klasör salt-okunur olduğu için dosyalar `teslim/` altında **birebir yol yapısıyla** üretildi. Aşağıdaki gibi projeye kopyala.

---

## 1) Yeni dosyalar (kopyala)

| Bu pakette | Projende olması gereken yer |
|---|---|
| `teslim/public/assets/css/datatables.brand.css` | `public/assets/css/datatables.brand.css` |
| `teslim/public/assets/js/datatables.init.js`    | `public/assets/js/datatables.init.js` |

## 2) Değişen dosyalar (üzerine yaz)

| Bu pakette | Projedeki dosya | Ne değişti |
|---|---|---|
| `teslim/app/Views/layout/app.php` | `app/Views/layout/app.php` | `<head>`'e tema CSS'i; `</body>` öncesi jQuery + DataTables + Responsive + başlatıcı (yalnızca giriş yapınca) |
| `teslim/app/Controllers/Admin/Employees.php` | `app/Controllers/Admin/Employees.php` | `index()` artık tüm satırları döndürür (sayfalama tarayıcıda) |
| `teslim/app/Views/admin/employees/index.php` | `app/Views/admin/employees/index.php` | DataTable + sayfalar-arası toplu seçim + departman/durum sütun filtresi |

## 3) Hiç dokunma — otomatik çalışır

Şu 10 liste **değişmeden** DataTable olur (başlatıcı tüm `table.data` tablolarını otomatik dönüştürür):
Departmanlar · Pozisyonlar · Lokasyonlar · Vardiyalar · Puantaj · Talepler (izin+avans) · Şüpheli işlemler · Raporlar · Geçmişim.

---

## Nasıl çalışıyor

- `datatables.init.js`, sayfadaki **her `table.data` tablosunu** (`.no-dt` sınıfı olanlar hariç) Kurumsal Klasik ayarlarıyla başlatır.
- **Sıralanmayan sütunlar** otomatik algılanır: başlığı boş olan, "İşlem"/"Actions" olan veya içinde seçim kutusu bulunan sütunlar.
- **Türkçe tarih sıralaması:** `gg.aa.yyyy` (ve `gg.aa.yyyy ss:dd`) içeren sütunlar kronolojik sıralanır (Şüpheli işlemler, Geçmişim, Talepler).
- **Mobil:** ekrana sığmayan sütunlar satıra tıklanınca açılan detay olarak gösterilir (DataTables Responsive).

## Yeni liste eklersen

Sadece tabloya `class="data"` ver — otomatik DataTable olur. İstisna istiyorsan `class="data no-dt"`.

### Sütun bazlı açılır filtre (isteğe bağlı)
Tablonun `id`'si olsun ve filtre `<select>`'ine şunu ekle:
```html
<select data-dt-target="#tabloId" data-dt-col="2">
  <option value="">Tümü</option>
  <option value="Aktif">Aktif</option>
</select>
```
`data-dt-col` = filtrelenecek sütunun 0-tabanlı indeksi. Seçilen değer o sütunda tam eşleşmeyle filtrelenir.

---

## Notlar

- **CDN:** jQuery 3.7.1 + DataTables 1.13.8 + Responsive 2.5.0 CDN'den yüklenir. İnternetsiz sunucu için bu üç dosyayı indirip `public/assets/js/` altına koyup `layout/app.php`'deki `<script src>` adreslerini yerel yollarla değiştir.
- **Para/sayı sıralaması:** Para ("₺12.500") veya "1s 30dk" gibi biçimli sütunlar metin olarak sıralanır. Kusursuz sayısal sıralama gerekirse ilgili `<td>`'ye `data-order="12500"` ekle.
- **Büyük personel listesi:** Şu an Personeller istemci taraflı (tüm kayıtlar tek seferde). Binlerce kayıtta yavaşlarsa sunucu-taraflı (server-side) DataTables'a geçilebilir — ayrı bir adım.
- **Eski partial'lar:** `partials/list_toolbar.php` ve `partials/pagination.php` artık Personeller'de kullanılmıyor; başka yerde kullanılmıyorsa silinebilir (zorunlu değil).
