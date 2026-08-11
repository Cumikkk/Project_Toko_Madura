<div class="main-content-inner py-3">
    <!-- Header Section -->
    <div class="mb-4">
        <h3 class="fw-bold text-dark mb-1">
            <i class="fa-solid fa-user-gear me-2" style="color: #701416;"></i>Profil Saya
        </h3>
        <p class="text-muted mb-0">Kelola informasi data diri dan kata sandi akun Investor Anda.</p>
    </div>

    <div class="row g-4">
        <!-- Account Info Card -->
        <div class="col-lg-4 col-12">
            <div class="card border-0 shadow-sm rounded-4 text-center p-4">
                <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center text-white mb-3 shadow" style="width: 90px; height: 90px; font-size: 36px; background-color: #701416;">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">H. Achmad Madura</h5>
                <span class="badge bg-danger-subtle text-danger px-3 py-1 rounded-pill fw-semibold mb-3">Investor Toko Madura</span>
                <p class="text-muted small mb-3"><i class="fa-solid fa-location-dot me-1"></i> Bangkalan, Jawa Timur</p>
                <div class="border-top pt-3 text-start small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Outlet:</span>
                        <span class="fw-bold text-dark">5 Outlet</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Bagian Investor:</span>
                        <span class="fw-bold text-success">50.00%</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Status Akun:</span>
                        <span class="badge bg-success-subtle text-success">Aktif</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Edit Profile -->
        <div class="col-lg-8 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h5 class="fw-bold text-dark mb-4 border-bottom pb-2">Informasi Akun Investor</h5>
                <form id="formProfileInvestor">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Nama Lengkap Investor</label>
                            <input type="text" class="form-control" value="H. Achmad Madura" required>
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">No. WhatsApp / Telepon</label>
                            <input type="text" class="form-control" value="0812-3456-7890" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alamat Email</label>
                        <input type="email" class="form-control" value="investor.achmad@email.com" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Alamat Domisili</label>
                        <textarea class="form-control" rows="2">Jl. Raya Kamal No. 88, Kab. Bangkalan, Jawa Timur</textarea>
                    </div>

                    <h5 class="fw-bold text-dark mb-3 border-bottom pb-2 pt-2">Ganti Kata Sandi</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Password Baru</label>
                            <input type="password" class="form-control" placeholder="Kosongkan jika tidak diganti">
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" placeholder="Ulangi password baru">
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-danger px-4 py-2 fw-semibold" style="background-color: #701416; border-color: #701416; border-radius: 8px;">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
