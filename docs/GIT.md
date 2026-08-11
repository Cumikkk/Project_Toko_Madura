# 📋 Panduan Alur Kerja Git (Step-by-Step Terurut)

Panduan ini khusus disusun untuk alur kerja kolaborasi antara **Anda (branch `Cumikkk`)** dan **Tegar (branch `tegar`)**.

---

## 🚀 1. Langkah Awal Pembuatan Branch (Hanya Sekali di Awal)
Sebelum mulai menulis kode baru, buatlah branch khusus masing-masing di komputer lokal agar tidak saling menimpa pekerjaan.

*   **Langkah untuk Anda:**
    ```bash
    git checkout -b Cumikkk
    ```
*   **Langkah untuk Tegar (Teman Anda):**
    ```bash
    git checkout -b tegar
    ```
    *(Tanda `-b` artinya membuat branch baru di lokal dan langsung berpindah ke sana).*

---

## 💻 2. Langkah Menyimpan Hasil Kerja Harian (Commit & Push)
Jalankan langkah-langkah di bawah ini secara berurutan setiap kali Anda selesai menulis kode dan ingin menyimpannya ke GitHub agar aman:

### **A. Urutan Perintah untuk Anda (Branch `Cumikkk`):**
1. Pastikan Anda berada di branch `Cumikkk`:
   ```bash
   git branch
   ```
2. Cek file yang berubah:
   ```bash
   git status
   ```
3. Tandai semua file untuk disimpan:
   ```bash
   git add .
   ```
4. Simpan perubahan secara permanen di komputer lokal Anda:
   ```bash
   git commit -m "Update Cumikkk."
   ```
5. Kirim kode Anda ke GitHub:
   ```bash
   git push origin Cumikkk
   ```

### **B. Urutan Perintah untuk Tegar (Branch `tegar`):**
1. Pastikan Tegar berada di branch `tegar`:
   ```bash
   git branch
   ```
2. Cek file yang berubah:
   ```bash
   git status
   ```
3. Tandai semua file untuk disimpan:
   ```bash
   git add .
   ```
4. Simpan perubahan secara permanen di komputer lokal Tegar:
   ```bash
   git commit -m "Tulis deskripsi update Tegar di sini"
   ```
5. Kirim kode Tegar ke GitHub:
   ```bash
   git push origin tegar
   ```

---

## 🤝 3. Langkah Menyatukan Kode ke Branch Utama (`main`)
Lakukan urutan ini jika fitur yang dikerjakan di branch `Cumikkk` atau `tegar` sudah selesai diuji dan ingin digabungkan secara resmi ke branch utama `main`.

### **Kasus: Menggabungkan kode dari `Cumikkk` ke `main`**
*(Dijalankan oleh Anda)*
1. Pindah ke branch `main`:
   ```bash
   git checkout main
   ```
2. Tarik update terbaru di branch `main` dari GitHub:
   ```bash
   git pull
   ```
3. Gabungkan kode `Cumikkk` ke dalam `main`:
   ```bash
   git merge Cumikkk
   ```
4. Kirim hasil gabungan tersebut ke GitHub:
   ```bash
   git push origin main
   ```

---

## 🔄 4. Langkah Sinkronisasi (Mengambil Kode Baru Teman dari `main`)
Setelah Anda memperbarui branch `main` (Langkah 3), Tegar harus memperbarui branch `tegar` miliknya agar mendapatkan fitur baru dari Anda.

### **Urutan yang dilakukan oleh Tegar di komputernya:**
1. Pindah ke branch `main` lokalnya:
   ```bash
   git checkout main
   ```
2. Tarik update kode terbaru dari GitHub:
   ```bash
   git pull
   ```
3. Kembali ke branch kerjanya:
   ```bash
   git checkout tegar
   ```
4. Gabungkan kode `main` terbaru ke branch kerjanya:
   ```bash
   git merge main
   ```
   *(Sekarang branch `tegar` sudah berisi kode buatan Anda berdua secara lengkap).*

---

## 💡 5. FAQ & Tips Penting (Jika Branch Teman Tidak Muncul)

### ❓ Tegar baru saja push branch `tegar` ke GitHub, kenapa tidak muncul di komputer Anda?
*   **Penyebab**: Komputer Anda belum memperbarui daftar info branch terbaru dari GitHub.
*   **Solusi (Urutan Perintah Anda)**:
    1. Tarik informasi branch terbaru dari GitHub:
       ```bash
       git fetch origin
       ```
    2. Cek semua daftar branch (lokal & online) untuk memastikan branch `tegar` sudah terdeteksi:
       ```bash
       git branch -a
       ```
       *(Branch online/remote biasanya tertulis berwarna merah).*
    3. Pindah/masuk ke branch Tegar jika ingin mengintip atau menyalin kodenya:
       ```bash
       git checkout tegar
       ```

### ❓ Apa beda `git fetch origin` dengan `git pull`?
*   **`git fetch origin` (Hanya Mengintip Info)**: Mengambil info terbaru dari GitHub (seperti munculnya branch baru `tegar`) tanpa mengubah file codingan Anda saat ini di VS Code.
*   **`git pull` (Mengunduh & Menimpa)**: Mengunduh data terbaru dan langsung menggabungkannya ke file lokal Anda agar sama persis dengan GitHub.

---

## ⏪ 6. Langkah Pembatalan Perubahan (Jika Terjadi Kesalahan)
*   Membatalkan editan file yang BELUM di-`git add`:
    ```bash
    git restore nama_file.php
    ```
*   Membatalkan file yang terlanjur di-`git add` (keluar dari antrean):
    ```bash
    git restore --staged nama_file.php
    ```
*   Membatalkan commit terakhir di lokal (File editan tetap aman):
    ```bash
    git reset --soft HEAD~1
    ```