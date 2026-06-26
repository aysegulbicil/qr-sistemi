<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Zengin demo veri: departman/pozisyon/vardiya/lokasyon + 15 personel,
 * son 35 günün giriş/çıkış kayıtları (geç/fazla mesai/erken/devamsız + bugün açık),
 * avans/kesinti, izin & avans talepleri, bildirim, şüpheli işlem, vardiya planı.
 *
 * Tekrar çalıştırılabilir: ana veriler güncellenir, hareket verileri sıfırlanıp yeniden üretilir.
 *   docker compose exec web php spark db:seed DemoDataSeeder
 */
class DemoDataSeeder extends Seeder
{
    public function run()
    {
        $db    = $this->db;
        $now   = date('Y-m-d H:i:s');
        $nowTs = time();
        $today = date('Y-m-d');
        $y     = (int) date('Y');
        $m     = (int) date('n');
        mt_srand(20260626); // tekrarlanabilir demo

        $ri = static function ($a, $b) {
            $a = (int) $a; $b = (int) $b;
            if ($a > $b) { $t = $a; $a = $b; $b = $t; }
            return mt_rand($a, $b);
        };

        // ============================================================
        // 1) AYARLAR
        // ============================================================
        $settings = [
            'company_name'        => 'Demo Şirketi A.Ş.',
            'work_mode'           => 'fixed',
            'fixed_start'         => '08:00',
            'fixed_end'           => '18:00',
            'grace_in'            => '5',
            'grace_out'           => '0',
            'currency'            => '₺',
            'daily_hours'         => '9',
            'workdays_per_month'  => '22',
            'overtime_multiplier' => '1.5',
        ];
        foreach ($settings as $k => $v) {
            $row = $db->table('settings')->where('setting_key', $k)->get()->getRowArray();
            if ($row) {
                $db->table('settings')->where('id', $row['id'])->update(['setting_value' => $v, 'updated_at' => $now]);
            } else {
                $db->table('settings')->insert(['setting_key' => $k, 'setting_value' => $v, 'created_at' => $now, 'updated_at' => $now]);
            }
        }

        // ============================================================
        // 2) DEPARTMANLAR
        // ============================================================
        $departments = [
            'Yönetim'          => 'Genel yönetim ve idari işler',
            'İnsan Kaynakları' => 'Personel, özlük ve işe alım',
            'Muhasebe'         => 'Mali işler ve finans',
            'Yazılım'          => 'Yazılım geliştirme ve teknoloji',
            'Satış'            => 'Satış ve pazarlama',
            'Üretim'           => 'Üretim ve saha operasyonu',
        ];
        $deptId = [];
        foreach ($departments as $name => $desc) {
            $row = $db->table('departments')->where('name', $name)->get()->getRowArray();
            if ($row) {
                $deptId[$name] = (int) $row['id'];
                $db->table('departments')->where('id', $row['id'])->update(['description' => $desc, 'updated_at' => $now]);
            } else {
                $db->table('departments')->insert(['name' => $name, 'description' => $desc, 'created_at' => $now, 'updated_at' => $now]);
                $deptId[$name] = (int) $db->insertID();
            }
        }

        // ============================================================
        // 3) POZİSYONLAR
        // ============================================================
        $positions = [
            ['Genel Müdür', 'Yönetim'],
            ['İK Müdürü', 'İnsan Kaynakları'],
            ['İK Uzmanı', 'İnsan Kaynakları'],
            ['Mali Müşavir', 'Muhasebe'],
            ['Muhasebe Uzmanı', 'Muhasebe'],
            ['Takım Lideri', 'Yazılım'],
            ['Kıdemli Yazılım Geliştirici', 'Yazılım'],
            ['Yazılım Geliştirici', 'Yazılım'],
            ['Satış Müdürü', 'Satış'],
            ['Satış Temsilcisi', 'Satış'],
            ['Vardiya Amiri', 'Üretim'],
            ['Üretim Operatörü', 'Üretim'],
        ];
        $posId = [];
        foreach ($positions as $p) {
            $row = $db->table('positions')->where('name', $p[0])->get()->getRowArray();
            $did = $deptId[$p[1]] ?? null;
            if ($row) {
                $posId[$p[0]] = (int) $row['id'];
                $db->table('positions')->where('id', $row['id'])->update(['department_id' => $did, 'updated_at' => $now]);
            } else {
                $db->table('positions')->insert(['name' => $p[0], 'department_id' => $did, 'created_at' => $now, 'updated_at' => $now]);
                $posId[$p[0]] = (int) $db->insertID();
            }
        }

        // ============================================================
        // 4) VARDİYALAR
        // ============================================================
        $shifts = [
            ['Gündüz Vardiyası', '08:00:00', '18:00:00', 5, 0, 0, '1,2,3,4,5'],
            ['Gece Vardiyası', '20:00:00', '06:00:00', 10, 0, 1, '1,2,3,4,5'],
            ['Yarı Zamanlı', '09:00:00', '13:00:00', 5, 0, 0, '1,2,3,4,5'],
        ];
        $shiftId = [];
        foreach ($shifts as $s) {
            $row  = $db->table('shifts')->where('name', $s[0])->get()->getRowArray();
            $data = [
                'name' => $s[0], 'start_time' => $s[1], 'end_time' => $s[2],
                'grace_in_minutes' => $s[3], 'grace_out_minutes' => $s[4],
                'crosses_midnight' => $s[5], 'workdays' => $s[6], 'updated_at' => $now,
            ];
            if ($row) {
                $shiftId[$s[0]] = (int) $row['id'];
                $db->table('shifts')->where('id', $row['id'])->update($data);
            } else {
                $data['created_at'] = $now;
                $db->table('shifts')->insert($data);
                $shiftId[$s[0]] = (int) $db->insertID();
            }
        }

        // ============================================================
        // 5) LOKASYONLAR
        // ============================================================
        $locations = [
            ['main-gate', 'Ana Giriş', 'fixed', null, null, null, 0],
            ['fabrika', 'Fabrika Kapısı', 'dynamic', 41.0150000, 28.9800000, 120, 1],
            ['ofis', 'Genel Müdürlük', 'fixed', 41.0082000, 28.9784000, 200, 0],
        ];
        $locId = [];
        foreach ($locations as $l) {
            $row  = $db->table('locations')->where('code', $l[0])->get()->getRowArray();
            $data = [
                'code' => $l[0], 'name' => $l[1], 'qr_mode' => $l[2],
                'geo_lat' => $l[3], 'geo_lng' => $l[4], 'geo_radius_m' => $l[5],
                'enforce_geo' => $l[6], 'is_active' => 1, 'updated_at' => $now,
            ];
            if ($row) {
                $locId[$l[0]] = (int) $row['id'];
                $db->table('locations')->where('id', $row['id'])->update($data);
            } else {
                $data['created_at'] = $now;
                $db->table('locations')->insert($data);
                $locId[$l[0]] = (int) $db->insertID();
            }
        }

        // ============================================================
        // 6) PERSONELLER
        // [username, ad soyad, kod, rol, departman, pozisyon, vardiya,
        //  maaş tipi, maaş, durum, telefon, TC, işe giriş, doğum]
        // ============================================================
        $hash = password_hash('password', PASSWORD_DEFAULT);
        $employees = [
            ['admin',  'Admin Yönetici', 'ADM-001', 'admin',    'Yönetim',          'Genel Müdür',                 'Gündüz Vardiyası', 'monthly', 0,     'active',     '0532 000 0001', '10000000010', '2020-01-06', '1984-05-10'],
            ['elif',   'Elif Şahin',     'IK-001',  'admin',    'İnsan Kaynakları', 'İK Müdürü',                   'Gündüz Vardiyası', 'monthly', 73000, 'active',     '0532 111 0001', '10000000030', '2020-09-01', '1987-02-14'],
            ['ayse',   'Ayşe Yılmaz',    'IK-002',  'employee', 'İnsan Kaynakları', 'İK Uzmanı',                   'Gündüz Vardiyası', 'monthly', 46000, 'active',     '0532 111 0002', '10000000020', '2021-03-15', '1992-08-21'],
            ['can',    'Can Öztürk',     'YZ-001',  'employee', 'Yazılım',          'Takım Lideri',                'Gündüz Vardiyası', 'monthly', 99000, 'active',     '0532 222 0001', '10000000060', '2019-11-04', '1988-09-19'],
            ['ahmet',  'Ahmet Çelik',    'YZ-002',  'employee', 'Yazılım',          'Kıdemli Yazılım Geliştirici', 'Gündüz Vardiyası', 'monthly', 87000, 'active',     '0532 222 0002', '10000000050', '2021-01-11', '1990-04-27'],
            ['mehmet', 'Mehmet Demir',   'YZ-003',  'employee', 'Yazılım',          'Yazılım Geliştirici',         'Gündüz Vardiyası', 'monthly', 67000, 'active',     '0532 222 0003', '10000000040', '2022-06-20', '1995-11-03'],
            ['merve',  'Merve Koç',      'MH-001',  'employee', 'Muhasebe',         'Mali Müşavir',                'Gündüz Vardiyası', 'monthly', 92000, 'active',     '0532 333 0001', '10000000080', '2020-05-18', '1986-06-08'],
            ['fatma',  'Fatma Kaya',     'MH-002',  'employee', 'Muhasebe',         'Muhasebe Uzmanı',             'Gündüz Vardiyası', 'monthly', 49000, 'active',     '0532 333 0002', '10000000070', '2022-02-07', '1993-12-30'],
            ['seda',   'Seda Aydın',     'ST-001',  'employee', 'Satış',            'Satış Müdürü',                'Gündüz Vardiyası', 'monthly', 81000, 'active',     '0532 444 0001', '10000000100', '2020-07-13', '1989-10-05'],
            ['zeynep', 'Zeynep Arslan',  'ST-003',  'employee', 'Satış',            'Satış Temsilcisi',            'Gündüz Vardiyası', 'monthly', 43000, 'active',     '0532 444 0003', '10000000090', '2023-03-01', '1996-01-22'],
            ['gizem',  'Gizem Aktaş',    'ST-004',  'employee', 'Satış',            'Satış Temsilcisi',            'Gündüz Vardiyası', 'monthly', 41000, 'active',     '0532 444 0004', '10000000110', '2023-08-21', '1997-05-16'],
            ['mustafa','Mustafa Yıldız', 'UR-003',  'employee', 'Üretim',           'Üretim Operatörü',            'Gündüz Vardiyası', 'daily',   1850,  'active',     '0532 555 0003', '10000000120', '2022-10-10', '1991-03-08'],
            ['burak',  'Burak Doğan',    'UR-001',  'employee', 'Üretim',           'Vardiya Amiri',               'Gece Vardiyası',   'monthly', 54000, 'active',     '0532 555 0001', '10000000130', '2021-06-28', '1990-07-12'],
            ['emre',   'Emre Polat',     'UR-002',  'employee', 'Üretim',           'Üretim Operatörü',            'Gece Vardiyası',   'hourly',  155,   'active',     '0532 555 0002', '10000000140', '2023-01-09', '1994-11-25'],
            ['hakan',  'Hakan Şen',      'ST-005',  'employee', 'Satış',            'Satış Temsilcisi',            'Gündüz Vardiyası', 'monthly', 40000, 'terminated', '0532 444 0005', '10000000150', '2021-02-01', '1992-02-02'],
        ];
        $userId = [];
        $ibanSeq = 10;
        foreach ($employees as $e) {
            $ibanSeq++;
            $row  = $db->table('users')->where('username', $e[0])->get()->getRowArray();
            $data = [
                'employee_code'     => $e[2],
                'full_name'         => $e[1],
                'email'             => $e[0] . '@demoas.com',
                'role'              => $e[3],
                'department_id'     => $deptId[$e[4]] ?? null,
                'position_id'       => $posId[$e[5]] ?? null,
                'shift_id'          => $shiftId[$e[6]] ?? null,
                'salary_type'       => $e[7],
                'salary_amount'     => $e[8],
                'employment_status' => $e[9],
                'phone'             => $e[10],
                'contact_email'     => $e[0] . '@demoas.com',
                'address'           => 'İstanbul, Türkiye',
                'national_id'       => $e[11],
                'iban'              => 'TR3300061005197864578' . str_pad((string) $ibanSeq, 5, '0', STR_PAD_LEFT),
                'hire_date'         => $e[12],
                'birth_date'        => $e[13],
                'is_active'         => $e[9] === 'terminated' ? 0 : 1,
                'updated_at'        => $now,
            ];
            if ($row) {
                $userId[$e[0]] = (int) $row['id'];
                $db->table('users')->where('id', $row['id'])->update($data);
            } else {
                $data['username']      = $e[0];
                $data['password_hash'] = $hash;
                $data['created_at']    = $now;
                $db->table('users')->insert($data);
                $userId[$e[0]] = (int) $db->insertID();
            }
        }

        // ============================================================
        // 7) HAREKET VERİLERİNİ SIFIRLA (tekrar çalıştırma için temiz)
        // ============================================================
        foreach (['attendance_logs', 'suspicious_events', 'advances', 'leave_requests', 'advance_requests', 'notifications', 'shift_assignments', 'qr_tokens'] as $t) {
            $db->table($t)->truncate();
        }

        // ============================================================
        // 8) GİRİŞ/ÇIKIŞ KAYITLARI (son 35 gün, hafta içi)
        // ============================================================
        $ua    = 'Mozilla/5.0 (Linux; Android 13; Mobile) AppleWebKit/537.36';
        $ips   = ['192.168.1.20', '192.168.1.21', '192.168.1.22', '192.168.1.34', '88.230.10.4'];
        $start = strtotime('-34 days', strtotime($today));
        $endTs = strtotime($today);

        foreach ($userId as $uname => $uid) {
            if ($uname === 'hakan') {
                continue; // ayrılan personel
            }
            for ($d = $start; $d <= $endTs; $d += 86400) {
                $dow = (int) date('N', $d);
                if ($dow >= 6) {
                    continue; // hafta sonu
                }
                $date    = date('Y-m-d', $d);
                $isToday = ($date === $today);

                // ~%7 devamsız
                if ($ri(1, 100) <= 7) {
                    continue;
                }

                // geç kalma dağılımı
                $lr = $ri(1, 100);
                if ($lr <= 65) {
                    $late = 0;
                } elseif ($lr <= 88) {
                    $late = $ri(6, 25);
                } else {
                    $late = $ri(26, 75);
                }

                $inTs = strtotime($date . ' 08:00:00') + $late * 60 + $ri(-120, 120);
                if ($inTs < strtotime($date . ' 07:45:00')) {
                    $inTs = strtotime($date . ' 07:45:00');
                }

                if ($isToday) {
                    if (strtotime($date . ' 08:00:00') > $nowTs) {
                        continue; // gün henüz başlamadı (erken çalıştırma)
                    }
                    if ($inTs > $nowTs - 120) {
                        $inTs = $ri(strtotime($date . ' 07:50:00'), max(strtotime($date . ' 07:51:00'), $nowTs - 120));
                    }
                }

                // lokasyon + şüpheli durum
                $loc  = ($ri(1, 100) <= 15) ? $locId['fabrika'] : $locId['main-gate'];
                $susp = 0; $reason = null; $dist = null; $glat = null; $glng = null;
                if ($loc === $locId['fabrika'] && $ri(1, 100) <= 22) {
                    $susp   = 1;
                    $dist   = $ri(180, 850);
                    $reason = 'Konum alan dışında (' . $dist . ' m)';
                    $glat   = 41.0150000 + $ri(-40, 40) / 10000;
                    $glng   = 28.9800000 + $ri(-40, 40) / 10000;
                }

                $db->table('attendance_logs')->insert([
                    'user_id'           => $uid,
                    'location_id'       => $loc,
                    'type'              => 'in',
                    'event_at'          => date('Y-m-d H:i:s', $inTs),
                    'source'            => ($ri(1, 100) <= 80 ? 'qr' : 'manual'),
                    'ip_address'        => $ips[array_rand($ips)],
                    'user_agent'        => $ua,
                    'geo_lat'           => $glat,
                    'geo_lng'           => $glng,
                    'distance_m'        => $dist,
                    'is_suspicious'     => $susp,
                    'suspicious_reason' => $reason,
                    'created_at'        => date('Y-m-d H:i:s', $inTs),
                ]);
                if ($susp) {
                    $db->table('suspicious_events')->insert([
                        'user_id' => $uid, 'location_id' => $loc, 'type' => 'in',
                        'reason' => $reason, 'geo_lat' => $glat, 'geo_lng' => $glng,
                        'distance_m' => $dist, 'ip_address' => $ips[array_rand($ips)],
                        'created_at' => date('Y-m-d H:i:s', $inTs),
                    ]);
                }

                // çıkış
                $shiftEndTs = strtotime($date . ' 18:00:00');
                $makeOut    = true;
                if ($isToday && $nowTs < $shiftEndTs) {
                    // gün sürüyor: çoğu hâlâ içeride
                    if ($ri(1, 100) <= 75) {
                        $makeOut = false;
                    } elseif ($nowTs - $inTs < 4 * 3600) {
                        $makeOut = false;
                    }
                }
                if (! $makeOut) {
                    continue;
                }

                $or = $ri(1, 100);
                if ($or <= 60) {
                    $outOff = $ri(-5, 8);
                } elseif ($or <= 85) {
                    $outOff = $ri(15, 150); // fazla mesai
                } else {
                    $outOff = -$ri(15, 120); // erken çıkış
                }
                $outTs = $shiftEndTs + $outOff * 60 + $ri(-90, 90);
                if ($outTs <= $inTs + 3600) {
                    $outTs = $inTs + 8 * 3600;
                }
                if ($isToday && $outTs > $nowTs - 60) {
                    $outTs = $nowTs - 60;
                }
                if ($outTs > $inTs + 1800) {
                    $db->table('attendance_logs')->insert([
                        'user_id'     => $uid,
                        'location_id' => $loc,
                        'type'        => 'out',
                        'event_at'    => date('Y-m-d H:i:s', $outTs),
                        'source'      => ($ri(1, 100) <= 80 ? 'qr' : 'manual'),
                        'ip_address'  => $ips[array_rand($ips)],
                        'user_agent'  => $ua,
                        'created_at'  => date('Y-m-d H:i:s', $outTs),
                    ]);
                }
            }
        }

        // ============================================================
        // 9) AVANS / KESİNTİ (bu ay, puantaja yansır)
        // ============================================================
        $advRows = [
            ['mehmet', 'advance',   5000, 'Maaş avansı'],
            ['zeynep', 'advance',   3000, 'Acil ihtiyaç avansı'],
            ['can',    'advance',  10000, 'Onaylı avans talebi'],
            ['ahmet',  'deduction', 750,  'Geç kalma kesintisi'],
            ['fatma',  'deduction', 500,  'Avans mahsubu'],
        ];
        foreach ($advRows as $a) {
            $db->table('advances')->insert([
                'user_id' => $userId[$a[0]], 'type' => $a[1], 'amount' => $a[2],
                'reason' => $a[3], 'period_year' => $y, 'period_month' => $m,
                'created_by' => $userId['admin'], 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // ============================================================
        // 10) İZİN TÜRLERİ (id eşle) + İZİN TALEPLERİ
        // ============================================================
        $lt = [];
        foreach ($db->table('leave_types')->get()->getResultArray() as $r) {
            $lt[$r['name']] = (int) $r['id'];
        }
        $days = static fn ($s, $e) => (float) ((strtotime($e) - strtotime($s)) / 86400 + 1);

        $leaveRows = [
            // [user, tür, başlangıç, bitiş, durum, sebep, note]
            ['mehmet', 'Yıllık İzin',   sprintf('%04d-%02d-15', $y, $m), sprintf('%04d-%02d-17', $y, $m), 'approved', 'Aile ziyareti', null],
            ['fatma',  'Ücretsiz İzin', sprintf('%04d-%02d-08', $y, $m), sprintf('%04d-%02d-09', $y, $m), 'approved', 'Kişisel işler', null],
            ['ahmet',  'Hastalık İzni', sprintf('%04d-%02d-12', $y, $m), sprintf('%04d-%02d-12', $y, $m), 'rejected', 'Rahatsızlık',   'Rapor/belge eksik'],
            ['zeynep', 'Mazeret İzni',  date('Y-m-d', strtotime('+4 days')), date('Y-m-d', strtotime('+4 days')), 'pending', 'Resmi işlem', null],
            ['seda',   'Yıllık İzin',   date('Y-m-d', strtotime('+10 days')), date('Y-m-d', strtotime('+14 days')), 'pending', 'Yıllık izin planı', null],
        ];
        foreach ($leaveRows as $l) {
            $decided = in_array($l[4], ['approved', 'rejected'], true) ? $now : null;
            $db->table('leave_requests')->insert([
                'user_id' => $userId[$l[0]], 'leave_type_id' => $lt[$l[1]] ?? null,
                'start_date' => $l[2], 'end_date' => $l[3], 'days' => $days($l[2], $l[3]),
                'reason' => $l[5], 'status' => $l[4],
                'approver_id' => $decided ? $userId['admin'] : null, 'decided_at' => $decided,
                'note' => $l[6], 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // ============================================================
        // 11) AVANS TALEPLERİ
        // ============================================================
        $advReq = [
            ['gizem', 4000, 'pending',  'Acil ihtiyaç'],
            ['can',  10000, 'approved', 'Konut peşinatı'],
            ['mustafa', 2000, 'rejected', 'Kişisel'],
        ];
        foreach ($advReq as $a) {
            $decided = in_array($a[2], ['approved', 'rejected'], true) ? $now : null;
            $db->table('advance_requests')->insert([
                'user_id' => $userId[$a[0]], 'amount' => $a[1], 'reason' => $a[3],
                'status' => $a[2], 'approver_id' => $decided ? $userId['admin'] : null,
                'decided_at' => $decided, 'period_year' => $y, 'period_month' => $m,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // ============================================================
        // 12) BİLDİRİMLER
        // ============================================================
        $notif = [
            // [user, mesaj, url, okundu mu]
            ['admin',  'Yeni izin talebi: Zeynep Arslan onay bekliyor', '/admin/requests', false],
            ['admin',  'Yeni avans talebi: Gizem Aktaş onay bekliyor',  '/admin/requests', false],
            ['elif',   'Yeni izin talebi: Seda Aydın onay bekliyor',    '/admin/requests', false],
            ['mehmet', 'Yıllık izin talebiniz onaylandı (15–17)',       '/requests',       true],
            ['ahmet',  'Hastalık izni talebiniz reddedildi',            '/requests',       false],
            ['can',    'Avans talebiniz onaylandı (10.000 ₺)',          '/requests',       false],
            ['zeynep', 'İzin talebiniz alındı, onay bekliyor',          '/requests',       true],
        ];
        foreach ($notif as $n) {
            $db->table('notifications')->insert([
                'user_id' => $userId[$n[0]], 'type' => 'request',
                'message' => $n[1], 'url' => $n[2],
                'read_at' => $n[3] ? $now : null, 'created_at' => $now,
            ]);
        }

        // ============================================================
        // 13) ŞÜPHELİ İŞLEMLER (ek belirgin kayıtlar)
        // ============================================================
        $db->table('suspicious_events')->insert([
            'user_id' => $userId['zeynep'], 'location_id' => $locId['fabrika'], 'type' => 'in',
            'reason' => 'Konum alan dışında (520 m)', 'geo_lat' => 41.0201000, 'geo_lng' => 28.9750000,
            'distance_m' => 520, 'ip_address' => '88.230.10.4',
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
        ]);
        $db->table('suspicious_events')->insert([
            'user_id' => $userId['emre'], 'location_id' => $locId['fabrika'], 'type' => 'in',
            'reason' => 'Kısa sürede tekrarlanan giriş denemesi', 'geo_lat' => null, 'geo_lng' => null,
            'distance_m' => null, 'ip_address' => '192.168.1.34',
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 days')),
        ]);

        // ============================================================
        // 14) VARDİYA PLANI (önümüzdeki 7 gün)
        // ============================================================
        $planUsers = [
            ['burak', 'Gece Vardiyası'],
            ['emre', 'Gece Vardiyası'],
            ['mustafa', 'Gündüz Vardiyası'],
            ['ayse', 'Gündüz Vardiyası'],
        ];
        for ($i = 0; $i < 7; $i++) {
            $wd = strtotime('+' . $i . ' days', strtotime($today));
            if ((int) date('N', $wd) >= 6) {
                continue;
            }
            $wdate = date('Y-m-d', $wd);
            foreach ($planUsers as $pu) {
                $db->table('shift_assignments')->insert([
                    'user_id' => $userId[$pu[0]], 'work_date' => $wdate,
                    'shift_id' => $shiftId[$pu[1]], 'note' => null,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        $atCount = $db->table('attendance_logs')->countAllResults();
        echo "Demo veri yüklendi.\n";
        echo "  Personel       : " . count($userId) . "\n";
        echo "  Giriş/çıkış     : " . $atCount . " kayıt\n";
        echo "  Departman       : " . count($deptId) . " | Pozisyon: " . count($posId) . "\n";
        echo "  Giriş: admin / password  (tüm hesaplar aynı şifre)\n";
    }
}
