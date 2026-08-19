<?php
namespace App\Models;

use Config\Core\Database;
use App\Models\Helper;
use App\Models\User;
use Exception;

class Outlet {

    // ==============================================
    // READ (GET) METHODS
    // ==============================================
    public static function getOutletStats() {
        $db = Database::connect();
        $stats = [
            'activeCount' => 0,
            'expiredCount' => 0,
            'pendingCount' => 0,
            'rejectCount' => 0
        ];

        $resActive = $db->query("SELECT COUNT(*) as total FROM outlet WHERE (status = 'active' OR (status IN ('pending', 'reject') AND tipe_request = 'perpanjangan')) AND (tgl_jatuh_tempo IS NULL OR DATE(tgl_jatuh_tempo) >= CURRENT_DATE())");
        if ($resActive && $resActive->num_rows > 0) {
            $stats['activeCount'] = (int)$resActive->fetch_assoc()['total'];
        }

        $resExpired = $db->query("SELECT COUNT(*) as total FROM outlet WHERE ((status = 'active' OR (status IN ('pending', 'reject') AND tipe_request = 'perpanjangan')) AND DATE(tgl_jatuh_tempo) < CURRENT_DATE()) OR (status = 'pending' AND tipe_request = 'baru') OR status = 'inactive'");
        if ($resExpired && $resExpired->num_rows > 0) {
            $stats['expiredCount'] = (int)$resExpired->fetch_assoc()['total'];
        }

        $stats['pendingCount'] = User::get_status('pending')['total'] ?? 0;
        $stats['rejectCount'] = User::get_status('reject')['total'] ?? 0;

        return $stats;
    }
    public static function getActiveOutlets() {
        $db = Database::connect();
        $sql = "
            SELECT o.*, u_kasir.nama_lengkap as pengelola_toko, u_kasir.username as username_kasir, u_kasir.no_hp as no_hp_toko, mw.provinsi, mw.kabupaten, mw.kecamatan, mw.kelurahan, u_kasir.alamat_lengkap as alamat_outlet,
                   inv.id_investor, inv.biaya_langganan_outlet,
                   u_inv.nama_lengkap as nama_investor, u_inv.username as username_investor, u_inv.no_hp as no_hp_investor,
                   COALESCE(omz.total_omzet_bulan_ini, 0) as omzet_bulan_ini,
                   COALESCE(omz.total_transaksi_bulan_ini, 0) as transaksi_bulan_ini
            FROM outlet o
            LEFT JOIN users u_kasir ON u_kasir.id_users = o.id_users
            LEFT JOIN master_wilayah mw ON mw.id_wilayah = u_kasir.id_wilayah
            LEFT JOIN investor inv ON inv.id_investor = o.id_investor
            LEFT JOIN users u_inv ON u_inv.id_users = inv.id_users
            LEFT JOIN (
                SELECT id_outlet, 
                       SUM(nominal_omzet) as total_omzet_bulan_ini,
                       COUNT(*) as total_transaksi_bulan_ini
                FROM laporan_omzet
                WHERE MONTH(tanggal_omzet) = MONTH(CURRENT_DATE()) 
                  AND YEAR(tanggal_omzet) = YEAR(CURRENT_DATE())
                GROUP BY id_outlet
            ) omz ON omz.id_outlet = o.id_outlet
            WHERE (o.status = 'active' OR (o.status IN ('pending', 'reject') AND o.tipe_request = 'perpanjangan')) AND (o.tgl_jatuh_tempo IS NULL OR DATE(o.tgl_jatuh_tempo) >= CURRENT_DATE())
            ORDER BY o.nama_outlet ASC
        ";
        return $db->query($sql);
    }
    public static function getExpiredOutlets() {
        $db = Database::connect();
        $sql = "
            SELECT o.*, u_kasir.nama_lengkap as pengelola_toko, u_kasir.username as username_kasir, u_kasir.no_hp as no_hp_toko, mw.provinsi, mw.kabupaten, mw.kecamatan, mw.kelurahan, u_kasir.alamat_lengkap as alamat_outlet,
                   inv.id_investor, inv.biaya_langganan_outlet,
                   u_inv.nama_lengkap as nama_investor, u_inv.username as username_investor, u_inv.no_hp as no_hp_investor,
                   COALESCE(omz.total_omzet_bulan_ini, 0) as omzet_bulan_ini,
                   COALESCE(omz.total_transaksi_bulan_ini, 0) as transaksi_bulan_ini
            FROM outlet o
            LEFT JOIN users u_kasir ON u_kasir.id_users = o.id_users
            LEFT JOIN master_wilayah mw ON mw.id_wilayah = u_kasir.id_wilayah
            LEFT JOIN investor inv ON inv.id_investor = o.id_investor
            LEFT JOIN users u_inv ON u_inv.id_users = inv.id_users
            LEFT JOIN (
                SELECT id_outlet, 
                       SUM(nominal_omzet) as total_omzet_bulan_ini,
                       COUNT(*) as total_transaksi_bulan_ini
                FROM laporan_omzet
                WHERE MONTH(tanggal_omzet) = MONTH(CURRENT_DATE()) 
                  AND YEAR(tanggal_omzet) = YEAR(CURRENT_DATE())
                GROUP BY id_outlet
            ) omz ON omz.id_outlet = o.id_outlet
            WHERE ((o.status = 'active' OR (o.status IN ('pending', 'reject') AND o.tipe_request = 'perpanjangan')) AND DATE(o.tgl_jatuh_tempo) < CURRENT_DATE()) OR o.status = 'inactive'
            ORDER BY o.tgl_jatuh_tempo DESC, o.nama_outlet ASC
        ";
        return $db->query($sql);
    }
    public static function getPendingOutlets() {
        $db = Database::connect();
        $sql = "
            SELECT o.*, u_kasir.nama_lengkap as pengelola_toko, u_kasir.username as username_kasir, u_kasir.no_hp as no_hp_toko, mw.provinsi, mw.kabupaten, mw.kecamatan, mw.kelurahan, u_kasir.alamat_lengkap as alamat_outlet,
                   u_inv.nama_lengkap as nama_investor, u_inv.username as username_investor, u_inv.no_hp as no_hp_investor
            FROM outlet o
            LEFT JOIN users u_kasir ON u_kasir.id_users = o.id_users
            LEFT JOIN master_wilayah mw ON mw.id_wilayah = u_kasir.id_wilayah
            LEFT JOIN investor inv ON inv.id_investor = o.id_investor
            LEFT JOIN users u_inv ON u_inv.id_users = inv.id_users
            WHERE o.status = 'pending'
            ORDER BY o.id_outlet DESC
        ";
        return $db->query($sql);
    }
    public static function getRejectedOutlets() {
        $db = Database::connect();
        $sql = "
            SELECT o.*, u_kasir.nama_lengkap as pengelola_toko, u_kasir.username as username_kasir, u_kasir.no_hp as no_hp_toko, mw.provinsi, mw.kabupaten, mw.kecamatan, mw.kelurahan, u_kasir.alamat_lengkap as alamat_outlet,
                   u_inv.nama_lengkap as nama_investor, u_inv.username as username_investor, u_inv.no_hp as no_hp_investor
            FROM outlet o
            LEFT JOIN users u_kasir ON u_kasir.id_users = o.id_users
            LEFT JOIN master_wilayah mw ON mw.id_wilayah = u_kasir.id_wilayah
            LEFT JOIN investor inv ON inv.id_investor = o.id_investor
            LEFT JOIN users u_inv ON u_inv.id_users = inv.id_users
            WHERE o.status = 'reject'
            ORDER BY o.id_outlet DESC
        ";
        return $db->query($sql);
    }
    public static function getOutletById($idOutlet) {
        $db = Database::connect();
        $id = intval($idOutlet);
        $sql = "
            SELECT o.*, u.nama_lengkap as kasir_nama, u.username as kasir_username, u.no_hp as kasir_no_hp, 
                   mw.provinsi, mw.kabupaten, mw.kecamatan, mw.kelurahan, u.alamat_lengkap as alamat_outlet, u.id_wilayah,
                   u_inv.nama_lengkap as nama_investor, u_inv.username as username_investor
            FROM outlet o
            JOIN users u ON (u.id_users = o.id_users)
            LEFT JOIN master_wilayah mw ON (u.id_wilayah = mw.id_wilayah)
            LEFT JOIN investor inv ON (inv.id_investor = o.id_investor)
            LEFT JOIN users u_inv ON (u_inv.id_users = inv.id_users)
            WHERE o.id_outlet = {$id}
            LIMIT 1
        ";
        $res = $db->query($sql);
        return ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;
    }

    // ==============================================
    // WRITE (SAVE / DELETE / UPDATE) METHODS
    // ==============================================
    public static function saveOutlet($data, $currentUserId = 1, $files = []) {
        $db = Database::connect();
        $idOutlet = intval($data['id_outlet'] ?? 0);
        $isEdit   = ($idOutlet > 0);

        $nama_outlet         = trim($data['nama_outlet']         ?? '');
        $id_investor         = intval($data['id_investor']       ?? 0);
        $id_wilayah          = !empty($data['id_wilayah']) ? intval($data['id_wilayah']) : null;
        $alamat_outlet       = trim($data['alamat_outlet']       ?? '');
        $persentase_potongan = floatval(str_replace(',', '.', $data['persentase_potongan'] ?? '10.00'));
        $persentase_hak_investor = floatval(str_replace(',', '.', $data['persentase_hak_investor'] ?? '50.00'));
        $tgl_jatuh_tempo     = trim($data['tgl_jatuh_tempo']     ?? '');
        $kasir_nama          = trim($data['kasir_nama']          ?? '');
        $kasir_username      = trim($data['kasir_username']      ?? '');
        $kasir_password      = trim($data['kasir_password']      ?? '');
        $kasir_no_hp         = trim($data['kasir_no_hp']         ?? '');

        if (empty($nama_outlet) || empty($id_investor) || empty($kasir_nama) || empty($kasir_username)) {
            return ['success' => false, 'message' => "Semua kolom utama wajib diisi"];
        }

        if (!$isEdit && empty($kasir_password)) {
            return ['success' => false, 'message' => "Password kasir wajib diisi saat mendaftar outlet baru"];
        }

        if (!empty($kasir_password)) {
            $check_password = Helper::validation_password($kasir_password);
            if ($check_password !== true) {
                return ['success' => false, 'message' => $check_password];
            }
        }

        // Validasi pembatasan wilayah: wilayah Outlet harus di provinsi yang sama dengan Investor
        if ($id_investor > 0 && $id_wilayah > 0) {
            $checkInvWilayah = $db->query("
                SELECT mw_inv.provinsi as prov_investor, mw_out.provinsi as prov_outlet
                FROM investor inv
                JOIN users u_inv ON u_inv.id_users = inv.id_users
                LEFT JOIN master_wilayah mw_inv ON mw_inv.id_wilayah = u_inv.id_wilayah
                CROSS JOIN master_wilayah mw_out ON mw_out.id_wilayah = {$id_wilayah}
                WHERE inv.id_investor = {$id_investor}
                LIMIT 1
            ");
            if ($checkInvWilayah && $rowIW = $checkInvWilayah->fetch_assoc()) {
                if (!empty($rowIW['prov_investor']) && !empty($rowIW['prov_outlet'])) {
                    if (strcasecmp(trim($rowIW['prov_investor']), trim($rowIW['prov_outlet'])) !== 0) {
                        return ['success' => false, 'message' => "Wilayah outlet harus berada di provinsi yang sama dengan Investor (" . $rowIW['prov_investor'] . ")"];
                    }
                }
            }
        }

        // Proses unggah berkas bukti pembayaran / kuitansi (opsional)
        $buktiPath = null;
        if (isset($files['bukti_pembayaran']) && $files['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath   = $files['bukti_pembayaran']['tmp_name'];
            $fileName      = $files['bukti_pembayaran']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $targetDir = defined('CRM_ROOT') ? CRM_ROOT . '/uploads/bukti_pembayaran/' : dirname(dirname(__DIR__)) . '/admin/uploads/bukti_pembayaran/';
                if (!is_dir($targetDir)) {
                    @mkdir($targetDir, 0777, true);
                }

                $newFileName = 'bukti_manual_admin_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
                $destPath = $targetDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $buktiPath = 'uploads/bukti_pembayaran/' . $newFileName;
                }
            }
        }

        // Ambil nominal biaya langganan investor untuk catatan transfer
        $resInv = $db->query("SELECT biaya_langganan_outlet FROM investor WHERE id_investor = {$id_investor} LIMIT 1");
        $nominalLangganan = ($resInv && $rowInv = $resInv->fetch_assoc()) ? (int)($rowInv['biaya_langganan_outlet'] ?? 100000) : 100000;

        $namaSafe      = $db->real_escape_string($nama_outlet);
        $wilayahVal    = $id_wilayah ? $id_wilayah : "NULL";
        $alamatSafe    = $db->real_escape_string($alamat_outlet);
        $kasirNama     = $db->real_escape_string($kasir_nama);
        $kasirUser     = $db->real_escape_string($kasir_username);
        $kasirHp       = $db->real_escape_string($kasir_no_hp);

        $db->begin_transaction();
        try {
            if ($isEdit) {
                $resOut = $db->query("SELECT id_outlet, id_users, bukti_pembayaran FROM outlet WHERE id_outlet = {$idOutlet} LIMIT 1");
                if (!$resOut || $resOut->num_rows == 0) {
                    throw new Exception("Data cabang toko tidak ditemukan");
                }
                $outletRow    = $resOut->fetch_assoc();
                $existingUser = intval($outletRow['id_users']);

                $chkUser = $db->query("SELECT id_users FROM users WHERE LOWER(username) = LOWER('{$kasirUser}') AND id_users != {$existingUser} LIMIT 1");
                if ($chkUser && $chkUser->num_rows > 0) {
                    throw new Exception("Username kasir '{$kasir_username}' sudah digunakan oleh akun lain");
                }

                if (!empty($kasir_password)) {
                    $hashedPass = password_hash($kasir_password, PASSWORD_BCRYPT);
                    $hashSafe   = $db->real_escape_string($hashedPass);
                    $db->query("UPDATE users SET nama_lengkap = '{$kasirNama}', username = '{$kasirUser}', no_hp = '{$kasirHp}', id_wilayah = {$wilayahVal}, alamat_lengkap = '{$alamatSafe}', password = '{$hashSafe}' WHERE id_users = {$existingUser}");
                } else {
                    $db->query("UPDATE users SET nama_lengkap = '{$kasirNama}', username = '{$kasirUser}', no_hp = '{$kasirHp}', id_wilayah = {$wilayahVal}, alamat_lengkap = '{$alamatSafe}' WHERE id_users = {$existingUser}");
                }

                $tglClause = "";
                if (!empty($tgl_jatuh_tempo)) {
                    $tglSafe = $db->real_escape_string($tgl_jatuh_tempo);
                    $statusUpdate = (strtotime($tgl_jatuh_tempo) >= strtotime(date('Y-m-d'))) ? ", status = 'active'" : "";
                    $tglClause = ", tgl_jatuh_tempo = '{$tglSafe}' {$statusUpdate}";
                }

                $buktiClause = "";
                if (!empty($buktiPath)) {
                    // Hapus berkas lama jika ada berkas baru diunggah
                    $oldFile = trim($outletRow['bukti_pembayaran'] ?? '');
                    if (!empty($oldFile) && $oldFile !== $buktiPath) {
                        $pathAdmin = (defined('CRM_ROOT') ? CRM_ROOT : dirname(dirname(__DIR__)) . '/admin') . '/' . $oldFile;
                        $pathClient = (defined('WEB_ROOT') ? WEB_ROOT : dirname(dirname(__DIR__)) . '/client') . '/' . $oldFile;
                        if (file_exists($pathAdmin)) @unlink($pathAdmin);
                        if (file_exists($pathClient)) @unlink($pathClient);
                    }
                    $buktiSafe = $db->real_escape_string($buktiPath);
                    $buktiClause = ", bukti_pembayaran = '{$buktiSafe}'";

                    // Update bukti pada riwayat terakhir
                    $db->query("UPDATE riwayat_langganan SET bukti_pembayaran = '{$buktiSafe}' WHERE id_outlet = {$idOutlet} ORDER BY id_riwayat DESC LIMIT 1");
                }

                $db->query("UPDATE outlet SET nama_outlet = '{$namaSafe}', id_investor = {$id_investor}, persentase_potongan = {$persentase_potongan}, persentase_hak_investor = {$persentase_hak_investor} {$tglClause} {$buktiClause} WHERE id_outlet = {$idOutlet}");

                $db->commit();
                return ['success' => true, 'message' => "Berhasil memperbarui data outlet dan akun kasir: {$nama_outlet}"];

            } else {
                $chkUser = $db->query("SELECT id_users FROM users WHERE LOWER(username) = LOWER('{$kasirUser}') LIMIT 1");
                if ($chkUser && $chkUser->num_rows > 0) {
                    throw new Exception("Username kasir '{$kasir_username}' sudah digunakan oleh akun lain");
                }

                $hashedPass = password_hash($kasir_password, PASSWORD_BCRYPT);
                $hashSafe   = $db->real_escape_string($hashedPass);
                $db->query("INSERT INTO users (nama_lengkap, username, no_hp, id_wilayah, alamat_lengkap, password, role) VALUES ('{$kasirNama}', '{$kasirUser}', '{$kasirHp}', {$wilayahVal}, '{$alamatSafe}', '{$hashSafe}', 'outlet')");

                if ($db->affected_rows < 1) throw new Exception("Gagal membuat akun kasir baru: " . $db->error);
                $newKasirId = $db->insert_id;

                $tglJatuhTempoVal = !empty($tgl_jatuh_tempo) ? "'{$db->real_escape_string($tgl_jatuh_tempo)}'" : "DATE_ADD(NOW(), INTERVAL 1 MONTH)";
                $buktiDbVal = !empty($buktiPath) ? "'{$db->real_escape_string($buktiPath)}'" : "NULL";
                
                $db->query("INSERT INTO outlet (id_users, id_investor, nama_outlet, persentase_potongan, persentase_hak_investor, nominal_transfer, bukti_pembayaran, status, tgl_request, tgl_disetujui, tgl_jatuh_tempo) VALUES ({$newKasirId}, {$id_investor}, '{$namaSafe}', {$persentase_potongan}, {$persentase_hak_investor}, {$nominalLangganan}, {$buktiDbVal}, 'active', NOW(), NOW(), {$tglJatuhTempoVal})");

                if ($db->affected_rows < 1) {
                    throw new Exception("Gagal mendaftarkan cabang outlet baru: " . $db->error);
                }
                $newOutletId = $db->insert_id;

                // Catat riwayat pendaftaran awal
                $escapedBukti = !empty($buktiPath) ? $db->real_escape_string($buktiPath) : '';
                $db->query("INSERT INTO riwayat_langganan (id_outlet, tipe_request, nominal_transfer, bukti_pembayaran, status, tgl_request, tgl_disetujui) VALUES ({$newOutletId}, 'baru', {$nominalLangganan}, '{$escapedBukti}', 'active', NOW(), NOW())");

                $db->commit();
                return ['success' => true, 'message' => "Berhasil mendaftarkan toko cabang baru beserta akun kasir: {$nama_outlet}"];
            }
        } catch (\Throwable $e) {
            $db->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    public static function deleteOutlet($idOutlet) {
        $db = Database::connect();
        $id = intval($idOutlet);
        
        if ($id <= 0) return ['success' => false, 'message' => "ID Outlet tidak valid"];

        $resOutlet = $db->query("SELECT id_users, bukti_pembayaran FROM outlet WHERE id_outlet = {$id} LIMIT 1");
        if (!$resOutlet || $resOutlet->num_rows == 0) {
            return ['success' => false, 'message' => "Data outlet tidak ditemukan"];
        }
        
        $rowOutlet = $resOutlet->fetch_assoc();
        $userId = intval($rowOutlet['id_users'] ?? 0);
        $buktiPembayaran = trim($rowOutlet['bukti_pembayaran'] ?? '');

        $db->begin_transaction();
        try {
            $db->query("DELETE FROM laporan_omzet WHERE id_outlet = {$id}");
            $db->query("DELETE FROM riwayat_langganan WHERE id_outlet = {$id}");
            $db->query("DELETE FROM outlet WHERE id_outlet = {$id}");
            if ($userId > 0) {
                $db->query("DELETE FROM users WHERE id_users = {$userId}");
            }

            if (!empty($buktiPembayaran)) {
                $path1 = WEB_ROOT . '/' . $buktiPembayaran;
                $path2 = CRM_ROOT . '/' . $buktiPembayaran;
                if (file_exists($path1)) @unlink($path1);
                if (file_exists($path2)) @unlink($path2);
            }

            $db->commit();
            return ['success' => true, 'message' => "Berhasil menghapus toko cabang outlet"];
        } catch (\Throwable $e) {
            $db->rollback();
            return ['success' => false, 'message' => "Gagal menghapus cabang outlet: " . $e->getMessage()];
        }
    }
    public static function acceptRequest($idOutlet) {
        $db = Database::connect();
        $id = intval($idOutlet);
        if ($id <= 0) return ['success' => false, 'message' => "ID Outlet tidak valid"];

        $resOut = $db->query("SELECT * FROM outlet WHERE id_outlet = {$id} LIMIT 1");
        if (!$resOut || $resOut->num_rows === 0) {
            return ['success' => false, 'message' => "Outlet tidak ditemukan"];
        }
        $out = $resOut->fetch_assoc();

        $sql = "UPDATE outlet SET status = 'active', tgl_disetujui = NOW(), tgl_jatuh_tempo = DATE_ADD(GREATEST(NOW(), IFNULL(tgl_jatuh_tempo, NOW())), INTERVAL 1 MONTH) WHERE id_outlet = {$id}";
        if ($db->query($sql)) {
            $db->query("UPDATE riwayat_langganan SET status = 'active', tgl_disetujui = NOW() WHERE id_outlet = {$id} AND status = 'pending' ORDER BY id_riwayat DESC LIMIT 1");
            
            if ($db->affected_rows === 0) {
                $nominal = (int)($out['nominal_transfer'] ?? 100000);
                if ($nominal <= 0) $nominal = 100000;
                $bukti = $db->real_escape_string($out['bukti_pembayaran'] ?? '');
                $tipe = ($out['tipe_request'] ?? 'baru') === 'perpanjangan' ? 'perpanjangan' : 'baru';
                $tglReq = !empty($out['tgl_request']) ? "'{$out['tgl_request']}'" : "NOW()";
                $db->query("INSERT INTO riwayat_langganan (id_outlet, tipe_request, nominal_transfer, bukti_pembayaran, status, tgl_request, tgl_disetujui) VALUES ({$id}, '{$tipe}', {$nominal}, '{$bukti}', 'active', {$tglReq}, NOW())");
            }
            return ['success' => true, 'message' => 'Request outlet & pembayaran berhasil disetujui. Outlet kini resmi aktif!'];
        }
        return ['success' => false, 'message' => 'Gagal mengaktifkan outlet: ' . $db->error];
    }
    public static function rejectRequest($idOutlet, $alasan) {
        $db = Database::connect();
        $id = intval($idOutlet);
        if ($id <= 0) return ['success' => false, 'message' => "ID Outlet tidak valid"];

        $resOut = $db->query("SELECT * FROM outlet WHERE id_outlet = {$id} LIMIT 1");
        if (!$resOut || $resOut->num_rows === 0) {
            return ['success' => false, 'message' => "Outlet tidak ditemukan"];
        }
        $out = $resOut->fetch_assoc();

        $escapedAlasan = $db->real_escape_string(trim($alasan));
        $sql = "UPDATE outlet SET status = 'reject', alasan_penolakan = '{$escapedAlasan}', tgl_ditolak = NOW() WHERE id_outlet = {$id}";
        if ($db->query($sql)) {
            $db->query("UPDATE riwayat_langganan SET status = 'reject', alasan_penolakan = '{$escapedAlasan}' WHERE id_outlet = {$id} AND status = 'pending' ORDER BY id_riwayat DESC LIMIT 1");
            
            if ($db->affected_rows === 0) {
                $nominal = (int)($out['nominal_transfer'] ?? 100000);
                if ($nominal <= 0) $nominal = 100000;
                $bukti = $db->real_escape_string($out['bukti_pembayaran'] ?? '');
                $tipe = ($out['tipe_request'] ?? 'baru') === 'perpanjangan' ? 'perpanjangan' : 'baru';
                $tglReq = !empty($out['tgl_request']) ? "'{$out['tgl_request']}'" : "NOW()";
                $db->query("INSERT INTO riwayat_langganan (id_outlet, tipe_request, nominal_transfer, bukti_pembayaran, status, alasan_penolakan, tgl_request, tgl_disetujui) VALUES ({$id}, '{$tipe}', {$nominal}, '{$bukti}', 'reject', '{$escapedAlasan}', {$tglReq}, NOW())");
            }
            return ['success' => true, 'message' => 'Request outlet & pembayaran berhasil ditolak.'];
        }
        return ['success' => false, 'message' => 'Gagal menolak outlet: ' . $db->error];
    }
    public static function updateRejectReason($idOutlet, $alasan) {
        $db = Database::connect();
        $id = intval($idOutlet);
        $alasan = trim($alasan);
        if ($id <= 0) return ['success' => false, 'message' => "ID Outlet tidak valid"];
        if (empty($alasan)) return ['success' => false, 'message' => "Alasan penolakan tidak boleh kosong"];

        $escapedAlasan = $db->real_escape_string($alasan);
        $sql = "UPDATE outlet SET alasan_penolakan = '{$escapedAlasan}' WHERE id_outlet = {$id} AND status = 'reject'";
        if ($db->query($sql)) {
            $db->query("UPDATE riwayat_langganan SET alasan_penolakan = '{$escapedAlasan}' WHERE id_outlet = {$id} AND status = 'reject' ORDER BY id_riwayat DESC LIMIT 1");
            return ['success' => true, 'message' => 'Alasan penolakan outlet berhasil diperbarui.'];
        }
        return ['success' => false, 'message' => 'Gagal memperbarui alasan penolakan: ' . $db->error];
    }

    // ==============================================
    // OMZET & SUBSCRIPTION FEE MONITORING METHODS
    // ==============================================
    public static function getOutletOmzetDetail($idOutlet, $bulan = 0, $tahun = 0) {
        $db = Database::connect();
        $id = intval($idOutlet);
        if ($id <= 0) return ['outlet' => null, 'transaksi' => [], 'summary' => []];

        // 1. Fetch Outlet & Investor Info
        $resOutlet = $db->query("
            SELECT o.id_outlet, o.nama_outlet, o.persentase_potongan, o.persentase_hak_investor, o.status,
                   u_kasir.nama_lengkap as pengelola_toko, u_kasir.no_hp as no_hp_toko, u_kasir.alamat_lengkap as alamat_outlet,
                   mw.kelurahan, mw.kecamatan, mw.kabupaten, mw.provinsi,
                   inv.id_investor, inv.biaya_langganan_outlet,
                   u_inv.nama_lengkap as nama_investor, u_inv.username as username_investor, u_inv.no_hp as no_hp_investor
            FROM outlet o
            LEFT JOIN users u_kasir ON u_kasir.id_users = o.id_users
            LEFT JOIN master_wilayah mw ON mw.id_wilayah = u_kasir.id_wilayah
            LEFT JOIN investor inv ON inv.id_investor = o.id_investor
            LEFT JOIN users u_inv ON u_inv.id_users = inv.id_users
            WHERE o.id_outlet = {$id}
            LIMIT 1
        ");
        $outletInfo = ($resOutlet && $resOutlet->num_rows > 0) ? $resOutlet->fetch_assoc() : null;

        // 2. Build Where Clause for Laporan Omzet
        $where = ["id_outlet = {$id}"];
        if ($bulan > 0) {
            $where[] = "MONTH(tanggal_omzet) = " . intval($bulan);
        }
        if ($tahun > 0) {
            $where[] = "YEAR(tanggal_omzet) = " . intval($tahun);
        }
        $whereSql = implode(" AND ", $where);

        // 3. Fetch Transactions
        $sqlTrx = "
            SELECT id_laporan, id_outlet, tanggal_omzet, nominal_omzet, persentase_potongan, persentase_hak_investor, nominal_potongan, created_at
            FROM laporan_omzet
            WHERE {$whereSql}
            ORDER BY tanggal_omzet DESC, id_laporan DESC
        ";
        $resTrx = $db->query($sqlTrx);
        $transaksi = [];
        $totalOmzet = 0;
        $totalPotongan = 0;
        $totalHakInvestor = 0;
        $totalHakOutlet = 0;

        if ($resTrx && $resTrx->num_rows > 0) {
            while ($row = $resTrx->fetch_assoc()) {
                $omzet = (float)($row['nominal_omzet'] ?? 0);
                $pctPot = (float)($row['persentase_potongan'] ?? 0);
                $pctInv = (float)($row['persentase_hak_investor'] ?? 0);
                
                $nomPotongan = (float)($row['nominal_potongan'] ?? (($omzet * $pctPot) / 100));
                $nomHakInvestor = ($nomPotongan * $pctInv) / 100;
                $nomHakOutlet = $nomPotongan - $nomHakInvestor;
                $nomModalBelanja = max(0, $omzet - $nomPotongan);
                $nomBersihTotal = $nomModalBelanja + $nomHakOutlet;

                $row['nominal_hak_investor'] = $nomHakInvestor;
                $row['nominal_hak_outlet'] = $nomHakOutlet;
                $row['nominal_modal_belanja'] = $nomModalBelanja;
                $row['nominal_bersih_outlet'] = $nomBersihTotal;

                $transaksi[] = $row;
                $totalOmzet += $omzet;
                $totalPotongan += $nomPotongan;
                $totalHakInvestor += $nomHakInvestor;
                $totalHakOutlet += $nomHakOutlet;
            }
        }

        $summary = [
            'total_omzet'        => $totalOmzet,
            'total_potongan'     => $totalPotongan,
            'total_hak_investor' => $totalHakInvestor,
            'total_hak_outlet'   => $totalHakOutlet,
            'total_transaksi'    => count($transaksi),
            'rata_rata_omzet'    => count($transaksi) > 0 ? ($totalOmzet / count($transaksi)) : 0
        ];

        return [
            'outlet'    => $outletInfo,
            'transaksi' => $transaksi,
            'summary'   => $summary
        ];
    }

    public static function getOutletOmzetMonitoring($bulan = 0, $tahun = 0) {
        $db = Database::connect();
        $bulan = intval($bulan);
        $tahun = intval($tahun);

        $whereOmzet = "1=1";
        if ($bulan > 0) {
            $whereOmzet .= " AND MONTH(tanggal_omzet) = {$bulan}";
        }
        if ($tahun > 0) {
            $whereOmzet .= " AND YEAR(tanggal_omzet) = {$tahun}";
        }

        $sql = "
            SELECT o.*, u_kasir.nama_lengkap as pengelola_toko, u_kasir.username as username_kasir, u_kasir.no_hp as no_hp_toko, 
                   mw.provinsi, mw.kabupaten, mw.kecamatan, mw.kelurahan, u_kasir.alamat_lengkap as alamat_outlet,
                   inv.id_investor, inv.biaya_langganan_outlet,
                   u_inv.nama_lengkap as nama_investor, u_inv.username as username_investor, u_inv.no_hp as no_hp_investor,
                   COALESCE(omz.total_omzet, 0) as omzet_periode,
                   COALESCE(omz.total_transaksi, 0) as transaksi_periode,
                   COALESCE(omz.total_potongan, 0) as potongan_periode,
                   COALESCE(omz.total_hak_investor, 0) as hak_investor_periode,
                   COALESCE(omz.total_hak_outlet, 0) as hak_outlet_periode,
                   COALESCE(omz.total_modal_belanja, 0) as modal_belanja_periode,
                   COALESCE(omz.total_bersih_outlet, 0) as bersih_outlet_periode
            FROM outlet o
            LEFT JOIN users u_kasir ON u_kasir.id_users = o.id_users
            LEFT JOIN master_wilayah mw ON mw.id_wilayah = u_kasir.id_wilayah
            LEFT JOIN investor inv ON inv.id_investor = o.id_investor
            LEFT JOIN users u_inv ON u_inv.id_users = inv.id_users
            LEFT JOIN (
                SELECT id_outlet, 
                       SUM(nominal_omzet) as total_omzet,
                       COUNT(*) as total_transaksi,
                       SUM(nominal_potongan) as total_potongan,
                       SUM(nominal_potongan * (persentase_hak_investor / 100)) as total_hak_investor,
                       SUM(nominal_potongan * (1 - (persentase_hak_investor / 100))) as total_hak_outlet,
                       SUM(nominal_omzet - nominal_potongan) as total_modal_belanja,
                       SUM(nominal_omzet - (nominal_potongan * (persentase_hak_investor / 100))) as total_bersih_outlet
                FROM laporan_omzet
                WHERE {$whereOmzet}
                GROUP BY id_outlet
            ) omz ON omz.id_outlet = o.id_outlet
            WHERE (o.status = 'active' OR (o.status IN ('pending', 'reject') AND o.tipe_request = 'perpanjangan'))
            ORDER BY omzet_periode DESC, o.nama_outlet ASC
        ";

        $res = $db->query($sql);
        $outlets = [];
        $grandTotalOmzet = 0;
        $grandTotalPotongan = 0;
        $grandTotalHakInvestor = 0;
        $grandTotalHakOutlet = 0;
        $grandTotalTransaksi = 0;

        if ($res && $res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                $grandTotalOmzet += (float)$row['omzet_periode'];
                $grandTotalPotongan += (float)$row['potongan_periode'];
                $grandTotalHakInvestor += (float)$row['hak_investor_periode'];
                $grandTotalHakOutlet += (float)$row['hak_outlet_periode'];
                $grandTotalTransaksi += (int)$row['transaksi_periode'];
                $outlets[] = $row;
            }
        }

        return [
            'outlets' => $outlets,
            'summary' => [
                'grand_total_omzet'        => $grandTotalOmzet,
                'grand_total_potongan'     => $grandTotalPotongan,
                'grand_total_hak_investor' => $grandTotalHakInvestor,
                'grand_total_hak_outlet'   => $grandTotalHakOutlet,
                'grand_total_transaksi'    => $grandTotalTransaksi,
                'total_outlet'             => count($outlets),
                'rata_rata_omzet'          => count($outlets) > 0 ? ($grandTotalOmzet / count($outlets)) : 0
            ]
        ];
    }

    public static function updateBiayaLanggananInvestor($idInvestor, $biayaBaru) {
        $db = Database::connect();
        $id = intval($idInvestor);
        $biaya = intval($biayaBaru);
        if ($id <= 0) return ['success' => false, 'message' => "ID Investor tidak valid"];
        if ($biaya < 0) return ['success' => false, 'message' => "Biaya langganan tidak boleh bernilai negatif"];

        $sql = "UPDATE investor SET biaya_langganan_outlet = {$biaya} WHERE id_investor = {$id}";
        if ($db->query($sql)) {
            return [
                'success' => true, 
                'message' => 'Tarif biaya langganan outlet berhasil diperbarui menjadi Rp ' . number_format($biaya, 0, ',', '.') . ' / bulan'
            ];
        }
        return ['success' => false, 'message' => 'Gagal memperbarui biaya langganan: ' . $db->error];
    }

    public static function getAvailableTahunOmzet() {
        $db = Database::connect();
        $listTahun = [];
        try {
            $res = $db->query("SELECT DISTINCT YEAR(tanggal_omzet) as tahun FROM laporan_omzet WHERE tanggal_omzet IS NOT NULL AND tanggal_omzet > '1970-01-01' ORDER BY tahun DESC");
            if ($res && $res->num_rows > 0) {
                while ($row = $res->fetch_assoc()) {
                    if (!empty($row['tahun'])) {
                        $listTahun[] = intval($row['tahun']);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Fallback
        }
        if (empty($listTahun)) {
            $listTahun[] = intval(date('Y'));
        }
        return $listTahun;
    }

    public static function getOutletHistoriHeader(int $idOutlet) {
        $db = Database::connect();
        $id = intval($idOutlet);
        $sql = "
            SELECT o.id_outlet, o.nama_outlet, o.tgl_jatuh_tempo, o.status,
                   u_kasir.nama_lengkap as nama_kasir, u_kasir.username as username_kasir, u_kasir.no_hp as no_hp_kasir,
                   inv.id_investor, inv.biaya_langganan_outlet,
                   u_inv.nama_lengkap as nama_investor, u_inv.username as username_investor,
                   mw.provinsi, mw.kabupaten, mw.kecamatan, mw.kelurahan, u_kasir.alamat_lengkap as alamat_outlet
            FROM outlet o
            LEFT JOIN users u_kasir ON u_kasir.id_users = o.id_users
            LEFT JOIN master_wilayah mw ON mw.id_wilayah = u_kasir.id_wilayah
            LEFT JOIN investor inv ON inv.id_investor = o.id_investor
            LEFT JOIN users u_inv ON u_inv.id_users = inv.id_users
            WHERE o.id_outlet = {$id}
            LIMIT 1
        ";
        $res = $db->query($sql);
        return ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;
    }

    public static function getRiwayatLanggananByOutlet(int $idOutlet) {
        $db = Database::connect();
        $id = intval($idOutlet);
        $sql = "SELECT * FROM riwayat_langganan WHERE id_outlet = {$id} ORDER BY id_riwayat DESC";
        return $db->query($sql);
    }
}

