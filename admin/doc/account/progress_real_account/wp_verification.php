<?php
    
    use App\Models\Account;
    use App\Models\Helper;
    use App\Models\FileUpload;
    use App\Models\Regol;
    $data = Helper::getSafeInput($_GET);
    $id_acc = $data["d"] ?? "";

    $COMPANY = App\Models\CompanyProfile::$name;
    $page_title = 'Progress Real Account';
    $web_name_full = $COMPANY;
    $progressAccount = Account::realAccountDetail($id_acc);
    $userBanks = (!empty($progressAccount["MBR_BKJSN"])) ? json_decode($progressAccount["MBR_BKJSN"], true) : [];
    $tanggalLahir = ($progressAccount['ACC_TANGGAL_LAHIR'] ?? '');

    if(!$progressAccount) {
        die("<script>alert('Invalid code'); location.href = '/account/progress_real_account/view'; </script>");
    }

    /** Permission update profile */
    $permisPrflUpdate = $adminPermissionCore->isHavePermission($moduleId, "update.profile");
?>
<div class="page-header">
	<div>
		<h2 class="main-content-title tx-24 mg-b-5"><?php echo $page_title; ?></h2>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
			<li class="breadcrumb-item"><a href="javascript:void(0);">Account</a></li>
			<li class="breadcrumb-item"><a href="<?= pathbreadcrumb(2) ?>/view"><?php echo $page_title; ?></a></li>
			<li class="breadcrumb-item active">WP Verification</li>
		</ol>
	</div>
</div>

<?php if($progressAccount['ACC_CDD'] == Regol::$cddTypeStandard && $progressAccount['ACC_NEEDS_UPGRADE']) : ?>
    <div class="alert alert-info">
        <div class="d-flex align-items-center gap-2">
            <i class="fas fa-info-circle"></i>
            <span>Akun ini merupakan akun <strong>CDDS</strong> yang saat ini sedang dalam proses upgrade ke <strong>CDD Standar</strong>.</span>
        </div>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-12 mb-3">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between flex-wrap align-items-center mb-3">
                    <h5 class="card-title">Agreement</h5>
                    <div class="d-flex flex-start flex-wrap gap-2">
                        <a target="_blank" href="/export/pernyataan-pengungkapan?acc=<?php echo $id_acc; ?>" class="btn btn-primary d-flex align-items-center"><i class="fa fa-eye"></i>&nbsp;Disclosure Statement</a> &nbsp;
                        <a href="/export/all?acc=<?php echo $id_acc; ?>" class="btn btn-primary"><i class="fa fa-eye"></i> All Documents</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <tbody>
                            <tr>
                                <td>1.</td>
                                <td>Formulir Nomor : 107.PBK.01 </td>
                                <td>
                                    Profile Perusahaan <br>
                                    <small><?php echo $web_name_full ?> adalah Perusahaan Pialang yang bergerak di bidang perdagangan kontrak derivatif komoditi, Indeks Saham dan Foreign Exchange.</small>
                                </td>
                                <td><a target="_blank" href="/export/profile-perusahaan?acc=<?php echo $id_acc; ?>" class="btn btn-primary d-flex align-items-center"><i class="fa fa-eye"></i>&nbsp;View</a></td>
                            </tr>
                            <tr>
                                <td>2.</td>
                                <td>Formulir Nomor : 107.PBK.02.1</td>
                                <td>
                                    Pernyataan Telah Melakukan Simulasi perdagangan berjangka komoditi<br>
                                    <small>Calon Nasabah diwajibkan untuk memiliki demo account <?php echo $web_name_full ?> sebagai sarana untuk melakukan simulasi transaksi di <?php echo $web_name_full ?>.</small>
                                </td>
                                <td><a target="_blank" href="/export/pernyataan-simulasi?acc=<?php echo $id_acc; ?>" class="btn btn-primary d-flex align-items-center"><i class="fa fa-eye"></i>&nbsp;View</a></td>
                            </tr>
                            <tr>
                                <td>3.</td>
                                <td>Formulir Nomor : 107.PBK.02.2</td>
                                <td>
                                    Pernyataan telah berpengalaman melaksanakan transaksi perdagangan berjangka komoditi<br>
                                    <small>Dalam hal calon nasabah telah berpengalaman dalam melaksanakan transaksi dalam Perdagangan Berjangka Komoditi, Nasabah memberikan pernyataan dengan Surat Pernyataan Telah Berpengalaman Melaksanakan Transaksi Perdagangan Berjangka Komoditi.</small>
                                </td>
                                <td><a target="_blank" href="/export/pernyataan-pengalaman?acc=<?php echo $id_acc; ?>" class="btn btn-primary d-flex align-items-center"><i class="fa fa-eye"></i>&nbsp;View</a></td>
                            </tr>
                            <tr>
                                <td>4.</td>
                                <td>Formulir Nomor : 107.PBK.03</td>
                                <td>
                                    Aplikasi Pembukaan Rekening Transaksi secara Elektronik On-line<br>
                                    <small>Seluruh data isian dalam Aplikasi Pembukaan Rekening Transaksi Secara Elektronik On-line Dalam Sistem Perdagangan Alternatif wajib di isi sendiri oleh Nasabah, dan Nasabah bertanggung jawab atas kebenaran informasi yang diberikan dalam mengisi dokumen ini.</small>
                                </td>
                                <td><a target="_blank" href="/export/aplikasi-pembukaan-rekening?acc=<?php echo $id_acc; ?>" class="btn btn-primary d-flex align-items-center"><i class="fa fa-eye"></i>&nbsp;View</a></td>
                            </tr>
                            <tr>
                                <td>5.</td>
                                <td>Formulir Nomor : 107.PBK.04.2</td>
                                <td>
                                    Document pemberitahuan adanya resiko<br>
                                    <small>Maksud dokumen ini adalah memberitahukan bahwa kemungkinan kerugian atau keuntungan dalam perdagangan Kontrak derifatif bisa mencapai jumlah yang sangat besar. Oleh karena itu, Anda harus berhati-hati dalam memutuskan untuk melakukan transaksi, apakah kondisi keuangan Anda mencukupi.</small>
                                </td>
                                <td><a target="_blank" href="/export/pemberitahuan-adanya-risiko?acc=<?php echo $id_acc; ?>" class="btn btn-primary d-flex align-items-center"><i class="fa fa-eye"></i>&nbsp;View</a></td>
                            </tr>
                            <tr>
                                <td>6.</td>
                                <td>Formulir Nomor : 107.PBK.05.2</td>
                                <td>
                                    Perjanjian pemberian amanat secara elektronik on-line untuk transaksi kontrak derifatif
                                    <small>Perjanjian kontrak berjangka dan sepakat untuk mengadakan Perjanjian Pemberian Amanat untuk melakukan transaksi penjualan maupun pembelian Kontrak</small>
                                </td>
                                <td><a target="_blank" href="/export/perjanjian-pemberian-amanat?acc=<?php echo $id_acc; ?>" class="btn btn-primary d-flex align-items-center"><i class="fa fa-eye"></i>&nbsp;View</a></td>
                            </tr>
                            <tr>
                                <td>7.</td>
                                <td>Formulir Nomor : 107.PBK.06</td>
                                <td>
                                    Peraturan Perdagangan (Trading Rules)<br>
                                    <small>Peraturan Perdagangan (Trading Rules) dalam siste, aplikasi penerimaan nasabah secara elektronik On-Line</small>
                                </td>
                                <td><a target="_blank" href="<?php echo App\Models\Regol::urlTradingRule($progressAccount['RTYPE_FILE']); ?>" class="btn btn-primary d-flex align-items-center"><i class="fa fa-eye"></i>&nbsp;View</a></td>
                            </tr>
                            <tr>
                                <td>8.</td>
                                <td>Formulir Nomor : 107.PBK.07</td>
                                <td>
                                    Pernyataan bertanggung jawab<br>
                                    <small>Pernyataan bertanggung jawab atas kode akses transaksi nasabah(Personal Access Password)</small>
                                </td>
                                <td><a target="_blank" href="/export/personal-access-password?acc=<?php echo $id_acc; ?>" class="btn btn-primary d-flex align-items-center"><i class="fa fa-eye"></i>&nbsp;View</a></td>
                            </tr>
                            <tr>
                                <td>9.</td>
                                <td>-</td>
                                <td>
                                    Formulir Penyataan Dana Nasabah<br>
                                    <small>Pernyataan Bahwa Dana Yang Di Gunakan Sebagai Margin Merupakan Dana Milik Nasabah Sendiri</small>
                                </td>
                                <td><a target="_blank" href="/export/pernyataan-dana-nasabah?acc=<?php echo $id_acc; ?>" class="btn btn-primary d-flex align-items-center"><i class="fa fa-eye"></i>&nbsp;View</a></td>
                            </tr>
                            <tr>
                                <td>10.</td>
                                <td>-</td>
                                <td>
                                    Surat Pernyataan<br>
                                    <small>Surat pernyataan nasabah</small>
                                </td>
                                <td><a target="_blank" href="/export/surat-pernyataan?acc=<?php echo $id_acc; ?>" class="btn btn-primary d-flex align-items-center"><i class="fa fa-eye"></i>&nbsp;View</a></td>
                            </tr>
                            <tr>
                                <td>11.</td>
                                <td>-</td>
                                <td>
                                    Kelengkapan Formulir<br>
                                    <small>Proses Penerimaan Nasabah Secara Elektronik Online</small>
                                </td>
                                <td><a target="_blank" href="/export/kelengkapan-formulir?acc=<?php echo $id_acc; ?>" class="btn btn-primary d-flex align-items-center"><i class="fa fa-eye"></i>&nbsp;View</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12 mb-3">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between flex-wrap align-items-center mb-3">
                    <h5 class="card-title">Summary</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <?php require_once __DIR__ . "/summary.php" ?>
                    </div>
                    <div class="col-md-4">
                        <form action="" method="post" enctype="multipart/form-data" id="form-document">
                            <input type="hidden" name="account" value="<?= $id_acc ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <input type="file" class="dropify" name="app_image_1" id="app_image_1" data-max-file-size="2M" data-show-remove="false" data-allowed-file-extensions="png jpg jpeg" data-default-file="<?php echo App\Factory\FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_IMG']); ?>">
                                    <label for="app_image_1" class="form-control-label">
                                        <a target="_blank" href="<?php echo App\Factory\FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_IMG']); ?>">
                                            Rekening Koran Bank / Tagihan Kartu Kredit / Rekening Listrik, Telepon / NPWP
                                        </a>
                                    </label>
                                </div>
    
                                <div class="col-md-6 mb-3">
                                    <input type="file" class="dropify" name="app_image_selfie" id="app_image_selfie" data-max-file-size="4M" data-min-width="480" data-min-height="640" data-show-remove="false" data-allowed-file-extensions="png jpg jpeg" data-default-file="<?php echo App\Factory\FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_FOTO']); ?>">
                                    <label for="app_image_selfie" class="form-control-label">
                                        <a target="_blank" href="<?php echo App\Factory\FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_FOTO']); ?>">
                                           Foto Terbaru (Selfie)
                                        </a>
                                    </label>
                                </div>
    
                                <div class="col-md-6 mb-3">
                                    <input type="file" class="dropify" name="app_image_identitas" id="app_image_identitas" data-max-file-size="2M"  data-min-width="480" data-min-height="320" data-show-remove="false" data-allowed-file-extensions="png jpg jpeg" data-default-file="<?php echo App\Factory\FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_ID']); ?>">
                                    <label for="app_image_identitas" class="form-control-label">
                                        <a target="_blank" href="<?php echo App\Factory\FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_ID']); ?>">
                                           Foto Identitas
                                        </a>
                                    </label>
                                </div>
                             
                                <div class="col-md-6 mb-3">
                                    <input type="file" class="dropify" name="app_image_3" id="app_image_3" data-max-file-size="2M" data-show-remove="false" data-allowed-file-extensions="png jpg jpeg" data-default-file="<?php echo App\Factory\FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_IMG3']); ?>">
                                    <label for="app_image_3" class="form-control-label">
                                        <a target="_blank" href="<?php echo App\Factory\FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_IMG3']); ?>">
                                            Dokumen Lainnya
                                        </a>
                                    </label>
                                </div>
    
                                <div class="col-md-6 mb-3">
                                    <input type="file" class="dropify" name="app_image_4" id="app_image_4" data-max-file-size="2M" data-show-remove="false" data-allowed-file-extensions="png jpg jpeg" data-default-file="<?php echo App\Factory\FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_IMG4']); ?>">
                                    <label for="app_image_4" class="form-control-label">
                                        <a target="_blank" href="<?php echo App\Factory\FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_IMG4']); ?>">
                                            Dokumen Lainnya
                                        </a>
                                    </label>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-center align-items-center gap-3">
                    <?php if($progressAccount['ACC_STS'] == 1 && $progressAccount['ACC_WPCHECK'] == Regol::$statusWPCheckRegister) : ?>
                        <?php if($progressAccount['ACC_TYPE_IDT'] == "KTP") : ?>
                            <button type="button" id="verif_verihub" class="btn btn-primary">Verifikasi Verihub</button>
                        <?php endif; ?>
                        <?php if($permisUpdate = $adminPermissionCore->isHavePermission($moduleId, "update.document")) : ?>
                            <a href="javascript:void(0)" id="update-document" data-url="<?= $permisUpdate['link'] ?>" class="btn btn-primary">Update Document</a>
                        <?php endif; ?>
                        <?php if($permisPrflUpdate) : ?>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModalCenter" type="button">Edit Data Pribadi</button>
                        <?php endif; ?>
                        <button type="button" data-act="reject" class="btnAct btn btn-danger px-5">Reject</button>
                        <button type="button" data-act="accept" class="btnAct btn btn-success px-5">Accept</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if($permisPrflUpdate){ ?>
    <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="<?= $permisPrflUpdate["link"] ?>" method="post" id="editPrfl">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Edit Data Pribadi</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="namleng">Nama Lengkap</label>
                                    <input type="text" name="naleng" id="namleng" value="<?= $progressAccount['ACC_FULLNAME'] ?>" class="form-control">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="smls_tipeidt">Tipe Identitas</label>
                                    <select name="smls_tipeidt" id="smls_tipeidt" class="form-control form-control-sm" required>
                                        <option disabled selected value>Pilih Jenis Identitas</option>
                                        <?php foreach(App\Models\Regol::$typeIdentitas as $type) : ?>
                                            <option value="<?= $type ?>" <?= ($progressAccount['ACC_TYPE_IDT'] == $type ) ? 'selected' : NULL ; ?> ><?= $type ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="smls_nomidt">Nomer Identitas</label>
                                    <input type="text" autocomplete="off" data-kind="<?= strtolower($progressAccount['ACC_TYPE_IDT']) ?>" inputmode="numeric" placeholder="No. Identitas" name="smls_nomidt" id="smls_nomidt" value="<?php echo $progressAccount['ACC_NO_IDT'] ?>" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="smls_tgllhr">Tanggal Lahir</label>
                                    <input type="text" name="smls_tgllhr" id="smls_tgllhr" value="<?php echo (!empty($tanggalLahir)) ? date("Y-m-d", strtotime($tanggalLahir)) : NULL ?>" class="form-control datepicker" required data-max="<?= date("Y-m-d", strtotime("-18 years")) ?>">
                                    <span class="text-primary">*Format (yyyy-mm-dd)</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="smls_tmptlhr">Tempat Lahir</label>
                                    <input type="text" autocomplete="off" placeholder="Tempat Lahir" name="smls_tmptlhr" id="smls_tmptlhr" value="<?php echo html_entity_decode(htmlspecialchars_decode($progressAccount['ACC_TEMPAT_LAHIR'])) ?? '' ?>" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="app_no_handphone">Nomer Telephone</label>
                                    <input type="text" data-kind="phone" inputmode="tel" autocomplete="off" placeholder="62xxxxxxxxx" id="app_no_handphone" name="app_no_handphone" value="<?= ($progressAccount['ACC_F_APP_PRIBADI_HP'] == 0) ? 0 : ($progressAccount['ACC_F_APP_PRIBADI_HP'] ?? 0); ?>" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="app_no_handphone">Nomer NPWP</label>
                                    <input type="text" data-kind="npwp" inputmode="numeric" autocomplete="off" placeholder="No. Npwp" name="app_npwp" value="<?= $progressAccount['ACC_F_APP_PRIBADI_NPWP'] ?>" class="form-control">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="app_no_handphone">Nama Ibukandung</label>
                                    <input type="text" data-kind="nama" inputmode="text" autocomplete="off" placeholder="Nama Ibu Kandung" name="app_nama_ibu" value="<?= $progressAccount['ACC_F_APP_PRIBADI_IBU'] ?>" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="sid_nmbr">Single Investor Identification (SID)</label>
                                    <input type="text" class="form-control" name="sid_nmbr" id="sid_nmbr" value="<?php echo $progressAccount['ACC_F_SID_NOMER'] ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="x" value="<?= $id_acc ?>">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="Submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php } ?>
<script>
    (() => {
        // Konfigurasi ringkas: title, pattern, minimal & maksimal KARAKTER (bukan hanya digit)
        const CONFIG = {
            "kodepos": { 
                title: "Kode pos harus 5 digit angka", 
                pattern: "^\\d{5}$", 
                min: 5,  
                max: 5 
            },
            "npwp":    { 
                title: "NPWP harus 16 digit angka (tanpa titik/strip)", 
                pattern: "^\\d{16}$", 
                min: 16, 
                max: 16 
            },
            "NIK":    { 
                title: "NIK harus 16 digit angka (tanpa titik/strip)", 
                pattern: "^\\d{16}$", 
                min: 16, 
                max: 16 
            },
            "phone": { 
                title: "Nomor telepon tidak valid",
                pattern: "^\\d{8,15}$",
                min: 9, 
                max: 15 
            },
            "kitas": { 
                title: "Nomor KITAS (6-20) digit",
                pattern: "/^[A-Z0-9\-\/]{6,20}$/i",
                min: 6, 
                max: 20 
            },
            "passport": {
                title: "Passport minimal 9 angka atau huruf, dan maksimal 9 angka atau huruf.",
                pattern: "^[0-9A-Za-zÀ-ÖØ-öø-ÿ .,'’\\-]+$",
                min: 7,  
                max: 9
            },
        };


        // --- Filter nilai sesuai tipe ---
        function sanitizeByKind(val, kind) {
            if (kind === "nama") {
                // huruf latin + spasi . ' ’ -
                return (val || "").replace(/[^A-Za-zÀ-ÖØ-öø-ÿ .,'’\-]/g, "");
            }
            if (kind === "passport") {
                return (val || "").replace(/[^0-9A-Za-zÀ-ÖØ-öø-ÿ .,'’\-]/g, "");
            }
            if (kind === "phone") {
                // angka + opsional satu '+' di depan
                const hasPlusFirst = (val || "").startsWith("+");
                const digitsOnly = (val || "").replace(/\D/g, "");
                return hasPlusFirst ? ("+" + digitsOnly) : digitsOnly;
            }
            // kodepos & npwp: angka saja
            return (val || "").replace(/\D/g, "");
        }

        // Blokir karakter tidak valid saat KETIK (paste dibersihkan di 'input')
        document.addEventListener("beforeinput", (e) => {
            const el = e.target;
            if (!el.matches('input[data-kind]')) return;

            const kind = el.dataset.kind;
            const t = e.inputType;
            const ch = e.data ?? "";

            if (t === "insertText") {
                if (kind === "nama") {
                    // izinkan huruf latin + spasi . ' ’ -
                    if (!/^[A-Za-zÀ-ÖØ-öø-ÿ .,'’\-]$/.test(ch)) e.preventDefault();
                } else if (kind === "passport"){
                    if (!/^[0-9A-Za-zÀ-ÖØ-öø-ÿ .,'’\-]$/.test(ch)) e.preventDefault();
                } else if (kind === "phone") {
                    const selStart = el.selectionStart ?? 0;
                    const insertingPlus = ch === "+";
                    const alreadyPlus = el.value.includes("+");
                    const isDigit = /\d/.test(ch);
                    if (insertingPlus) {
                        if (selStart !== 0 || alreadyPlus) e.preventDefault();
                    } else if (!isDigit) {
                        e.preventDefault();
                    }
                } else {
                    // kodepos/npwp -> hanya digit
                    if (!/\d/.test(ch)) e.preventDefault();
                }
            }
        });

        // Terapkan aturan + balon error bawaan browser
        function applyRules(el, { showNow = false } = {}) {
            const kind = el.dataset.kind;
            const cfg = CONFIG[kind];
            if (!cfg) return;

            // sanitize
            const cleaned = sanitizeByKind(el.value, kind);
            if (cleaned !== el.value) el.value = cleaned;

            // atribut validasi
            el.setAttribute("title", cfg.title);
            el.setAttribute("pattern", cfg.pattern);
            el.setAttribute("minlength", String(cfg.min));
            el.setAttribute("maxlength", String(cfg.max));

            // cek validitas ringan untuk pesan cepat
            const val = el.value;
            let msg = "";
            if (val.length === 0) {
                msg = ""; // biarkan required
            } else if (val.length < cfg.min) {
                msg = `Minimal ${cfg.min} karakter.`;
            } else if (val.length > cfg.max) {
                msg = `Maksimal ${cfg.max} karakter.`;
            } else if (!(new RegExp(cfg.pattern).test(val))) {
                msg = cfg.title;
            }
            el.setCustomValidity(msg);

            if (showNow) el.reportValidity();
        }

        // keyup -> tampilkan balon sekarang
        document.addEventListener("keyup", (e) => {
            if (e.target.matches('input[data-kind]')) applyRules(e.target, { showNow: true });
        });

        // input -> handle paste/autofill
        document.addEventListener("input", (e) => {
            if (e.target.matches('input[data-kind]')) applyRules(e.target);
        });

        // init
        window.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll('input[data-kind]').forEach(el => applyRules(el));
        });

        document.querySelector(`select[name="smls_tipeidt"]`).addEventListener('change', function(e){
            var iid = document.querySelector(`input[name="smls_nomidt"]`);
            switch (e.target.value.toLowerCase()) {
                case 'passport':
                    iid.setAttribute('inputmode', 'text');
                    iid.setAttribute('data-kind', 'passport');
                    break;

                case 'ktp':
                    iid.setAttribute('inputmode', 'numeric');
                    iid.setAttribute('data-kind', 'NIK');
                    break;

                case 'kitas':
                    iid.setAttribute('inputmode', 'numeric');
                    iid.setAttribute('data-kind', 'kitas');
                    break;
            
                default: false; break;
            }
            iid.value = null;
            applyRules(iid);
            console.log(iid);
        });
    })();
</script>
<script>
    $(document).ready(function() {
        $('#verif_verihub').on('click', function() {
            Swal.fire({
                title: "Verifikasi Verihub",
                text: "Mohon konfirmasi untuk melanjutkan",
                icon: "info",
                showCancelButton: true,
                reverseButtons: true
            }).then((result) => {
                if(result.isConfirmed) {
                    Swal.fire({
                        text: "Please wait...",
                        allowOutsideClick: false,
                        didOpen: function() {
                            Swal.showLoading();
                        }
                    })

                    $.post("/ajax/post/account/verifikasi_verihub", {account: '<?= $id_acc; ?>'}, (resp) => {
                        Swal.fire(resp.alert).then(() => location.reload())
                    }, 'json')
                }
            })
        })

        $('.btnAct').on('click', function() {
            let ARCBTN = {
                title: `${$(this).data('act').toUpperCase()} DATA`,
                text: `Berikan catatan sebelum ${$(this).data('act').toUpperCase()}`,
                icon: 'warning',
                showCancelButton: true,
                reverseButtons: true,
                input: "text",
                inputLabel: `Masukan catatan`,
                inputAttributes: {
                    required: true,
                }
            };
            Swal.fire(ARCBTN).then((result) => {
                if(result.isConfirmed) {
                    Swal.fire({
                        text: "Please wait...",
                        allowOutsideClick: false,
                        didOpen: function() {
                            Swal.showLoading();
                        }
                    });

                    let id  = '<?= $id_acc ?>';
                    let act = $(this).data('act');
                    $.post("/ajax/post/account/wp_verification_acion", {sbmt_id: id, sbmt_act: act, sbmt_note: result.value}, function(resp) {
                        Swal.fire(resp.alert).then(() => {
                            if(resp.success) {
                                if(resp?.data?.reloc?.length){
                                    location.href = resp?.data?.reloc;
                                }else{ location.reload(); }
                            }
                        })
                    }, 'json');
                }
            });
        });

        $('#update-document').on('click', function(event) {
            let url = $(this).data('url')
            Swal.fire({
                title: "Update User Document",
                text: "Konfirmasi untuk melanjutkan",
                icon: "question",
                showCancelButton: true,
                reverseButtons: true
            }).then((result) => {
                if(result.isConfirmed) {
                    Swal.fire({
                        text: "Loading...",
                        allowOutsideClick: false,
                        didOpen: function() {
                            Swal.showLoading();
                        } 
                    })

                    $.ajax({
                        url: `/ajax/post${url}`,
                        type: "post",
                        dataType: "json",
                        data: new FormData($('#form-document')[0]),
                        contentType: false,
                        processData: false,
                        cache: false
                    }).done((resp) => {
                        Swal.fire(resp.alert).then(() => {
                            if(resp.success) {
                                location.reload();
                            }
                        })
                    })
                }
            })
        });

        
        $('#editPrfl').on('submit', function(event) {
            event.preventDefault();
            $('#exampleModalCenter').modal('hide');
            let data = $(this).serialize(),
                button = $(this).find('button[type="submit"]'),
                url = "/ajax/post".concat($(this).attr('action'));

            Swal.fire({
                title: 'Loading',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                    $.post(url, data, (resp) => {
                        Swal.fire(resp.alert).then(() => {
                            if(resp.success) {
                                location.reload();
                            }
                        })
                    }, 'json');
                }
            });
        })

        $('#sid_nmbr')
        .mask('** - * - **** - ****** - **')
        .on('input', function() {
            this.value = this.value.toUpperCase();
        })
    })
</script>