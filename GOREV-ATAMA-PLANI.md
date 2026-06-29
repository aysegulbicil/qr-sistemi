# Görev Atama Modülü — Plan (to-do tipi)

> Tarih: 29.06.2026 · Durum: **Tasarım — uygulama onayı bekleniyor**
> Kararlar (senin seçimlerin): **bağımsız iş/görev listesi** · **personel "tamamlandı" işaretler** · **yalnızca admin atar**

---

## 0. Özet
Admin, personele başlıklı görevler atar (açıklama, öncelik, son tarih). Personel "Görevlerim"de görür ve durumunu günceller (bekliyor → yapılıyor → tamamlandı). **Yoklama/maaşa dokunmaz**; tamamen yeni, izole bir tablo → mevcut veri-bütünlüğü risklerini artırmaz, sağlamlaştırma işinden **bağımsız** ilerleyebilir.

---

## 1. Veri modeli (tek tablo, additive migration)
`app/Database/Migrations/2026-06-29-000002_CreateTasks.php` — `tasks` tablosu:

| Alan | Tip | Not |
|---|---|---|
| id | PK | |
| user_id | → users | göreve atanan personel |
| title | varchar(150) | zorunlu |
| description | text? | |
| priority | enum(low, normal, high) | default `normal` |
| status | enum(pending, in_progress, done, cancelled) | default `pending` |
| due_date | date? | son tarih (ops.) |
| assigned_by | → users | atayan admin (audit) |
| completed_at | datetime? | `done` olunca dolar |
| created_at / updated_at | timestamps | |

İndeks: `(user_id, status)`, `due_date`, `assigned_by`. Kural korunur: `CreateInitialSchema`'ya dokunulmaz, migration eklemeli.

> Not: "şablon görev havuzu" gerekmediği için **tek tablo** yeterli — `tasks` doğrudan atanmış görevdir; gereksiz `task_assignments` ayrımına gerek yok.

---

## 2. Model
`app/Models/TaskModel.php` — `$allowedFields`, `$useTimestamps = true`. Metotlar:
- `forUser(int $userId, ?string $status = null): array` — personelin görevleri
- `countOpenForUser(int $userId): int` — dashboard rozeti
- `markStatus(int $id, string $status, ?int $ownerId = null): bool` — durum güncelle (ownerId verilirse **sahiplik doğrula**; `done` ise `completed_at` set)
- `listDetailed(array $filters): array` — admin liste (atanan + atayan adıyla join); mevcut `UserModel::listDetailed` desenini taklit eder.

---

## 3. Controller'lar
**Admin** — `app/Controllers/Admin/Tasks.php` (`admin` filtresi):
- `index()` — DataTables liste (personel / durum / öncelik filtresi); mevcut `table.data` auto-init deseniyle uyumlu.
- `new()` / `create()` — modal `_form` + `form.php` sarmalayıcı + `BaseController::jsonOk/jsonError` (mevcut modal-form deseni, sayfa-geneli CSRF refresh). **Çoklu personele aynı anda atama** (`user_id[]` → N satır).
- `edit()` / `update()`, `delete()` (veya `cancelled`).
- Doğrulama: `title` required; `user_id` exists; `priority`/`status` izinli kümede.

**Personel** — `app/Controllers/Tasks.php` (`auth` filtresi):
- `mine()` — "Görevlerim" listesi.
- `updateStatus(int $id)` — POST; görev **session kullanıcısına ait mi** doğrular, sonra `markStatus`.

---

## 4. Rotalar
admin grubuna: `tasks`, `tasks/new`, `tasks`(POST), `tasks/(:num)/edit`, `tasks/(:num)`(POST), `tasks/(:num)/delete`(POST).
auth grubuna: `gorevlerim` → `Tasks::mine`; `gorevlerim/(:num)/durum`(POST) → `Tasks::updateStatus/$1`.

> Not: "Görevlerim" bir **bilgi sayfasıdır** → `ScanFilter`'a **ekleme** (kendi #4 kararın: bilgi sayfaları taramasız erişilsin). Mevcutta history/requests hâlâ scan-gated; tutarlılık için ileride onları da gözden geçirmek gerek.

---

## 5. Ekranlar (mevcut bileşenlerle)
- `admin/tasks/index.php` — DataTables; durum/öncelik rozetleri; "+ Görev ata" modal tetikleyici.
- `admin/tasks/_form.php` + `form.php` — personel seçimi (çoklu), başlık, açıklama, öncelik, son tarih.
- `tasks/mine.php` — personelin görev kartları; durum butonları (Başla / Tamamla) AJAX POST.
- Sidebar: admin menüsüne **"Görevler"**, personel menüsüne **"Görevlerim"**.
- Dashboard: personel için "Bekleyen görevlerim" sayacı; admin Genel Bakış'a "açık / tamamlanan görev" kutusu (Faz F ile uyumlu).

---

## 6. Bildirim entegrasyonu (mevcut sistem)
Atama yapılınca personele in-app bildirim ("Sana yeni görev atandı: …") — izin/avansta kullanılan mevcut `NotificationModel` / `NotificationService` tekrar kullanılır. Ops: tamamlanınca atayan admine bildirim.

---

## 7. Açık kalan küçük kararlar
1. Durum adımı: `bekliyor → yapılıyor → tamamlandı` mı, yoksa sade `bekliyor → tamamlandı` mı? (önerim: "yapılıyor" dahil, ama opsiyonel.)
2. Son tarih geçince "gecikmiş" rozeti/vurgu? (önerilir, ucuz.)
3. Silme vs iptal: gerçekten sil mi, `cancelled` mı? (önerim: **cancelled** — geçmiş kalsın; #1 silme riskiyle tutarlı.)
4. Çoklu atama (bir görevi birden çok personele) açık olsun mu? (önerim: **evet**.)

---

## 8. Uygulama sırası (onayında)
1. Migration + `TaskModel`.
2. Admin Tasks CRUD (liste + modal atama).
3. Personel "Görevlerim" + durum güncelleme.
4. Bildirim + dashboard sayaçları + sidebar.

> İzole modül; yoklama/maaşa dokunmaz, mevcut sağlamlaştırma backlog'undan bağımsız ilerleyebilir.
