<?php
namespace App\Models;

use Config\Core\Database;
use Exception;

class Dashboard {
    public static function getRoleCount(string $role): int {
        try {
            $db = Database::connect();
            $query = $db->query("SELECT COUNT(*) as total FROM users WHERE role = '{$role}'");
            return (int)($query->fetch_assoc()['total'] ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }
    public static function getOutletCount(): int {
        try {
            $db = Database::connect();
            $query = $db->query("SELECT COUNT(*) as total FROM outlet WHERE status = 'active' AND (tgl_jatuh_tempo IS NULL OR DATE(tgl_jatuh_tempo) >= CURRENT_DATE())");
            return (int)($query->fetch_assoc()['total'] ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }
    public static function getTopByOmzet() {
        $db = Database::connect();
        $sql = "
            SELECT o.id_outlet, o.nama_outlet, u_out.kecamatan, u_out.alamat_lengkap as alamat_outlet, SUM(l.nominal_omzet) as total_omzet,
                   u_inv.nama_lengkap as nama_investor, u_inv.kecamatan as kecamatan_investor, u_inv.alamat_lengkap as alamat_investor
            FROM laporan_omzet l
            JOIN outlet o ON l.id_outlet = o.id_outlet
            LEFT JOIN users u_out ON (u_out.id_users = o.id_users)
            LEFT JOIN investor inv ON (inv.id_investor = o.id_investor)
            LEFT JOIN users u_inv ON (u_inv.id_users = inv.id_users)
            GROUP BY l.id_outlet
            ORDER BY total_omzet DESC
        ";
        return $db->query($sql);
    }
    public static function getRecentRequests() {
        $db = Database::connect();
        $sql = "
            SELECT o.*, u_inv.nama_lengkap as nama_investor, u_inv.kecamatan as kecamatan_investor, u_inv.alamat_lengkap as alamat_investor
            FROM outlet o
            LEFT JOIN investor inv ON (inv.id_investor = o.id_investor)
            LEFT JOIN users u_inv ON (u_inv.id_users = inv.id_users)
            WHERE o.status = 'pending'
            ORDER BY o.id_outlet DESC
        ";
        return $db->query($sql);
    }
}
