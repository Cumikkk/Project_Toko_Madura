<?php

use App\Factory\FileUploadFactory;
use App\Models\Account;
use App\Models\FileUpload;
use App\Models\Helper;
use App\Models\User;

$myBanks = User::myBank($user['MBR_ID']); 
$depositData = Account::getDepositNewAccount_data($realAccount['ID_ACC']);
$depositHistory = Account::getDepositNewAccount_History($realAccount['ID_ACC']);
die("<script>location.href='/account/create?page=selesai';</script>");
?>

<div class="row">
    <div class="col-md-9 mx-auto mb-3">
        <?php if(!empty($depositData)) : ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title text-primary">Detail Deposit New Account</h5>
                    <div class="table-responsic">
                        <table class="table table-hover" style="text-align: left; table-layout: fixed;" width="100%">
                            <tbody>
                                <tr>
                                    <td width="20%" class="top-align fw-bold">Bank Nasabah</td>
                                    <td width="3%" class="top-align"> : </td>
                                    <td class="top-align text-start"><?= $depositData['DPWD_BANKSRC']; ?></td>
                                </tr>
                                <tr>
                                    <td width="20%" class="top-align fw-bold">Bank Penerima</td>
                                    <td width="3%" class="top-align"> : </td>
                                    <td class="top-align text-start"><?= $depositData['DPWD_BANK']; ?></td>
                                </tr>
                                <tr>
                                    <td width="20%" class="top-align fw-bold">Jumlah Deposit</td>
                                    <td width="3%" class="top-align"> : </td>
                                    <td class="top-align text-start"><?= $depositData['DPWD_CURR_FROM'] ?> <?= Helper::formatCurrency($depositData['DPWD_AMOUNT_SOURCE']); ?></td>
                                </tr>
                                <tr>
                                    <td width="20%" class="top-align fw-bold">Status</td>
                                    <td width="3%" class="top-align"> : </td>
                                    <td class="top-align text-start">
                                        <?php if($depositData['DPWD_STS'] == 0) : ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php elseif($depositData['DPWD_STS'] == -1) : ?>
                                            <span class="badge bg-success">Success</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="20%" class="top-align fw-bold">Bukti Transfer</td>
                                    <td width="3%" class="top-align"> : </td>
                                    <td class="top-align text-start">
                                        <input type="file" class="dropify" data-default-file="<?= FileUploadFactory::aws()->awsFile($depositData['DPWD_PIC'] ?? "") ?>" disabled>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <script type="text/javascript">
                $(document).ready(function() {
                    $('.dropify').dropify();
                })
            </script>

        <?php else : ?>
            <form class="mb-3" method="post" enctype="multipart/form-data" id="form-deposit-new-account">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6 text-center border-end-1">
                                <div class="card mb-20">
                                    <div class="card-header">
                                        <h4>Bank Pengirim</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-12 mb-2">
                                                <label for="dpnewacc_bankusr" class="form-label">Pilih Bank</label>
                                                <select name="dpnewacc_bankusr" id="dpnewacc_bankusr" class="form-control form-select text-center" required>
                                                    <option disabled selected value>Pilih bank yang anda miliki</option>
                                                    <?php foreach($myBanks as $bank) : ?>
                                                        <option value="<?= md5(md5($bank['ID_MBANK'])) ?>" data-pemilik="<?= $bank['MBANK_HOLDER'] ?>" data-rekening="<?= $bank['MBANK_ACCOUNT'] ?>">
                                                            <?= $bank['MBANK_NAME'] ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-12 mb-2">
                                                <label class="form-label">Nama Pemilik</label>
                                                <input type="text" class="form-control" id="bank_pemilik" readonly>
                                            </div>
                                            <div class="col-12 mb-2">
                                                <label class="form-label">Nomor Rekening</label>
                                                <input type="text" class="form-control" id="bank_rekening" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 text-center border-start-1">
                                <div class="card mb-20">
                                    <div class="card-header">
                                        <h4>Nominal & Bukti Transfer Deposit</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-12 mb-2">
                                                <label for="dpnewacc_bankcmpy" class="form-label">Pilihan Bank Perusahaan Yang Akan Dituju</label>
                                                <select name="dpnewacc_bankcmpy" id="dpnewacc_bankcmpy" class="form-control form-select text-center" required>
                                                    <?php $sqlGetBankAdm = $db->query("SELECT * FROM tb_bankadm WHERE BKADM_CURR = '".($realAccount['RTYPE_CURR'] ?? "")."'"); ?>
                                                    <?php foreach($sqlGetBankAdm->fetch_all(MYSQLI_ASSOC) as $bank_admin) : ?>
                                                        <option data-curr="<?= $bank_admin['BKADM_CURR'] ?>" value="<?= md5(md5($bank_admin['ID_BKADM'])); ?>">
                                                            <?= implode(" / ", [$bank_admin['BKADM_HOLDER'], $bank_admin['BKADM_ACCOUNT']]) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
    
                                            <div class="col-12 mb-2">
                                                <label class="form-label">Nominal Deposit</label>
                                                <div class="input-group">
                                                    <span class="input-group-text" id="currency"></span>
                                                    <input type="text" name="dpnewacc_dpstval" placeholder="Nilai Deposit" class="form-control amount-formatter" autocomplete="off" required>
                                                </div>
                                            </div>
    
                                            <div class="col-12 mb-2">
                                                <label class="form-label">Foto Bukti Transfer</label>
                                                <div id="imgframe" style="content-visibility : <?php echo (!empty($bkdpsimg)) ? 'unset' : 'hidden' ?>;">
                                                    <img id="dpnewacc_tfprove" class="mb-3" <?php echo (!empty($bkdpsimg)) ? 'src="'.$aws_folder.$bkdpsimg.'"' : NULL ?>>
                                                </div>
                                                <input type="file" class="form-control dropify" name="dpnewacc_tfprove" accept=".png, .jpg, .jpeg" required data-src="<?php echo $bkdpsimg ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex flex-row justify-content-end align-items-center gap-2 mt-25">
                            <a href="<?= ($prevPage['page'])? ("/account/create?page=".$prevPage['page']) : "javascript:void(0)"; ?>" class="btn btn-secondary">Previous</a>
                            <button type="submit" class="btn btn-primary">Next</button>
                        </div>
                    </div>
                </div>
            </form>
            
            <script type="text/javascript">
                $(document).ready(function() {
                    $('.dropify').dropify();
                    $('#dpnewacc_bankusr').on('change', function() {
                        let option = $(this).find('option:selected');
                        if(option) {
                            $('#bank_pemilik').val( option.data('pemilik') )
                            $('#bank_cabang').val( option.data('cabang') )
                            $('#bank_rekening').val( option.data('rekening') )
                            $('#bank_jenis').val( option.data('jenis') )
                        }
                    })
            
                    $('#dpnewacc_bankcmpy').on('change', function() {
                        let option = $(this).find('option:selected');
                        if(option) {
                            $('#currency').text(option.data('curr'))
                        }
                    }).change();
            
                    $('#form-deposit-new-account').on('submit', function(event) {
                        event.preventDefault();
                        Swal.fire({
                            text: "Please wait...",
                            allowOutsideClick: false,
                            didOpen: function() {
                                Swal.showLoading();
                            }
                        })
                        
                        $.ajax({
                            url: "/ajax/regol/depositNewAccount",
                            type: "POST",
                            dataType: "json",
                            data: new FormData(this),
                            processData: false,
                            contentType: false,
                            cache: false
                        }).done(function(resp) {
                            Swal.fire(resp.alert).then(() => {
                                if(resp.success) {
                                    location.href = resp.redirect
                                }
                            })
                        })
                    })
                })
            </script>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <h5 class="card-title text-primary mb-3">Riwayat</h5>
                <div class="table-responsive">
                    <table class="table table-hover" style="table-layout: fixed;" width="100%">
                        <thead>
                            <tr>
                                <th width="15%" class="text-start">Tanggal</th>
                                <th class="text-start">Bank Nasabah</th>
                                <th class="text-start">Bank Penerima</th>
                                <th class="text-start">Jumlah</th>
                                <th width="20%" class="text-start">Bukti Transfer</th>
                                <th width="10%" class="text-start">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($depositHistory as $history) : ?>
                                <tr>
                                    <td width="15%" class="top-align"><?= date("Y-m-d H:i:s", strtotime($history['NOTE_DATETIME'])); ?></td>
                                    <td class="top-align text-start"><?= $history['DPWD_BANKSRC'] ?></td>
                                    <td class="top-align text-start"><?= $history['DPWD_BANK'] ?></td>
                                    <td class="top-align text-start"><?= $history['DPWD_CURR_FROM'] ?> <?= Helper::formatCurrency($history['DPWD_AMOUNT_SOURCE']) ?></td>
                                    <td width="20%" class="top-align text-start"><a target="_blank" href="<?= FileUploadFactory::aws()->awsFile($history['DPWD_PIC']) ?>"><i>Lihat disini</i></a></td>
                                    <td width="10%" class="top-align text-start">
                                        <?php if($history['DPWD_STS'] == 0) : ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php elseif($history['DPWD_STS'] == 1) : ?>
                                            <span class="badge bg-danger">Ditolak</span>
                                        <?php elseif($history['DPWD_STS'] == -1) : ?>
                                            <span class="badge bg-success">Success</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr style="border-bottom: 1px dashed white;">
                                    <td class="top-align text-start" colspan="6">Note: <?= $history['NOTE_NOTE'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>