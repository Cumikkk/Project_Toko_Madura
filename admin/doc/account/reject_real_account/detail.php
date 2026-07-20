<?php
    
    use App\Factory\FileUploadFactory;
    use App\Models\Account;
    use App\Models\Helper;
    use App\Models\FileUpload;
    $data = Helper::getSafeInput($_GET);

    $COMPANY         = App\Models\CompanyProfile::$name;
    $page_title      = 'Rejected Real Account';
    $web_name_full   = $COMPANY;
    $progressAccount = Account::realAccountDetail($data["d"]);
    $id_acc          = $data["d"];
    $userBanks       = (!empty($progressAccount["MBR_BKJSN"])) ? json_decode($progressAccount["MBR_BKJSN"], true) : [];
    $MULTIPN         = ["multi", "multilateral"];
    $SPANAME         = ["spa"];
?>
<div class="page-header">
	<div>
		<h2 class="main-content-title tx-24 mg-b-5">Rejected Account Detail</h2>
		<ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
			<li class="breadcrumb-item">Account</li>
			<li class="breadcrumb-item"><a href="<?= pathbreadcrumb(2) ?>/view"><?php echo $page_title; ?></a></li>
			<li class="breadcrumb-item active">Rejected Account Detail</li>
		</ol>
	</div>
</div>
<div class="row">
    <div class="col-md-12 mb-3">
        <div class="card">
            <div class="card-body">
                Note reject : <?= $progressAccount["RJCT_NOTE"] ?>
            </div>
        </div>
    </div>
    <div class="col-md-12 mb-3">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title">Agreement</h5>
                    <a href="/export/all?acc=<?php echo $id_acc; ?>" class="btn btn-primary"><i class="fa fa-eye"></i> All Documents</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-repsonsive">
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
                                <td>
                                    <?php if(in_array(strtolower($progressAccount['RTYPE_TYPE_AS']), $MULTIPN)){ ?>
                                        Formulir Nomor : 107.PBK.04.1
                                    <?php } else if(in_array(strtolower($progressAccount['RTYPE_TYPE_AS']), $SPANAME)){ ?>
                                        Formulir Nomor : 107.PBK.04.2
                                    <?php } ?>
                                </td>
                                <td>
                                    Document pemberitahuan adanya resiko<br>
                                    <?php if(in_array(strtolower($progressAccount['RTYPE_TYPE_AS']), $MULTIPN)){ ?>
                                        <small>Maksud dokumen ini adalah memberitahukan bahwa kemungkinan kerugian atau keuntungan dalam perdagangan Kontrak Berjangka bisa mencapai jumlah yang sangat besar. Oleh karena itu, Anda harus berhati-hati dalam memutuskan untuk melakukan transaksi, apakah kondisi keuangan Anda mencukupi.</small>
                                    <?php } else if(in_array(strtolower($progressAccount['RTYPE_TYPE_AS']), $SPANAME)){ ?>
                                        <small>Maksud dokumen ini adalah memberitahukan bahwa kemungkinan kerugian atau keuntungan dalam perdagangan Kontrak derifatif bisa mencapai jumlah yang sangat besar. Oleh karena itu, Anda harus berhati-hati dalam memutuskan untuk melakukan transaksi, apakah kondisi keuangan Anda mencukupi.</small>
                                    <?php } ?><br>
                                </td>
                                <td><a target="_blank" href="/export/pemberitahuan-adanya-risiko?acc=<?php echo $id_acc; ?>" class="btn btn-primary d-flex align-items-center"><i class="fa fa-eye"></i>&nbsp;View</a></td>
                            </tr>
                            <tr>
                                <td>6.</td>
                                <td>
                                    <?php if(in_array(strtolower($progressAccount['RTYPE_TYPE_AS']), $MULTIPN)){ ?>
                                        Formulir Nomor : 107.PBK.05.1
                                    <?php } else if(in_array(strtolower($progressAccount['RTYPE_TYPE_AS']), $SPANAME)){ ?>
                                        Formulir Nomor : 107.PBK.05.2
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php if(in_array(strtolower($progressAccount['RTYPE_TYPE_AS']), $MULTIPN)){ ?>
                                        Perjanjian pemberian amanat secara elektronik on-line untuk transaksi kontrak berjangka
                                    <?php } else if(in_array(strtolower($progressAccount['RTYPE_TYPE_AS']), $SPANAME)){ ?>
                                        Perjanjian pemberian amanat secara elektronik on-line untuk transaksi kontrak derifatif
                                    <?php } ?><br>
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
                                <td><a target="_blank" href="/export/trading-rules?acc=<?php echo $id_acc; ?>" class="btn btn-primary d-flex align-items-center"><i class="fa fa-eye"></i>&nbsp;View</a></td>
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
                                    <small>Proses Penerimaan Nasabah Secara Elektronik Online</small>
                                </td>
                                <td><a target="_blank" href="/export/pernyataan-dana-nasabah?acc=<?php echo $id_acc; ?>" class="btn btn-primary d-flex align-items-center"><i class="fa fa-eye"></i>&nbsp;View</a></td>
                            </tr>
                            <tr>
                                <td>10.</td>
                                <td>-</td>
                                <td>
                                    Surat Pernyataan<br>
                                    <small>Proses Penerimaan Nasabah Secara Elektronik Online</small>
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
                    <!-- <div class="d-flex flex-start flex-wrap gap-2">
                        <a target="_blank" href="/export/all-new?acc=<?php echo $id_acc; ?>" class="btn btn-primary"><i class="fa fa-eye"></i> All Documents</a>
                    </div> -->
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <tbody>
        
                                    <tr>
                                        <!-- <td class="text-start">Product</td>
                                        <td width="3%">:</td>
                                        <td class="text-start">
                                            <strong>
                                                <?php
                                                    if($progressAccount['ACC_PRODUCT'] == '' || $progressAccount['ACC_PRODUCT'] == '-') {
                                                        echo '-';
                                                    } else { echo $progressAccount['ACC_PRODUCT']; };
                                                ?>
                                            </strong>
                                        </td> -->
                                        

                                        <td class="text-start">Email</td>
                                        <td width="3%">:</td>
                                        <td class="text-start">
                                            <?php
                                                if($progressAccount['MBR_EMAIL'] == '' || $progressAccount['MBR_EMAIL'] == '-') {
                                                    echo '-';
                                                } else { echo $progressAccount['MBR_EMAIL']; };
                                            ?>
                                        </td>

                                        <!-- <td class="text-start"></td>
                                        <td width="3%"></td>
                                        <td class="text-start">
                                        </td> -->

                                        <td class="text-start">No. NPWP</td>
                                        <td width="3%">:</td>
                                        <td class="text-start">
                                            <?php
                                                if($progressAccount['ACC_F_APP_PRIBADI_NPWP'] == '' ||$progressAccount['ACC_F_APP_PRIBADI_NPWP'] == '-') {
                                                    echo '-';
                                                } else { echo $progressAccount['ACC_F_APP_PRIBADI_NPWP']; };
                                            ?>
                                        </td>
                                        
                                    </tr>
                                    <tr>
                                        <td class="text-start">Type</td>
                                        <td width="3%" class="text-start">:</td>
                                        <td class="text-start">
                                            <strong>
                                                <?= $progressAccount['RTYPE_NAME'].'/'.$progressAccount['RTYPE_KOMISI'].'/'.(($progressAccount['RTYPE_ISFLOATING'] == 1) ? 'Floating' : ($progressAccount['RTYPE_RATE']/1000)) ?>
                                            </strong>
                                        </td>
        
                                        <td class="text-start">Rate</td>
                                        <td width="3%">:</td>
                                        <td class="text-start">
                                            <strong>
                                                <?php
                                                    if($progressAccount['RTYPE_ISFLOATING'] == 1){
                                                        echo 'Floating';
                                                    }else{ echo number_format($progressAccount['RTYPE_RATE'], 0); }
                                                ?>
                                            </strong>
                                        </td>
                                    </tr>
        
                                    <tr>
                                        <td class="text-start">Nama</td>
                                        <td width="3%">:</td>
                                        <td class="text-start">
                                            <?php
                                                if($progressAccount['ACC_FULLNAME'] == '' ||$progressAccount['ACC_FULLNAME'] == '-') {
                                                    echo '-';
                                                } else { echo $progressAccount['ACC_FULLNAME']; };
                                            ?>
                                        </td>

                                        <td class="text-start">Charge</td>
                                        <td width="3%" class="text-start">:</td>
                                        <td class="text-start"><strong><?= $progressAccount['RTYPE_KOMISI'] ?? 0 ?></strong></td>
                                    </tr>
        
                                    <tr>
                                        <!-- <td class="text-start">Product</td>
                                        <td width="3%">:</td>
                                        <td class="text-start">
                                            <strong>
                                                <?php
                                                    if($progressAccount['ACC_PRODUCT'] == '' ||$progressAccount['ACC_PRODUCT'] == '-') {
                                                        echo '-';
                                                    } else { echo $progressAccount['ACC_PRODUCT']; };
                                                ?>
                                            </strong>
                                        </td> -->
                                        

                                        <td class="text-start">Email</td>
                                        <td width="3%">:</td>
                                        <td class="text-start">
                                            <?php
                                                if($progressAccount['MBR_EMAIL'] == '' ||$progressAccount['MBR_EMAIL'] == '-') {
                                                    echo '-';
                                                } else { echo $progressAccount['MBR_EMAIL']; };
                                            ?>
                                        </td>
                                    </tr>
        
                                    <tr>
                                        <td class="text-start">No Tlp</td>
                                        <td width="3%" class="text-start">:</td>
                                        <td class="text-start">
                                            <?php
                                                if($progressAccount['ACC_F_APP_PRIBADI_HP'] == '' ||$progressAccount['ACC_F_APP_PRIBADI_HP'] == '-') {
                                                    echo '-';
                                                } else { echo $progressAccount['ACC_F_APP_PRIBADI_HP']; };
                                            ?>
                                        </td>
        
                                        <td class="text-start">Tempat lahir</td>
                                        <td width="3%">:</td>
                                        <td class="text-start">
                                            <?php
                                                if($progressAccount['ACC_TEMPAT_LAHIR'] == '' || $progressAccount['ACC_TEMPAT_LAHIR'] == '-') {
                                                    echo '-';
                                                } else {
                                                    echo $progressAccount['ACC_TEMPAT_LAHIR'];
                                                };
                                            ?>
                                        </td>
                                    </tr>
        
                                    <tr>
                                        <td class="text-start">Type Identitas</td>
                                        <td width="3%">:</td>
                                        <td class="text-start">
                                            <?php
                                                if($progressAccount['ACC_TYPE_IDT'] == '' || $progressAccount['ACC_TYPE_IDT'] == '-') {
                                                    echo '-';
                                                } else { echo $progressAccount['ACC_TYPE_IDT']; };
                                            ?>
                                        </td>

                                        <td class="text-start">Ibu Kandung</td>
                                        <td width="3%" class="text-start">:</td>
                                        <td class="text-start">
                                            <?php
                                                if($progressAccount['ACC_F_APP_PRIBADI_IBU'] == '' || $progressAccount['ACC_F_APP_PRIBADI_IBU'] == '-') {
                                                    echo '-';
                                                } else { echo $progressAccount['ACC_F_APP_PRIBADI_IBU']; };
                                            ?>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="text-start">Tanggal lahir</td>
                                        <td width="3%">:</td>
                                        <td class="text-start">
                                            <?php
                                                if($progressAccount['ACC_TANGGAL_LAHIR'] == '' || $progressAccount['ACC_TANGGAL_LAHIR'] == '-') {
                                                    echo '-';
                                                } else { echo $progressAccount['ACC_TANGGAL_LAHIR']; };
                                            ?>
                                        </td>

                                        <td class="text-start">No Identitas</td>
                                        <td width="3%">:</td>
                                        <td class="text-start">
                                            <?php
                                                if($progressAccount['ACC_NO_IDT'] == '' || $progressAccount['ACC_NO_IDT'] == '-') {
                                                    echo '-';
                                                } else { echo $progressAccount['ACC_NO_IDT']; };
                                            ?>
                                        </td>
                                    </tr>
                                    <!-- <tr>
                                        <td class="text-start">Product</td>
                                        <td width="3%" class="text-start">:</td>
                                        <td class="text-start">
                                            <?php
                                                if($progressAccount['ACC_PRODUCT'] == '' ||$progressAccount['ACC_PRODUCT'] == '-') {
                                                    echo '-';
                                                } else { 
                                                    echo (strtolower($progressAccount['ACC_PRODUCT']) == "multilateral" ? $progressAccount['ACC_PRODUCT'] : ""); 
                                                };
                                            ?>
                                        </td>
                                        
                                        <td class="text-start">No. NPWP</td>
                                        <td width="3%">:</td>
                                        <td class="text-start">
                                            <?php
                                                if($progressAccount['ACC_F_APP_PRIBADI_NPWP'] == '' ||$progressAccount['ACC_F_APP_PRIBADI_NPWP'] == '-') {
                                                    echo '-';
                                                } else { echo $progressAccount['ACC_F_APP_PRIBADI_NPWP']; };
                                            ?>
                                        </td>
                                    </tr> -->

                                    <tr>
                                        <td class="text-start">Document Type</td>
                                        <td width="3%" class="text-start">:</td>
                                        <td class="text-start">
                                            <?php
                                                if($progressAccount['ACC_F_APP_FILE_TYPE'] == '' ||$progressAccount['ACC_F_APP_FILE_TYPE'] == '-') {
                                                    echo '-';
                                                } else {
                                                    echo $progressAccount['ACC_F_APP_FILE_TYPE'];
                                                };
                                            ?>
                                        </td>

                                        <td class="text-start">Jenis Pekerjaan</td>
                                        <td width="3%">:</td>
                                        <td class="text-start">
                                            <?php
                                                if($progressAccount['ACC_F_APP_KRJ_NAMA'] == '' ||$progressAccount['ACC_F_APP_KRJ_NAMA'] == '-') {
                                                    echo '-';
                                                } else {
                                                    echo $progressAccount['ACC_F_APP_KRJ_NAMA'].' ('.$progressAccount['ACC_F_APP_KRJ_BDNG'].')';
                                                };
                                            ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="table-responsive mt-3">
                            <table class="table table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th colspan="3" class="bg-secondary text-muted">User Bank</th>
                                    </tr>
                                    <tr>
                                        <th>Nama Bank</th>
                                        <th>No. Rekening</th>
                                        <th>Nama</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($userBanks as $bank) : ?>
                                        <tr>
                                            <td><?= $bank['MBANK_NAME'] ?></td>
                                            <td><?= $bank['MBANK_ACCOUNT'] ?></td>
                                            <td><?= $bank['MBANK_HOLDER'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card text-center h-100">
                                    <?php if($progressAccount['ACC_F_APP_FILE_IMG'] == ''|| $progressAccount['ACC_F_APP_FILE_IMG'] == '-' ){ ?>
                                        <img class="card-img-top" src="/assets/img/unknown-file.png" width="75%">
                                    <?php } else { ?>
                                        <a target="_blank" href="<?php echo FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_IMG']); ?>"></a>
                                        <a target="_blank" href="<?php echo FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_IMG']); ?>">
                                            <img class="card-img-top" width="75%" src="<?php echo FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_IMG']); ?>">
                                        </a>
                                    <?php }; ?>
                                    <div class="card-body">
                                        <a target="_blank" href="<?php echo FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_IMG']); ?>">
                                            Dokumen Pendukung
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="card text-center h-100">
                                    <?php if($progressAccount['ACC_F_APP_FILE_FOTO'] == ''|| $progressAccount['ACC_F_APP_FILE_FOTO'] == '-' ){ ?>
                                        <img class="card-img-top" src="/assets/img/unknown-file.png" width="75%">
                                    <?php } else { ?>
                                        <a target="_blank" href="<?php echo FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_FOTO']); ?>"></a>
                                        <a target="_blank" href="<?php echo FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_FOTO']); ?>">
                                            <img class="card-img-top" width="75%" src="<?php echo FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_FOTO']); ?>">
                                        </a>
                                    <?php }; ?>
                                    <div class="card-body">
                                        <a target="_blank" href="<?php echo FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_FOTO']); ?>">
                                            Foto Terbaru
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="card text-center h-100">
                                    <?php if($progressAccount['ACC_F_APP_FILE_ID'] == ''|| $progressAccount['ACC_F_APP_FILE_ID'] == '-' ){ ?>
                                        <img class="card-img-top" src="/assets/img/unknown-file.png" width="75%">
                                    <?php } else { ?>
                                        <a target="_blank" href="<?php echo FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_ID']); ?>"></a>
                                        <a target="_blank" href="<?php echo FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_ID']); ?>">
                                            <img class="card-img-top" width="75%" src="<?php echo FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_ID']); ?>">
                                        </a>
                                    <?php }; ?>
                                    <div class="card-body">
                                        <a target="_blank" href="<?php echo FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_ID']); ?>">
                                            Foto Identitas
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="card text-center h-100">
                                    <?php if($progressAccount['ACC_F_APP_FILE_IMG2'] == ''|| $progressAccount['ACC_F_APP_FILE_IMG2'] == '-' ){ ?>
                                        <img class="card-img-top" src="/assets/img/unknown-file.png" width="75%">
                                    <?php } else { ?>
                                        <a target="_blank" href="<?php echo FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_IMG2']); ?>"></a>
                                        <a target="_blank" href="<?php echo FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_IMG2']); ?>">
                                            <img class="card-img-top" width="75%" src="<?php echo FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_IMG2']); ?>">
                                        </a>
                                    <?php }; ?>
                                    <div class="card-body">
                                        <a target="_blank" href="<?php echo FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_IMG2']); ?>">
                                            Dokumen Pendukung Lainya
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>