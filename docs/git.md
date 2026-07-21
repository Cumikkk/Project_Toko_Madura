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

---

## 👥 C. Cara agar Teman Punya Branch Sendiri (Kolaborasi)

Jika teman Anda ingin mulai bekerja di branch-nya sendiri tanpa mengganggu branch `main` atau `Cumikkk`, berikut adalah langkah-langkah yang harus dilakukan oleh **teman Anda**:

### Langkah 1: Kloning Repositori (Jika belum punya kodenya)
Teman Anda harus mengkloning repositori ini ke komputer lokalnya:
```bash
git clone https://github.com/Cumikkk/Project_Toko_Madura.git
cd Project_Toko_Madura
```

### Langkah 2: Buat Branch Baru Khusus untuk Dirinya
Teman Anda membuat branch baru dengan nama bebas (misalnya `branch-andi`):
```bash
git checkout -b branch-andi
```
*(Perintah ini akan membuat branch baru lokal dan langsung berpindah ke branch tersebut).*

### Langkah 3: Lakukan Coding dan Simpan Perubahan
Setelah selesai menulis kode:
```bash
# 1. Tandai file yang berubah
git add .

# 2. Kunci perubahan di lokal
git commit -m "Deskripsi hasil kerja teman"
```

### Langkah 4: Push Pertama Kali ke GitHub
Teman Anda harus mengunggah branch barunya ke GitHub agar Anda bisa melihat kodenya:
```bash
git push -u origin branch-andi
```
*(Setelah push pertama ini sukses, untuk push berikutnya teman Anda cukup mengetik `git push` atau `git push origin branch-andi`).*

---

## 🔄 D. Cara Mengambil Pembaruan dari Branch Teman
Jika Anda ingin melihat atau mengambil kode yang sudah dikerjakan teman Anda di branch-nya (`branch-andi`):

1. **Ambil informasi branch baru dari GitHub**:
   ```bash
   git fetch origin
   ```
2. **Pindah ke branch teman Anda untuk melihat kodenya**:
   ```bash
   git checkout branch-andi
   ```