<?php
namespace App\Models;

use Config\Core\Database;
use App\Models\Helper;
use Exception;

class Master {

    // ==============================================
    // READ (GET) METHODS
    // ==============================================
    public static function getAllMasters() {
        $db = Database::connect();
        $sql = "
            SELECT u.id_users, u.nama_lengkap, u.username, u.no_hp, mw.provinsi, mw.kabupaten, mw.kecamatan, mw.kelurahan, u.alamat_lengkap as alamat, u.created_at,
                   COUNT(DISTINCT inv.id_investor) as total_investor,
                   COUNT(DISTINCT o.id_outlet) as total_outlet
            FROM users u
            LEFT JOIN master_wilayah mw ON (u.id_wilayah = mw.id_wilayah)
            LEFT JOIN investor inv ON inv.id_master = u.id_users
            LEFT JOIN outlet o ON (o.id_investor = inv.id_investor AND (o.status = 'active' OR (o.status IN ('pending', 'reject') AND o.tipe_request = 'perpanjangan')) AND (o.tgl_jatuh_tempo IS NULL OR DATE(o.tgl_jatuh_tempo) >= CURRENT_DATE()))
            WHERE u.role = 'master'
            GROUP BY u.id_users
            ORDER BY u.id_users DESC
        ";
        return $db->query($sql);
    }
    public static function getMasterById($id_users) {
        $db = Database::connect();
        $id = intval($id_users);
        $res = $db->query("
            SELECT u.*, mw.provinsi, mw.kabupaten, mw.kecamatan, mw.kelurahan, mw.kodepos
            FROM users u
            LEFT JOIN master_wilayah mw ON mw.id_wilayah = u.id_wilayah
            WHERE u.id_users = {$id} AND u.role = 'master' LIMIT 1
        ");
        return ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;
    }
    public static function getAllMasterOptions() {
        $db = Database::connect();
        return $db->query("
            SELECT u.id_users, u.nama_lengkap, u.username, mw.provinsi, mw.kabupaten 
            FROM users u 
            LEFT JOIN master_wilayah mw ON mw.id_wilayah = u.id_wilayah 
            WHERE u.role = 'master' 
            ORDER BY u.nama_lengkap ASC
        ");
    }
    public static function getAllKomisi($id_master = null) {
        $db = Database::connect();
        $sql = "
            SELECT km.*, u.nama_lengkap as nama_master, u.username as username_master
            FROM komisi_master km
            JOIN users u ON u.id_users = km.id_master
        ";
        if ($id_master !== null) {
            $id = intval($id_master);
            $sql .= " WHERE km.id_master = {$id}";
        }
        $sql .= " ORDER BY km.tgl_transfer DESC, km.id_komisi DESC";
        return $db->query($sql);
    }
    public static function getKomisiById($id_komisi) {
        $db = Database::connect();
        $id = intval($id_komisi);
        $res = $db->query("SELECT * FROM komisi_master WHERE id_komisi = {$id} LIMIT 1");
        return ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;
    }

    // ==============================================
    // WRITE (SAVE / DELETE) METHODS
    // ==============================================
    public static function saveMaster($data) {
        $db = Database::connect();
        $idUsers = intval($data['id_users'] ?? 0);
        $isEdit  = ($idUsers > 0);

        $nama_lengkap = trim($data['nama_lengkap'] ?? '');
        $username     = trim($data['username'] ?? '');
        $password     = trim($data['password'] ?? '');
        $no_hp        = !empty($data['no_hp']) ? trim($data['no_hp']) : null;
        $id_wilayah   = !empty($data['id_wilayah']) ? intval($data['id_wilayah']) : null;
        $alamat       = !empty($data['alamat']) ? trim($data['alamat']) : null;

        if (empty($nama_lengkap) || empty($username)) {
            return ['success' => false, 'message' => "Nama Lengkap dan Username wajib diisi"];
        }

        if (!$isEdit && empty($password)) {
            return ['success' => false, 'message' => "Password wajib diisi untuk Master baru"];
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

        if ($isEdit) {
            $sql_check = $db->query("SELECT id_users FROM users WHERE LOWER(username) = LOWER('{$usernameSafe}') AND id_users != {$idUsers} LIMIT 1");
            if ($sql_check && $sql_check->num_rows > 0) {
                return ['success' => false, 'message' => "Username '{$username}' sudah digunakan oleh pengguna lain"];
            }

            if (!empty($password)) {
                $hashedPass = password_hash($password, PASSWORD_BCRYPT);
                $passSafe   = $db->real_escape_string($hashedPass);
                $db->query("UPDATE users SET nama_lengkap = '{$nameSafe}', username = '{$usernameSafe}', no_hp = {$hpVal}, id_wilayah = {$wilayahVal}, alamat_lengkap = {$alamatVal}, password = '{$passSafe}' WHERE id_users = {$idUsers} AND role = 'master'");
            } else {
                $db->query("UPDATE users SET nama_lengkap = '{$nameSafe}', username = '{$usernameSafe}', no_hp = {$hpVal}, id_wilayah = {$wilayahVal}, alamat_lengkap = {$alamatVal} WHERE id_users = {$idUsers} AND role = 'master'");
            }

            return ['success' => true, 'message' => "Berhasil memperbarui data Master: {$nama_lengkap}"];
        } else {
            $sql_check = $db->query("SELECT id_users FROM users WHERE LOWER(username) = LOWER('{$usernameSafe}') LIMIT 1");
            if ($sql_check && $sql_check->num_rows > 0) {
                return ['success' => false, 'message' => "Username '{$username}' sudah terdaftar, silakan pilih username lain"];
            }

            $hashedPass = password_hash($password, PASSWORD_BCRYPT);
            $passSafe   = $db->real_escape_string($hashedPass);

            $db->query("INSERT INTO users (nama_lengkap, username, no_hp, id_wilayah, alamat_lengkap, password, role) VALUES ('{$nameSafe}', '{$usernameSafe}', {$hpVal}, {$wilayahVal}, {$alamatVal}, '{$passSafe}', 'master')");

            if ($db->affected_rows < 1) {
                return ['success' => false, 'message' => "Gagal membuat akun Master: " . $db->error];
            }

            return ['success' => true, 'message' => "Berhasil mendaftarkan Master baru: {$nama_lengkap}"];
        }
    }
    public static function deleteMaster($id_users) {
        $db = Database::connect();
        $idUsers = intval($id_users);
        if ($idUsers <= 0) return ['success' => false, 'message' => "ID Master tidak valid"];

        $db->begin_transaction();
        try {
            // 1. Cari semua investor di bawah Master ini
            $investors = $db->query("SELECT id_investor, id_users FROM investor WHERE id_master = {$idUsers}");
            if ($investors && $investors->num_rows > 0) {
                while ($inv = $investors->fetch_assoc()) {
                    $invId = intval($inv['id_investor']);
                    $invUserId = intval($inv['id_users']);

                    // Cari semua outlet di bawah investor ini
                    $outlets = $db->query("SELECT id_outlet, id_users FROM outlet WHERE id_investor = {$invId}");
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

                    $db->query("DELETE FROM investor WHERE id_investor = {$invId}");
                    if ($invUserId > 0) {
                        $db->query("DELETE FROM users WHERE id_users = {$invUserId}");
                    }
                }
            }

            // 2. Hapus akun Master dari tabel users
            $db->query("DELETE FROM users WHERE id_users = {$idUsers} AND role = 'master'");

            $db->commit();
            return ['success' => true, 'message' => "Berhasil menghapus akun Master beserta seluruh data terikatnya"];
        } catch (\Throwable $e) {
            $db->rollback();
            return ['success' => false, 'message' => "Gagal menghapus data Master: " . $e->getMessage()];
        }
    }
    public static function saveKomisi($data, $files) {
        $db = Database::connect();
        $idKomisi = intval($data['id_komisi'] ?? 0);
        $idMaster  = intval($data['id_master'] ?? 0);
        $tanggal   = trim($data['tgl_transfer'] ?? '');
        $nominalStr = preg_replace('/[^0-9]/', '', $data['nominal_transfer_komisi'] ?? '0');
        $nominal   = intval($nominalStr);
        $catatan   = trim($data['catatan'] ?? '');

        if ($idMaster <= 0) return ['success' => false, 'message' => 'Harap pilih Master Owner!'];
        if (empty($catatan)) return ['success' => false, 'message' => 'Harap isi catatan komisi!'];
        if ($nominal <= 0) return ['success' => false, 'message' => 'Nominal komisi harus lebih besar dari Rp 0!'];

        if (empty($tanggal)) {
            $tanggal = date('Y-m-d H:i:s');
        } else {
            $tanggal = date('Y-m-d H:i:s', strtotime($tanggal));
        }

        $buktiPath = null;
        if (isset($files['bukti_pembayaran']) && $files['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $files['bukti_pembayaran']['tmp_name'];
            $fileName    = $files['bukti_pembayaran']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $targetDirAdmin = CRM_ROOT . '/uploads/bukti_komisi/';
                if (!is_dir($targetDirAdmin)) {
                    @mkdir($targetDirAdmin, 0777, true);
                }

                $newFileName = 'bukti_komisi_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
                $destPathAdmin = $targetDirAdmin . $newFileName;

                if (move_uploaded_file($fileTmpPath, $destPathAdmin)) {
                    $buktiPath = 'uploads/bukti_komisi/' . $newFileName;
                }
            }
        }

        $catatanEsc = $db->real_escape_string($catatan);

        if ($idKomisi > 0) {
            // UPDATE
            $updateBukti = "";
            if (!empty($buktiPath)) {
                // Hapus berkas bukti lama dari penyimpanan jika ada berkas baru diunggah
                $resBkt = $db->query("SELECT bukti_pembayaran FROM komisi_master WHERE id_komisi = {$idKomisi} LIMIT 1");
                if ($resBkt && $rowBkt = $resBkt->fetch_assoc()) {
                    $oldFile = trim($rowBkt['bukti_pembayaran'] ?? '');
                    if (!empty($oldFile) && $oldFile !== $buktiPath) {
                        $pathAdmin = CRM_ROOT . '/' . $oldFile;
                        $pathClient = WEB_ROOT . '/' . $oldFile;
                        if (file_exists($pathAdmin)) @unlink($pathAdmin);
                        if (file_exists($pathClient)) @unlink($pathClient);
                    }
                }
                $buktiEsc = $db->real_escape_string($buktiPath);
                $updateBukti = ", bukti_pembayaran = '{$buktiEsc}'";
            }
            $sql = "UPDATE komisi_master 
                    SET id_master = {$idMaster}, 
                        tgl_transfer = '{$tanggal}', 
                        nominal_transfer_komisi = {$nominal}, 
                        catatan = '{$catatanEsc}'
                        {$updateBukti}
                    WHERE id_komisi = {$idKomisi}";
            $db->query($sql);
            return ['success' => true, 'message' => 'Data komisi master berhasil diperbarui.'];
        } else {
            // INSERT
            $buktiVal = !empty($buktiPath) ? "'" . $db->real_escape_string($buktiPath) . "'" : "NULL";
            $sql = "INSERT INTO komisi_master (id_master, tgl_transfer, nominal_transfer_komisi, catatan, bukti_pembayaran) 
                    VALUES ({$idMaster}, '{$tanggal}', {$nominal}, '{$catatanEsc}', {$buktiVal})";
            $db->query($sql);
            return ['success' => true, 'message' => 'Data komisi master berhasil ditambahkan.'];
        }
    }
    public static function deleteKomisi($id_komisi) {
        $db = Database::connect();
        $idKomisi = intval($id_komisi);
        if ($idKomisi <= 0) return ['success' => false, 'message' => 'ID Komisi tidak valid.'];

        $resBkt = $db->query("SELECT bukti_pembayaran FROM komisi_master WHERE id_komisi = {$idKomisi} LIMIT 1");
        if ($resBkt && $rowBkt = $resBkt->fetch_assoc()) {
            $oldFile = trim($rowBkt['bukti_pembayaran'] ?? '');
            if (!empty($oldFile)) {
                $pathAdmin = CRM_ROOT . '/' . $oldFile;
                $pathClient = WEB_ROOT . '/' . $oldFile;
                if (file_exists($pathAdmin)) @unlink($pathAdmin);
                if (file_exists($pathClient)) @unlink($pathClient);
            }
        }

        $db->query("DELETE FROM komisi_master WHERE id_komisi = {$idKomisi}");
        return ['success' => true, 'message' => 'Data komisi master berhasil dihapus.'];
    }

    // ==============================================
    // CLIENT PORTAL (ROLE MASTER) METHODS
    // ==============================================
    public static function getInvestorKabupatenOptions(int $masterId) {
        $db = Database::connect();
        $id = intval($masterId);
        $resKab = $db->query("
            SELECT DISTINCT mw.kabupaten 
            FROM investor i 
            JOIN users u ON u.id_users = i.id_users 
            JOIN master_wilayah mw ON mw.id_wilayah = u.id_wilayah 
            WHERE (i.id_master = {$id} OR i.id_master IS NULL) 
              AND mw.kabupaten IS NOT NULL AND mw.kabupaten != ''
            ORDER BY mw.kabupaten ASC
        ");
        $list = [];
        if ($resKab) {
            while ($kRow = $resKab->fetch_assoc()) {
                $list[] = $kRow['kabupaten'];
            }
        }
        return $list;
    }

    public static function getInvestorJoinYears(int $masterId) {
        $db = Database::connect();
        $id = intval($masterId);
        $resYears = $db->query("
            SELECT DISTINCT YEAR(u.created_at) as y_periode 
            FROM investor i 
            JOIN users u ON u.id_users = i.id_users 
            WHERE (i.id_master = {$id} OR i.id_master IS NULL) 
            ORDER BY y_periode DESC
        ");
        $list = [];
        if ($resYears) {
            while ($yRow = $resYears->fetch_assoc()) {
                if (!empty($yRow['y_periode'])) {
                    $list[] = (int)$yRow['y_periode'];
                }
            }
        }
        if (!in_array((int)date('Y'), $list)) {
            array_unshift($list, (int)date('Y'));
        }
        return $list;
    }

    public static function getInvestorListForMaster(int $masterId) {
        $db = Database::connect();
        $id = intval($masterId);
        $sqlInv = "
            SELECT 
                i.id_investor,
                u.nama_lengkap,
                u.username,
                u.no_hp,
                mw.provinsi,
                mw.kabupaten,
                mw.kecamatan,
                mw.kelurahan,
                u.alamat_lengkap as alamat_investor,
                u.created_at as tanggal_bergabung,
                COUNT(o.id_outlet) as total_outlet,
                SUM(CASE WHEN (o.status = 'active' OR (o.status IN ('pending', 'reject') AND o.tipe_request = 'perpanjangan')) AND (o.tgl_jatuh_tempo IS NULL OR o.tgl_jatuh_tempo >= NOW()) THEN 1 ELSE 0 END) as total_aktif
            FROM investor i
            JOIN users u ON u.id_users = i.id_users
            LEFT JOIN master_wilayah mw ON mw.id_wilayah = u.id_wilayah
            LEFT JOIN outlet o ON o.id_investor = i.id_investor
            WHERE (i.id_master = {$id} OR i.id_master IS NULL)
            GROUP BY i.id_investor
            ORDER BY i.id_investor DESC
        ";

        $resInvestors = $db->query($sqlInv);
        $investorList = [];
        $totalOverallInvestors = 0;
        $totalOverallActiveOutlets = 0;

        if ($resInvestors && $resInvestors->num_rows > 0) {
            while ($row = $resInvestors->fetch_assoc()) {
                $invId = (int)$row['id_investor'];
                $outlets = [];
                $sqlOut = "SELECT o.nama_outlet, mw_out.provinsi, mw_out.kabupaten, mw_out.kecamatan, mw_out.kelurahan, u.alamat_lengkap as alamat_outlet, 
                                  o.tgl_disetujui as tanggal_bergabung 
                           FROM outlet o 
                           JOIN users u ON u.id_users = o.id_users 
                           LEFT JOIN master_wilayah mw_out ON mw_out.id_wilayah = u.id_wilayah
                           WHERE o.id_investor = {$invId} 
                             AND (o.status = 'active' OR (o.status IN ('pending', 'reject') AND o.tipe_request = 'perpanjangan')) 
                             AND (o.tgl_jatuh_tempo IS NULL OR o.tgl_jatuh_tempo >= NOW())
                           ORDER BY o.id_outlet DESC";
                $resOut = $db->query($sqlOut);
                if ($resOut) {
                    while ($out = $resOut->fetch_assoc()) {
                        $outlets[] = $out;
                    }
                }
                $row['outlets_data'] = $outlets;
                $investorList[] = $row;
                $totalOverallInvestors++;
                $totalOverallActiveOutlets += (int)$row['total_aktif'];
            }
        }

        return [
            'investors'                  => $investorList,
            'totalOverallInvestors'      => $totalOverallInvestors,
            'totalOverallActiveOutlets'  => $totalOverallActiveOutlets
        ];
    }

    public static function getKomisiListForMaster(int $masterId) {
        $db = Database::connect();
        $id = intval($masterId);
        $sqlList = "
            SELECT * 
            FROM komisi_master 
            WHERE id_master = {$id}
            ORDER BY tgl_transfer DESC, id_komisi DESC
        ";
        $resKomisi = $db->query($sqlList);
        $komisiList = [];
        $totalOverallKomisi = 0;
        $totalKomisiBulanIni = 0;

        $currentMonth = (int)date('n');
        $currentYear  = (int)date('Y');

        if ($resKomisi && $resKomisi->num_rows > 0) {
            while ($row = $resKomisi->fetch_assoc()) {
                $komisiList[] = $row;
                $nom = (float)($row['nominal_transfer_komisi'] ?? 0);
                $totalOverallKomisi += $nom;
                
                $m = !empty($row['tgl_transfer']) ? (int)date('n', strtotime($row['tgl_transfer'])) : 0;
                $y = !empty($row['tgl_transfer']) ? (int)date('Y', strtotime($row['tgl_transfer'])) : 0;
                if ($m === $currentMonth && $y === $currentYear) {
                    $totalKomisiBulanIni += $nom;
                }
            }
        }

        return [
            'komisiList'          => $komisiList,
            'totalOverallKomisi'  => $totalOverallKomisi,
            'totalKomisiBulanIni' => $totalKomisiBulanIni
        ];
    }

    public static function getAvailableTahunKomisiByMaster(int $masterId) {
        $db = Database::connect();
        $id = intval($masterId);
        $availableYears = [];
        $resYears = $db->query("SELECT DISTINCT YEAR(tgl_transfer) as y_periode FROM komisi_master WHERE id_master = {$id} ORDER BY y_periode DESC");
        if ($resYears) {
            while ($yRow = $resYears->fetch_assoc()) {
                if (!empty($yRow['y_periode'])) {
                    $availableYears[] = (int)$yRow['y_periode'];
                }
            }
        }
        if (!in_array((int)date('Y'), $availableYears)) {
            array_unshift($availableYears, (int)date('Y'));
        }
        return $availableYears;
    }
}

