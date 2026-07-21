# 📋 Panduan Alur Kerja Git (Step-by-Step Terurut)

Panduan ini disusun secara berurutan mulai dari awal pembuatan branch, memasukkan perintah sehari-hari, hingga cara berkolaborasi dengan tim.

---

## 🚀 1. Langkah Awal Pembuatan Branch (Hanya Sekali di Awal)
Sebelum mulai mengotak-atik kode, buatlah branch khusus agar pekerjaan Anda terpisah dari branch `main` (utama).

*   **Untuk Anda (membuat branch `Cumikkk`):**
    ```bash
    git checkout -b Cumikkk
    ```
*   **Untuk Teman Anda (membuat branch sendiri, misal `branch-andi`):**
    ```bash
    git checkout -b branch-andi
    ```
    *(Tanda `-b` artinya membuat branch baru dan langsung berpindah ke sana).*

---

## 💻 2. Langkah-Langkah Memasukkan Perintah (Alur Kerja Harian)
Jalankan langkah-langkah di bawah ini secara berurutan setiap kali Anda selesai menulis kode dan ingin menyimpannya ke GitHub:

### **Langkah A: Cek Branch Aktif**
Pastikan Anda berada di branch Anda sendiri, bukan di branch `main`:
```bash
git branch
```
*(Yang aktif harus memiliki tanda bintang `*` di sebelah namanya, contoh: `* Cumikkk`).*

### **Langkah B: Cek File yang Berubah**
Ketik ini untuk melihat file apa saja yang baru Anda edit:
```bash
git status
```

### **Langkah C: Tandai File yang Diedit**
Ketik ini untuk menandai semua file siap disimpan:
```bash
git add .
```

### **Langkah D: Simpan di Komputer Lokal**
Kunci perubahan tersebut di lokal komputer Anda dengan pesan deskripsi singkat:
```bash
git commit -m "Tulis pesan mengenai apa yang Anda ubah"
```
*(Contoh: `git commit -m "Update login master"`).*

### **Langkah E: Kirim Perubahan ke GitHub**
*   **Jika Baru Pertama Kali Push Branch Ini:**
    ```bash
    git push -u origin [nama-branch-anda]
    ```
    *(Contoh: `git push -u origin Cumikkk`)*
*   **Untuk Push Rutin Berikutnya:**
    ```bash
    git push origin [nama-branch-anda]
    ```
    *(Contoh: `git push origin Cumikkk`)*

---

## 🤝 3. Langkah Menyatukan Kode ke Branch Utama (`main`)
Lakukan langkah ini jika salah satu fitur di branch uji coba (misal `Cumikkk`) sudah selesai dites dan ingin digabungkan secara resmi ke branch `main`.

1. **Pindah ke branch `main`**:
   ```bash
   git checkout main
   ```
2. **Tarik pembaruan online terbaru di branch `main`**:
   ```bash
   git pull
   ```
3. **Gabungkan (Merge) branch uji coba Anda ke `main`**:
   ```bash
   git merge Cumikkk
   ```
4. **Kirim hasil penggabungan ke GitHub**:
   ```bash
   git push origin main
   ```

---

## 🔄 4. Langkah Sinkronisasi Ulang (Mengambil Kode Baru dari Teman)
Setelah salah satu dari Anda memperbarui branch `main` (Langkah 3), pihak yang lain harus memperbarui branch kerjanya masing-masing agar mendapatkan fitur terbaru tersebut.

**Contoh: Teman Anda ingin mengambil fitur baru buatan Anda dari `main`:**
1. **Teman Anda pindah ke branch `main` lokalnya**:
   ```bash
   git checkout main
   ```
2. **Teman Anda menarik kode baru dari GitHub**:
   ```bash
   git pull
   ```
3. **Teman Anda kembali ke branch kerjanya**:
   ```bash
   git checkout branch-andi
   ```
4. **Teman Anda menggabungkan kode `main` terbaru ke branch-nya**:
   ```bash
   git merge main
   ```
   *(Sekarang kode di branch teman Anda sudah lengkap berisi hasil kerja Anda berdua).*

---

## ⏪ 5. Langkah Pembatalan Perubahan (Jika Terjadi Kesalahan)
*   **Membatalkan editan file yang BELUM di-`git add`:**
    ```bash
    git restore nama_file.php
    ```
*   **Membatalkan file yang terlanjur di-`git add`:**
    ```bash
    git restore --staged nama_file.php
    ```
*   **Membatalkan commit terakhir di lokal (File editan Anda tetap aman):**
    ```bash
    git reset --soft HEAD~1
    ```

---

## 💡 6. Tips Tambahan & FAQ (PENTING)

### ❓ Kenapa saat mengetik `git branch` hanya muncul branch `main`?
*   **Penyebab**: Perintah `git branch` hanya menampilkan branch yang **sudah pernah diaktifkan secara lokal** di komputer Anda. Karena Anda baru melakukan kloning repositori, branch lain (seperti `Cumikkk`) masih tersimpan di server GitHub dan belum diunduh ke komputer Anda.
*   **Solusi**:
    1. Untuk melihat **semua** branch (baik lokal maupun di server GitHub), ketik:
       ```bash
       git branch -a
       ```
       *(Branch online/remote biasanya berwarna merah).*
    2. Untuk mengambil dan berpindah ke branch dari GitHub (misalnya branch `Cumikkk`), jalankan:
       ```bash
       git fetch origin
       git checkout Cumikkk
       ```

### ❓ Apa bedanya `git fetch origin` dengan `git pull`?
*   **`git fetch origin` (Mengintip/Mengambil Info)**:
    *   Hanya mengambil **informasi daftar perubahan terbaru** dari GitHub (seperti daftar branch baru yang dibuat teman Anda, atau adanya commit baru) tanpa merusak atau mengubah kode file yang sedang Anda buka di VS Code.
    *   **Kapan digunakan?** Ketika teman Anda baru saja membuat branch baru di GitHub dan Anda ingin berpindah ke branch tersebut, atau saat Anda ingin mengecek update terbaru tanpa ingin langsung menimpa kode lokal Anda.
*   **`git pull` (Mengunduh & Menggabungkan)**:
    *   Mengunduh data terbaru sekaligus **langsung menggabungkan (merge) ke file kode Anda saat ini** di VS Code.
    *   **Kapan digunakan?** Ketika Anda ingin menyelaraskan isi file codingan Anda dengan versi terbaru di GitHub.