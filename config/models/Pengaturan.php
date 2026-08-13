<?php
namespace App\Models;

use Config\Core\Database;
use Exception;

class Pengaturan {
    
    /**
     * Get all settings or filter by specific keys
     * @param array $keys (Optional) List of setting names to fetch
     * @return array Associative array of [nama_pengaturan => nilai]
     */
    public static function getSettings(array $keys = []): array {
        $db = Database::connect();
        $settings = [];
        
        $sql = "SELECT nama_pengaturan, nilai FROM pengaturan_sistem";
        if (!empty($keys)) {
            $escapedKeys = array_map(function($k) use ($db) {
                return "'" . $db->real_escape_string($k) . "'";
            }, $keys);
            $sql .= " WHERE nama_pengaturan IN (" . implode(',', $escapedKeys) . ")";
        }
        
        $res = $db->query($sql);
        if ($res && $res->num_rows > 0) {
            while ($r = $res->fetch_assoc()) {
                $settings[$r['nama_pengaturan']] = $r['nilai'];
            }
        }
        
        return $settings;
    }

    /**
     * Update or insert system settings
     * @param array $updates Associative array of [nama_pengaturan => nilai]
     * @return array [success => bool, message => string]
     */
    public static function updateSettings(array $updates): array {
        if (empty($updates)) {
            return ['success' => false, 'message' => 'Tidak ada data pengaturan yang dikirim'];
        }

        $db = Database::connect();
        $db->begin_transaction();

        try {
            foreach ($updates as $key => $val) {
                $escapedKey = $db->real_escape_string($key);
                $escapedVal = $db->real_escape_string($val);
                
                // Using INSERT ... ON DUPLICATE KEY UPDATE is more efficient and safer for transactions
                $sql = "INSERT INTO pengaturan_sistem (nama_pengaturan, nilai) 
                        VALUES ('{$escapedKey}', '{$escapedVal}') 
                        ON DUPLICATE KEY UPDATE nilai = '{$escapedVal}'";
                
                if (!$db->query($sql)) {
                    throw new Exception("Gagal menyimpan pengaturan {$escapedKey}: " . $db->error);
                }
            }
            
            $db->commit();
            return ['success' => true, 'message' => 'Pengaturan berhasil diperbarui!'];
            
        } catch (Exception $e) {
            $db->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
