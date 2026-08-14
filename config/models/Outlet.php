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

        $resActive = $db->query("SELECT COUNT(*) as total FROM outlet WHERE status = 'active' AND (tgl_jatuh_tempo IS NULL OR DATE(tgl_jatuh_tempo) >= CURRENT_DATE())");
        if ($resActive && $resActive->num_rows > 0) {
            $stats['activeCount'] = (int)$resActive->fetch_assoc()['total'];
        }

        $resExpired = $db->query("SELECT COUNT(*) as total FROM outlet WHERE (status = 'active' AND DATE(tgl_jatuh_tempo) < CURRENT_DATE()) OR status = 'inactive'");
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
            SELECT o.*, u_kasir.nama_lengkap as pengelola_toko, u_kasir.no_hp as no_hp_toko, mw.provinsi, mw.kabupaten, mw.kecamatan, mw.kelurahan, u_kasir.alamat_lengkap as alamat_outlet,
                   u_inv.nama_lengkap as nama_investor, u_inv.no_hp as no_hp_investor
            FROM outlet o
            LEFT JOIN users u_kasir ON u_kasir.id_users = o.id_users
            LEFT JOIN master_wilayah mw ON mw.id_wilayah = u_kasir.id_wilayah
            LEFT JOIN investor inv ON inv.id_investor = o.id_investor
            LEFT JOIN users u_inv ON u_inv.id_users = inv.id_users
            WHERE o.status = 'active' AND (o.tgl_jatuh_tempo IS NULL OR DATE(o.tgl_jatuh_tempo) >= CURRENT_DATE())
            ORDER BY o.nama_outlet ASC
        ";
        return $db->query($sql);
    }
    public static function getExpiredOutlets() {
        $db = Database::connect();
        $sql = "
            SELECT o.*, u_kasir.nama_lengkap as pengelola_toko, u_kasir.no_hp as no_hp_toko, mw.provinsi, mw.kabupaten, mw.kecamatan, mw.kelurahan, u_kasir.alamat_lengkap as alamat_outlet,
                   u_inv.nama_lengkap as nama_investor, u_inv.no_hp as no_hp_investor
            FROM outlet o
            LEFT JOIN users u_kasir ON u_kasir.id_users = o.id_users
            LEFT JOIN master_wilayah mw ON mw.id_wilayah = u_kasir.id_wilayah
            LEFT JOIN investor inv ON inv.id_investor = o.id_investor
            LEFT JOIN users u_inv ON u_inv.id_users = inv.id_users
            WHERE (o.status = 'active' AND DATE(o.tgl_jatuh_tempo) < CURRENT_DATE()) OR o.status = 'inactive'
            ORDER BY o.tgl_jatuh_tempo DESC, o.nama_outlet ASC
        ";
        return $db->query($sql);
    }
    public static function getPendingOutlets() {
        $db = Database::connect();
        $sql = "
            SELECT o.*, u_kasir.nama_lengkap as pengelola_toko, u_kasir.no_hp as no_hp_toko, mw.provinsi, mw.kabupaten, mw.kecamatan, mw.kelurahan, u_kasir.alamat_lengkap as alamat_outlet,
                   u_inv.nama_lengkap as nama_investor, u_inv.no_hp as no_hp_investor
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
            SELECT o.*, u_kasir.nama_lengkap as pengelola_toko, u_kasir.no_hp as no_hp_toko, mw.provinsi, mw.kabupaten, mw.kecamatan, mw.kelurahan, u_kasir.alamat_lengkap as alamat_outlet,
                   u_inv.nama_lengkap as nama_investor, u_inv.no_hp as no_hp_investor
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
            SELECT o.*, u.nama_lengkap as kasir_nama, u.username as kasir_username, u.no_hp as kasir_no_hp, mw.provinsi, mw.kabupaten, mw.kecamatan, mw.kelurahan, u.alamat_lengkap as alamat_outlet, u.id_wilayah
            FROM outlet o
            JOIN users u ON (u.id_users = o.id_users)
            LEFT JOIN master_wilayah mw ON (u.id_wilayah = mw.id_wilayah)
            WHERE o.id_outlet = {$id}
            LIMIT 1
        ";
        $res = $db->query($sql);
        return ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;
    }

    // ==============================================
    // WRITE (SAVE / DELETE / UPDATE) METHODS
    // ==============================================
    public static function saveOutlet($data, $currentUserId = 1) {
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

        $namaSafe      = $db->real_escape_string($nama_outlet);
        $wilayahVal    = $id_wilayah ? $id_wilayah : "NULL";
        $alamatSafe    = $db->real_escape_string($alamat_outlet);
        $kasirNama     = $db->real_escape_string($kasir_nama);
        $kasirUser     = $db->real_escape_string($kasir_username);
        $kasirHp       = $db->real_escape_string($kasir_no_hp);

        $db->begin_transaction();
        try {
            if ($isEdit) {
                $resOut = $db->query("SELECT id_outlet, id_users FROM outlet WHERE id_outlet = {$idOutlet} LIMIT 1");
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

                $db->query("UPDATE outlet SET nama_outlet = '{$namaSafe}', id_investor = {$id_investor}, persentase_potongan = {$persentase_potongan}, persentase_hak_investor = {$persentase_hak_investor} {$tglClause} WHERE id_outlet = {$idOutlet}");

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
                $db->query("INSERT INTO outlet (id_users, id_investor, nama_outlet, persentase_potongan, persentase_hak_investor, status, tgl_request, tgl_disetujui, tgl_jatuh_tempo) VALUES ({$newKasirId}, {$id_investor}, '{$namaSafe}', {$persentase_potongan}, {$persentase_hak_investor}, 'active', NOW(), NOW(), {$tglJatuhTempoVal})");

                if ($db->affected_rows < 1) {
                    throw new Exception("Gagal mendaftarkan cabang outlet baru: " . $db->error);
                }

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

        $sql = "UPDATE outlet SET status = 'active', tgl_disetujui = NOW(), tgl_jatuh_tempo = DATE_ADD(GREATEST(NOW(), IFNULL(tgl_jatuh_tempo, NOW())), INTERVAL 1 MONTH) WHERE id_outlet = {$id}";
        if ($db->query($sql)) {
            $db->query("UPDATE riwayat_langganan SET status = 'active', tgl_disetujui = NOW() WHERE id_outlet = {$id} AND status = 'pending' ORDER BY id_riwayat DESC LIMIT 1");
            return ['success' => true, 'message' => 'Request outlet & pembayaran berhasil disetujui. Outlet kini resmi aktif!'];
        }
        return ['success' => false, 'message' => 'Gagal mengaktifkan outlet: ' . $db->error];
    }
    public static function rejectRequest($idOutlet, $alasan) {
        $db = Database::connect();
        $id = intval($idOutlet);
        if ($id <= 0) return ['success' => false, 'message' => "ID Outlet tidak valid"];

        $escapedAlasan = $db->real_escape_string(trim($alasan));
        $sql = "UPDATE outlet SET status = 'reject', alasan_penolakan = '{$escapedAlasan}', tgl_ditolak = NOW() WHERE id_outlet = {$id}";
        if ($db->query($sql)) {
            $db->query("UPDATE riwayat_langganan SET status = 'reject', alasan_penolakan = '{$escapedAlasan}' WHERE id_outlet = {$id} AND status = 'pending' ORDER BY id_riwayat DESC LIMIT 1");
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
}
