# Panduan Penggunaan Template Projek Toko Madura

Dokumen ini menjelaskan arsitektur, cara kerja routing, penanganan AJAX, penggunaan database, serta pembuatan Model pada template PHP ini agar Anda dan tim dapat mengembangkannya dengan selaras.

---

## 1. Struktur Folder Utama
*   `admin/` : Portal khusus **Master (System Admin)**.
*   `client/` : Portal khusus **Investor & Outlet** (berbagi satu sistem login).
*   `config/` : Inti aplikasi (Database, Model, Environment, Library, dan Log).
*   `sampah/` : Tempat berkas cadangan fitur trading lama (bisa diabaikan).

---

## 2. Cara Kerja Routing Halaman (Views)
Template ini menggunakan konsep routing dinamis berbasis parameter URL yang diatur oleh berkas `.htaccess` dan dilemparkan ke `home.php` (untuk halaman setelah login) atau `index.php` (untuk halaman login/tamu).

### Halaman Tamu/Login (`index.php`)
*   URL: `http://localhost/.../client/` atau `http://localhost/.../client/signin`
*   Alur: Membaca parameter `a` (default: `signin`), lalu me-require file dari folder `auth/`:
    `client/auth/signin.php`

### Halaman Dashboard / Setelah Login (`home.php`)
*   URL: `http://localhost/.../client/dashboard`
*   Alur: `.htaccess` akan mengalihkan URL ke `home.php?a=dashboard`.
*   Di dalam `home.php`, sistem akan memverifikasi sesi login dan secara otomatis memuat berkas view dari folder `doc/`:
    `client/doc/dashboard/index.php`
*   Jika URL memiliki parameter tambahan seperti `/client/dashboard/detail`, maka sistem akan mencari file di:
    `client/doc/dashboard/detail.php`

---

## 3. Cara Menangani Request AJAX
Semua interaksi form dan muatan data menggunakan request AJAX agar halaman tidak perlu memuat ulang (*seamless*).

### A. Pengiriman Data (POST Form)
Semua form diarahkan ke alamat router AJAX `/ajax/post/`.
*   **Contoh Endpoint:** `/ajax/post/profile/update-password`
*   **File Handler (Target):** 
    *   Di Client: `client/ajax/postdata/profile/update-password.php`
    *   Di Admin: `admin/ajax/postdata/profile/update-password.php`
*   **Format Respon (JSON):**
    ```php
    JsonResponse([
        'code'    => 200,
        'success' => true,
        'message' => 'Password berhasil diubah',
        'alert'   => [
            'title' => 'Sukses!',
            'text'  => 'Password Anda telah diperbarui.',
            'icon'  => 'success'
        ]
    ]);
    ```

### B. Muatan Data Tabel (jQuery Datatable)
Khusus di portal Admin, tabel data menggunakan library Datatable server-side.
*   **Contoh Endpoint:** `/ajax/datatable/user/list`
*   **File Handler (Target):** `admin/ajax/tabledata/user/list.php`
*   **Cara Kerja:** Handler ini berisi query SQL yang divalidasi oleh library `Ozdemir\Datatables` untuk memformat pencarian, pengurutan, dan pagination data secara otomatis.

---

## 4. Cara Membuat & Menggunakan Model
Semua logika database dan pengolahan data harus diletakkan di dalam folder `config/models/`.

### A. Aturan Membuat Model Baru
1. Buat file baru di `config/models/` dengan nama kelas berformat PascalCase (misal: `Outlet.php`).
2. Gunakan namespace `App\Models`.
3. Gunakan koneksi database static menggunakan `Config\Core\Database`.

### B. Contoh Kerangka Model (`config/models/Outlet.php`)
```php
<?php
namespace App\Models;

use Config\Core\Database;
use Exception;

class Outlet {
    
    // Mendapatkan daftar outlet berdasarkan ID Investor
    public static function getByInvestor(int $investorId): array {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("SELECT * FROM outlet WHERE id_investor = ?");
            $stmt->bind_param("i", $investorId);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}
```

---

## 5. Koneksi Database di Berkas PHP
Untuk menjalankan query SQL langsung di file handler AJAX atau View, Anda dapat memanggil variabel global `$db` (yang sudah terinisialisasi otomatis di `setting.php`):

```php
global $db;

$query = $db->query("SELECT * FROM users");
$users = $query->fetch_all(MYSQLI_ASSOC);
```

---

## 6. Pengecekan Sesi & Hak Akses (Authentication)
*   **Di Portal Client (Investor & Outlet):**
    Gunakan `App\Models\User::user()` untuk memeriksa data login pengguna aktif.
    ```php
    use App\Models\User;
    $userActive = User::user(); // Mengembalikan array profil user atau false
    ```
*   **Di Portal Admin (Master):**
    Gunakan `App\Models\Admin::authentication()` untuk memverifikasi Master.
    ```php
    use App\Models\Admin;
    $adminActive = Admin::authentication(); // Mengembalikan array profil admin atau false
    ```
