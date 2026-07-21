# 📋 Panduan Alur Perintah Git

Berikut adalah urutan perintah Git yang rapi dan siap digunakan untuk alur kerja Anda:

---

## 🌿 A. Alur Kerja di Branch `Cumikkk` (Eksperimen/Uji Coba)

Jalankan urutan ini untuk menyimpan pekerjaan Anda ke branch `Cumikkk`:

1. **Cek Posisi Branch** (Pastikan tertulis `* Cumikkk` di terminal)
   ```bash
   git branch
   ```

2. **Tarik Pembaruan Terbaru** (Dari server online)
   ```bash
   git pull
   ```

3. **Tandai Semua Perubahan File**
   ```bash
   git add .
   ```

4. **Kunci Perubahan di Lokal**
   ```bash
   git commit -m "Update Cumikkk."
   ```

5. **Kirim Perubahan ke GitHub**
   ```bash
   git push origin Cumikkk
   ```

---

## 🔄 B. Cara Berpindah dan Menggabungkan ke Branch `main` (Utama)

Jalankan urutan ini jika Anda sudah siap menyatukan kode uji coba dari branch `Cumikkk` ke branch utama `main`:

1. **Pindah ke Branch `main`**
   ```bash
   git checkout main
   ```

2. **Gabungkan Perubahan dari `Cumikkk` ke `main`**
   ```bash
   git merge Cumikkk
   ```

3. **Tarik Pembaruan Online Terbaru**
   ```bash
   git pull
   ```

4. **Kirim Hasil Gabungan ke GitHub**
   ```bash
   git push origin main
   ```