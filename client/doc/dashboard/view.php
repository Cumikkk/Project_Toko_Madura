<div class="main-content-inner">
    <div class="row">
        <div class="col-12">
            <div class="card mt-4 border-0 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #1f1f2e 0%, #111119 100%); color: #fff;">
                <div class="card-body p-5">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="fw-bold mb-2">Selamat Datang di Portal Toko Madura!</h2>
                            <p class="text-white-50 fs-5 mb-4">Masuk sebagai: <strong><?= ucwords($user['role']) ?></strong> (<?= $user['MBR_NAME'] ?>)</p>
                            <span class="badge bg-warning text-dark px-3 py-2 fs-6 fw-semibold" style="border-radius: 30px;">Halaman Dashboard Utama</span>
                        </div>
                        <div class="col-md-4 text-center d-none d-md-block">
                            <i class="fa-light fa-store" style="font-size: 100px; color: #ffc107; opacity: 0.8;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
