<div class="row">
    <div class="col-12">
        <div class="panel">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-2">FORMULIR PBK. CDDS. 01</div>
                        <div class="col-md-8">
                            <strong>Profile Perusahaan</strong><br>
                            <small><?= App\Models\ProfilePerusahaan::get()['COMPANY_NAME']?> adalah Perusahaan Pialang yang bergerak di bidang perdagangan kontrak derivatif komoditi, Indeks Saham dan Foreign Exchange.</small>
                        </div>
                        <div class="col-md-2 text-center"><a href="/export/profile-perusahaan?acc=<?php echo md5(md5(App\Models\Account::realAccountDetail(App\Models\Helper::form_input($_GET['id'] ?? "-"))['ID_ACC'])) ?>" target="_blank" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i> View</a></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">FORMULIR PBK. CDDS. 02.1</div>
                        <div class="col-md-8">
                            <strong>Pernyataan Telah Melakukan Simulasi perdagangan berjangka komoditi</strong><br>
                            <small>Calon Nasabah diwajibkan untuk memiliki demo account <?= App\Models\ProfilePerusahaan::get()['COMPANY_NAME']?> sebagai sarana untuk melakukan simulasi transaksi di <?= App\Models\ProfilePerusahaan::get()['COMPANY_NAME']?>.</small>
                        </div>
                        <div class="col-md-2 text-center"><a href="/export/pernyataan-simulasi?acc=<?php echo md5(md5(App\Models\Account::realAccountDetail(App\Models\Helper::form_input($_GET['id'] ?? "-"))['ID_ACC'])) ?>" target="_blank" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i> View</a></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">FORMULIR PBK. CDDS. 02.2</div>
                        <div class="col-md-8">
                            <strong>Pernyataan telah berpengalaman melaksanakan transaksi perdagangan berjangka komoditi</strong><br>
                            <small>Dalam hal calon nasabah telah berpengalaman dalam melaksanakan transaksi dalam Perdagangan Berjangka Komoditi, Nasabah memberikan pernyataan dengan Surat Pernyataan Telah Berpengalaman Melaksanakan Transaksi Perdagangan Berjangka Komoditi.</small>
                        </div>
                        <div class="col-md-2 text-center"><a href="/export/pernyataan-pengalaman?acc=<?php echo md5(md5(App\Models\Account::realAccountDetail(App\Models\Helper::form_input($_GET['id'] ?? "-"))['ID_ACC'])) ?>" target="_blank" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i> View</a></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">FORMULIR PBK. CDDS. 03</div>
                        <div class="col-md-8">
                            <strong>Pernyataan Pengungkapan (<i>Disclosure Statement</i>)</strong><br>
                            <small>Menyatakan telah memahami poin-poin krusial dalam Pernyataan Pengungkapan (Disclosure Statement) secara sadar.</small>
                        </div>
                        <div class="col-md-2 text-center"><a href="/export/pernyataan-pengungkapan?acc=<?php echo md5(md5(App\Models\Account::realAccountDetail(App\Models\Helper::form_input($_GET['id'] ?? "-"))['ID_ACC'])) ?>" target="_blank" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i> View</a></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">FORMULIR PBK. CDDS. 04</div>
                        <div class="col-md-8">
                            <strong>Aplikasi Pembukaan Rekening Transaksi secara Elektronik On-line</strong><br>
                            <small>Seluruh data isian dalam Aplikasi Pembukaan Rekening Transaksi Secara Elektronik On-line Dalam Sistem Perdagangan Alternatif wajib di isi sendiri oleh Nasabah, dan Nasabah bertanggung jawab atas kebenaran informasi yang diberikan dalam mengisi dokumen ini..</small>
                        </div>
                        <div class="col-md-2 text-center"><a href="/export/aplikasi-pembukaan-rekening?acc=<?php echo md5(md5(App\Models\Account::realAccountDetail(App\Models\Helper::form_input($_GET['id'] ?? "-"))['ID_ACC'])) ?>" target="_blank" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i> View</a></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">FORMULIR PBK. CDDS. 05.2</div>
                        <div class="col-md-8">
                            <strong>Document pemberitahuan adanya resiko</strong><br>
                            <small>Maksud dokumen ini adalah memberitahukan bahwa kemungkinan kerugian atau keuntungan dalam perdagangan Kontrak Berjangka bisa mencapai jumlah yang sangat besar. Oleh karena itu, Anda harus berhati-hati dalam memutuskan untuk melakukan transaksi, apakah kondisi keuangan Anda mencukupi.</small>
                        </div>
                        <div class="col-md-2 text-center"><a href="/export/pemberitahuan-adanya-risiko?acc=<?php echo md5(md5(App\Models\Account::realAccountDetail(App\Models\Helper::form_input($_GET['id'] ?? "-"))['ID_ACC'])) ?>" target="_blank" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i> View</a></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">FORMULIR PBK. CDDS. 06.2</div>
                        <div class="col-md-8">
                            <strong>Perjanjian pemberian amanat secara elektronik on-line untuk transaksi kontrak berjangka</strong><br>
                            <small>Perjanjian kontrak berjangka dan sepakat untuk mengadakan Perjanjian Pemberian Amanat untuk melakukan transaksi penjualan maupun pembelian Kontrak.</small>
                        </div>
                        <div class="col-md-2 text-center"><a href="/export/perjanjian-pemberian-amanat?acc=<?php echo md5(md5(App\Models\Account::realAccountDetail(App\Models\Helper::form_input($_GET['id'] ?? "-"))['ID_ACC'])) ?>" target="_blank" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i> View</a></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">FORMULIR PBK. CDDS. 07</div>
                        <div class="col-md-8">
                            <strong>Peraturan Perdagangan (Trading Rules)</strong><br>
                            <small>Peraturan Perdagangan (Trading Rules) dalam sistem aplikasi penerimaan nasabah secara elektronik On-Line.</small>
                        </div>
                        <div class="col-md-2 text-center"><a href="<?php echo App\Factory\FileUploadFactory::aws()->awsFile($row['RTYPE_FILE']) ?>" target="_blank" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i> View</a></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">FORMULIR PBK. CDDS. 08</div>
                        <div class="col-md-8">
                            <strong>Pernyataan bertanggung jawab</strong><br>
                            <small>Pernyataan bertanggung jawab atas kode akses transaksi nasabah(Personal Access Password).</small>
                        </div>
                        <div class="col-md-2 text-center"><a href="/export/personal-access-password?acc=<?php echo md5(md5(App\Models\Account::realAccountDetail(App\Models\Helper::form_input($_GET['id'] ?? "-"))['ID_ACC'])) ?>" target="_blank" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i> View</a></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">FORMULIR PBK. CDDS. 09</div>
                        <div class="col-md-8">
                            <strong>Formulir Penyataan Dana Nasabah</strong><br>
                            <small>Proses Penerimaan Nasabah Secara Elektronik Online</small>
                        </div>
                        <div class="col-md-2 text-center"><a href="/export/pernyataan-dana-nasabah?acc=<?php echo md5(md5(App\Models\Account::realAccountDetail(App\Models\Helper::form_input($_GET['id'] ?? "-"))['ID_ACC'])) ?>" target="_blank" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i> View</a></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">FORMULIR PBK. CDDS. 10</div>
                        <div class="col-md-8">
                            <strong>Kelengkapan Formulir</strong><br>
                            <small>Proses Penerimaan Nasabah Secara Elektronik Online.</small>
                        </div>
                        <div class="col-md-2 text-center"><a href="/export/kelengkapan-formulir?acc=<?php echo md5(md5(App\Models\Account::realAccountDetail(App\Models\Helper::form_input($_GET['id'] ?? "-"))['ID_ACC'])) ?>" target="_blank" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i> View</a></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">WP CONFIRM</div>
                        <div class="col-md-8">
                            <strong>Surat Pernyataan</strong><br>
                            <small>Proses Penerimaan Nasabah Secara Elektronik Online.</small>
                        </div>
                        <div class="col-md-2 text-center"><a href="/export/bukti-konfirmasi-penerimaan-nasabah?acc=<?php echo md5(md5(App\Models\Account::realAccountDetail(App\Models\Helper::form_input($_GET['id'] ?? "-"))['ID_ACC'])) ?>" target="_blank" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i> View</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>