<?php
namespace App\Models;

use Config\Core\Database;
use App\Models\Helper;
use Exception;

class Investor {

    // ==============================================
    // READ (GET) METHODS
    // ==============================================
    public static function getAllInvestors($loggedInLevel = 1, $loggedInId = 1) {
        $db = Database::connect();
        
        $whereClause = "";
        if ($loggedInLevel == 2) {
            $whereClause = "WHERE i.id_master = {$loggedInId}";
        } elseif ($loggedInLevel != 1) {
            $whereClause = "WHERE i.id_master IN (SELECT id_users FROM users WHERE role = 'master')";
        }

        $sql = "
            SELECT i.*, u.nama_lengkap, u.username, u.no_hp, mw.provinsi, mw.kabupaten, mw.kecamatan, mw.kelurahan, u.alamat_lengkap as alamat_investor, u.created_at as tanggal_bergabung,
                   u_master.nama_lengkap as nama_master,
                   COUNT(DISTINCT o.id_outlet) as total_outlet
            FROM investor i
            JOIN users u ON (u.id_users = i.id_users)
            LEFT JOIN master_wilayah mw ON (u.id_wilayah = mw.id_wilayah)
            LEFT JOIN users u_master ON (u_master.id_users = i.id_master)
            LEFT JOIN outlet o ON (o.id_investor = i.id_investor AND o.status = 'active' AND (o.tgl_jatuh_tempo IS NULL OR DATE(o.tgl_jatuh_tempo) >= CURRENT_DATE()))
            {$whereClause}
            GROUP BY i.id_investor
            ORDER BY u.nama_lengkap ASC
        ";
        return $db->query($sql);
    }
    public static function getInvestorById($idInvestor) {
        $db = Database::connect();
        $id = intval($idInvestor);
        
        $sql = "
            SELECT i.*, u.nama_lengkap, u.username, u.no_hp, mw.provinsi, mw.kabupaten, mw.kecamatan, mw.kelurahan, u.alamat_lengkap as alamat_investor, u.id_wilayah
            FROM investor i
            JOIN users u ON u.id_users = i.id_users
            LEFT JOIN master_wilayah mw ON u.id_wilayah = mw.id_wilayah
            WHERE i.id_investor = {$id}
            LIMIT 1
        ";
        $res = $db->query($sql);
        return ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;
    }

    // ==============================================
    // WRITE (SAVE / DELETE) METHODS
    // ==============================================
    public static function saveInvestor($data, $currentUserId = 1) {
        $db = Database::connect();
        $idInvestor = intval($data['id_investor'] ?? 0);
        $isEdit = ($idInvestor > 0);

        $nama_lengkap = trim($data['nama_lengkap'] ?? '');
        $username     = trim($data['username'] ?? '');
        $password     = trim($data['password'] ?? '');
        $no_hp        = !empty($data['no_hp']) ? trim($data['no_hp']) : null;
        $id_wilayah   = !empty($data['id_wilayah']) ? intval($data['id_wilayah']) : null;
        $alamat       = !empty($data['alamat_lengkap']) ? trim($data['alamat_lengkap']) : null;
        
        $biayaRaw        = preg_replace('/[^0-9]/', '', $data['biaya_langganan_outlet'] ?? '100000');
        $biayaLangganan = intval($biayaRaw);

        if (empty($nama_lengkap) || empty($username)) {
            return ['success' => false, 'message' => "Nama Lengkap dan Username wajib diisi"];
        }

        if (!$isEdit && empty($password)) {
            return ['success' => false, 'message' => "Password wajib diisi untuk investor baru"];
        }

        if (!empty($password)) {
            $check_password = Helper::validation_password($password);
            if ($check_password !== true) {
                return ['success' => false, 'message' => $check_password];
            }
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return ['success' => false, 'message' => "Username tidak valid, hanya boleh huruf dan angka (tanpa spasi)"];
        }

        $nameSafe     = $db->real_escape_string($nama_lengkap);
        $usernameSafe = $db->real_escape_string($username);
        $hpVal        = $no_hp ? "'" . $db->real_escape_string($no_hp) . "'" : "NULL";
        $wilayahVal   = $id_wilayah ? $id_wilayah : "NULL";
        $alamatVal    = $alamat ? "'" . $db->real_escape_string($alamat) . "'" : "NULL";
        $idMaster     = intval($data['id_master'] ?? $currentUserId);

        $db->begin_transaction();
        try {
            if ($isEdit) {
                // 1. Edit Mode
                $resInv = $db->query("SELECT id_users FROM investor WHERE id_investor = {$idInvestor} LIMIT 1");
                if (!$resInv || $resInv->num_rows == 0) {
                    throw new Exception("Data investor tidak ditemukan");
                }
                $userId = intval($resInv->fetch_assoc()['id_users']);

                $sql_check = $db->query("SELECT id_users FROM users WHERE LOWER(username) = LOWER('{$usernameSafe}') AND id_users != {$userId} LIMIT 1");
                if ($sql_check && $sql_check->num_rows > 0) {
                    throw new Exception("Username '{$username}' sudah digunakan oleh pengguna lain");
                }

                if (!empty($password)) {
                    $hashedPass = password_hash($password, PASSWORD_BCRYPT);
                    $passSafe   = $db->real_escape_string($hashedPass);
                    $resUser = $db->query("UPDATE users SET nama_lengkap = '{$nameSafe}', username = '{$usernameSafe}', no_hp = {$hpVal}, id_wilayah = {$wilayahVal}, alamat_lengkap = {$alamatVal}, password = '{$passSafe}' WHERE id_users = {$userId}");
                } else {
                    $resUser = $db->query("UPDATE users SET nama_lengkap = '{$nameSafe}', username = '{$usernameSafe}', no_hp = {$hpVal}, id_wilayah = {$wilayahVal}, alamat_lengkap = {$alamatVal} WHERE id_users = {$userId}");
                }

                if (!$resUser) throw new Exception("Gagal memperbarui data user: " . $db->error);

                $resInvUpdate = $db->query("UPDATE investor SET id_master = {$idMaster}, biaya_langganan_outlet = {$biayaLangganan} WHERE id_investor = {$idInvestor}");
                if (!$resInvUpdate) throw new Exception("Gagal memperbarui data profil investor: " . $db->error);

                $db->commit();
                return ['success' => true, 'message' => "Berhasil memperbarui data investor: {$nama_lengkap}"];

            } else {
                // 2. Create Mode
                $sql_check = $db->query("SELECT id_users FROM users WHERE LOWER(username) = LOWER('{$usernameSafe}') LIMIT 1");
                if ($sql_check && $sql_check->num_rows > 0) {
                    throw new Exception("Username '{$username}' sudah terdaftar, silakan pilih username lain");
                }

                $hashedPass = password_hash($password, PASSWORD_BCRYPT);
                $passSafe   = $db->real_escape_string($hashedPass);

                $resUserInsert = $db->query("INSERT INTO users (nama_lengkap, username, no_hp, id_wilayah, alamat_lengkap, password, role) VALUES ('{$nameSafe}', '{$usernameSafe}', {$hpVal}, {$wilayahVal}, {$alamatVal}, '{$passSafe}', 'investor')");

                if (!$resUserInsert || $db->affected_rows < 1) {
                    throw new Exception("Gagal membuat akun user investor: " . $db->error);
                }

                $newUserId = $db->insert_id;

                $resInvInsert = $db->query("INSERT INTO investor (id_users, id_master, biaya_langganan_outlet) VALUES ({$newUserId}, {$idMaster}, {$biayaLangganan})");

                if (!$resInvInsert || $db->affected_rows < 1) {
                    throw new Exception("Gagal menyimpan data profil investor: " . $db->error);
                }

                $db->commit();
                return ['success' => true, 'message' => "Berhasil mendaftarkan investor baru: {$nama_lengkap}"];
            }
        } catch (\Throwable $e) {
            $db->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    public static function deleteInvestor($idInvestor) {
        $db = Database::connect();
        $id = intval($idInvestor);
        
        if ($id <= 0) return ['success' => false, 'message' => "ID Investor tidak valid"];

        $resInv = $db->query("SELECT id_users FROM investor WHERE id_investor = {$id} LIMIT 1");
        if (!$resInv || $resInv->num_rows == 0) {
            return ['success' => false, 'message' => "Data investor tidak ditemukan"];
        }

        $userId = intval($resInv->fetch_assoc()['id_users']);

        $db->begin_transaction();
        try {
            $outlets = $db->query("SELECT id_outlet, id_users FROM outlet WHERE id_investor = {$id}");
            if ($outlets && $outlets->num_rows > 0) {
                while ($o = $outlets->fetch_assoc()) {
                    $oId = intval($o['id_outlet']);
                    $oUserId = intval($o['id_users']);
                    $db->query("DELETE FROM laporan_omzet WHERE id_outlet = {$oId}");
                    $db->query("DELETE FROM outlet WHERE id_outlet = {$oId}");
                    if ($oUserId > 0) {
                        $db->query("DELETE FROM users WHERE id_users = {$oUserId}");
                    }
                }
            }

            $db->query("DELETE FROM investor WHERE id_investor = {$id}");

            if ($userId > 0) {
                $db->query("DELETE FROM users WHERE id_users = {$userId}");
            }

            $db->commit();
            return ['success' => true, 'message' => "Data investor beserta outlet terikat berhasil dihapus"];
        } catch (\Throwable $e) {
            $db->rollback();
            return ['success' => false, 'message' => "Gagal menghapus data investor: " . $e->getMessage()];
        }
    }
}
