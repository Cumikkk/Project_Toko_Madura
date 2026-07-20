<?php

use Config\Core\EmailSender;

 $profile = App\Models\ProfilePerusahaan::get(); ?>
<a href="/ticket" class="btn btn-primary"><i class="fas fa-plus"></i> Buka Tiket</a>
<div class="row mt-3">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="profile-edit-tab-title mb-3">
                    <h6>FAQ</h6>
                </div>

                <div class="row mb-4">
                    <div classweb="col-md-12">
                        <a href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#collapse-memulai-trading" class="w-100 text-dark">
                            <div class="card bg-primary">
                                <div class="card-header">
                                    <h6 class="card-title text-white">Memulai Trading?</h6>
                                </div>
                            </div>
                        </a>
                        <div class="collapse" id="collapse-memulai-trading">
                            <div class="panel">
                                <div class="panel-body">
                                    <p class="small">Anda bebas memilih jenis akun yang sesuai dengan keinginan Anda.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <a href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#collapse-deposit-dan-withdrawal" class="w-100 text-dark">
                            <div class="card bg-primary">
                                <div class="card-header">
                                    <h6 class="card-title text-white">Deposit & Penarikan Dana?</h6>
                                </div>
                            </div>
                        </a>
                        <div class="collapse" id="collapse-deposit-dan-withdrawal">
                            <div class="panel">
                                <div class="panel-body">
                                    <p class="small">Deposit bank lokal aman, cepat, dan mudah. Atau pilih dari beragam metode pembayaran dari kartu bank dan transfer hingga sistem pembayaran elektronik.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <a href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#collapse-tips-berinvestasi" class="w-100 text-dark">
                            <div class="card bg-primary">
                                <div class="card-header">
                                    <h6 class="card-title text-white">Tips Berinvestasi?</h6>
                                </div>
                            </div>
                        </a>
                        <div class="collapse" id="collapse-tips-berinvestasi">
                            <div class="panel">
                                <div class="panel-body">
                                    <ol>
                                        <li class="small">Gunakan idle money atau dana menganggur yang belum akan digunakan</li>
                                        <li class="small">Lakukan analisis pada produk sebelum mulai berinvestasi</li>
                                        <li class="small">Lakukan diferensiasi produk untuk meminimalisasi potensi risiko</li>
                                        <li class="small">Disiplin dan konsisten dalam berinvestasi</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="profile-edit-tab-title mb-3">
                    <h6>Keluhan / Pengaduan</h6>
                </div>
                <div class="row mb-4">
                    <div class="col-md-12">
                        <a href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#collapse-pengaduan-nasabah" class="w-100">
                            <div class="card bg-primary">
                                <div class="card-header">
                                    <h6 class="card-title text-white">Pengaduan nasabah melalui?</h6>
                                </div>
                            </div>
                        </a>
                        <div class="collapse" id="collapse-pengaduan-nasabah">
                            <div class="panel">
                                <div class="panel-body">
                                    <ol>
                                        <li class="small">Menu tiket di aplikasi <?= $profile['COMPANY_NAME'] ?></li>
                                        <li class="small">Nasabah bisa datang langsung ke kantor <?= $profile['COMPANY_NAME'] ?></li>
                                        <li class="small">Melalui surat tercatat</li>
                                        <li class="small">Surat elektronik <?= $profile['OFFICE']['OFC_EMAIL'] ?></li>
                                        <li class="small">Nomor Telepon <?= $profile['OFFICE']['OFC_PHONE'] ?></li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <a href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#collapse-pengaduan-bappebti" class="w-100 text-dark">
                            <div class="card bg-primary">
                                <div class="card-header">
                                    <h6 class="card-title text-white">Pengaduan Bappebti</h6>
                                </div>
                            </div>
                        </a>
                        <div class="collapse" id="collapse-pengaduan-bappebti">
                            <div class="panel">
                                <div class="panel-body">
                                    <p class="small">Peraturan Kepala Badan Pengawas Perdagangan Berjangka Komoditi Republik Indonesia Nomor 4 Tahun 2020.</p>

                                    <a href="https://pengaduan.bappebti.go.id" target="_blank">(https://pengaduan.bappebti.go.id/)</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>