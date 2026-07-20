<?php

use App\Models\Account;
use App\Models\Regol;

$accountSpa = Account::getAvailableProduct($userid, "spa");
$accountMultilateral = Account::getAvailableProduct($userid, "multilateral");
$accountTypes = [
    'spa'   => $accountSpa,
    // 'multilateral' => $accountMultilateral 
];

$accountCategories = array_keys($accountTypes);
$selectedAccType = strtolower($realAccount['RTYPE_TYPE'] ?? "");
$cddList = Regol::cddTypeArray();
$isHaveCddsAccount = Account::isHaveCddsAccount($user['MBR_ID']);
$isHaveCddAccount = Account::isHaveCddAccount($user['MBR_ID']);

/** jika sudah punya akun dengan cdd standar */
if($isHaveCddAccount) {
    $cddList = array_filter($cddList, fn($cdd) => $cdd !== App\Models\Regol::$cddTypeSederhana);
}

/** Selected Account */
if(!empty($selectedAccType)) {
    foreach($accountCategories as $category) {
        foreach($accountTypes[ $category ] as $typeKey => $types) {
            foreach($types['products'] as $productKey => $product) {
                if($selectedAccType == strtolower($types['type'])) {
                    $accountTypes[ $category ][ $typeKey ]['selected'] = true; 
                    $accountTypes[ $category ][ $typeKey ]['products'][ $productKey ]['selected'] = true; 
                }
            }
        }
    }
} else {
    if(isset($accountTypes['spa'][ 0 ])) {
        $accountTypes[ 'spa' ][ 0 ]['selected'] = true;
    }
}

?>

<style>
    .account-card.active .file-manager-card {
        border: 2px solid var(--bs-primary-color);
    }


    input[type="radio"] {
        appearance: none;
        display: none;
    }

    input[type="radio"]:checked + label > div.file-manager-card {
        border: 2px solid var(--bs-primary-color);
    }

    .select-type {
        cursor: pointer;
    }

</style>
<div class="row">
    <div class="col-md-8 mx-auto">
        <form method="post" id="form-account-type">
            <input type="hidden" name="csrf_token" value="<?= uniqid(); ?>">
            <div class="card">
                <div class="card-body">
                    <!-- <div class="mb-3">
                        <label class="form-label">Demo Account</label>
                        <input type="text" class="form-control" value="<?//= $demoAccount['ACC_LOGIN'] ?? ""; ?>" required disabled>
                    </div> -->

                    <div class="mb-3">
                        <label class="form-label">CDD Tipe</label>
                        <select id="cdd-type" name="cdd-type" class="form-select mb-3" required>
                            <?php foreach($cddList as $cdd) : ?>
                                <option value="<?= $cdd ?>" <?= ($cdd == ($realAccount['ACC_CDD'] ?? 0))? "selected" : ""; ?>><?= App\Models\Regol::cddType($cdd)['text'] ?? "-"; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="div-account-type">
                        <div class="mb-3">
                            <label class="form-label">Account Type</label>
                            <select id="acc-type" class="form-select mb-3" required>
                                <?php foreach($accountCategories as $accType) : ?>
                                    <option data-type="<?= $accType ?>" value="<?= strtoupper($accType) ?>" <?= strtoupper($realAccount['RTYPE_TYPE_AS'] ?? "") == strtoupper($accType)? "selected" : ""; ?>><?= strtoupper($accType); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label class="form-label">Product Rate</label>
                            <select id="acc-rate" class="form-select mb-3" required>
                                <?php 
                                    $sqlGet = $db->query("
                                        SELECT IF(tb_racctype.RTYPE_RATE = 0, 'Floating', tb_racctype.RTYPE_RATE) AS RTYPE_RATE
                                        FROM tb_racctype
                                        WHERE tb_racctype.RTYPE_TYPE <> 'DEMO'
                                        GROUP BY tb_racctype.RTYPE_RATE
                                        ORDER BY tb_racctype.RTYPE_RATE DESC
                                    ");
                                    
                                    // Tentukan rate yang dipilih dari realAccount
                                    $selectedRate = '10000'; // default
                                    if(!empty($realAccount)) {
                                        $selectedRate = ($realAccount['RTYPE_ISFLOATING'])? "Floating" : $realAccount['RTYPE_RATE'];
                                    }
                                    
                                    foreach($sqlGet->fetch_all(MYSQLI_ASSOC) as $product) :
                                        $isSelected = (strtoupper($product['RTYPE_RATE']) == strtoupper($selectedRate)) ? 'selected' : '';
                                ?>
                                    <option value="<?= strtoupper($product['RTYPE_RATE'] ?? "") ?>" <?= $isSelected ?>><?= strtoupper($product['RTYPE_RATE'] ?? ""); ?></option>
                                <?php endforeach; ?>
                            </select>
                            
                            <?php foreach($accountCategories as $type) : ?>
                                <?php $lowerType = strtolower($type) ?>
                                <div class="tab-categories" id="nav-<?= $lowerType ?>" <?= $lowerType != strtolower($realAccount['RTYPE_TYPE_AS'] ?? "")? "style='display: none;'" : ""; ?>>
                                    <nav class="mb-3">
                                        <?php if($lowerType == "multilateral") : ?>
                                            <div class="alert alert-warning">
                                                <p>Hubungi CS Kami untuk mendaftar akun <b>Multilateral</b></p>
                                            </div>
                                        <?php endif; ?>
    
                                        <div class="btn-box d-flex flex-wrap gap-2" id="nav-tab" role="tablist">
                                            <?php foreach($accountTypes[ $lowerType ] ?? [] as $key => $category) : ?>
                                                <?php
                                                    // Collect all rates available in this category
                                                    $categoryRates = [];
                                                    foreach($category['products'] as $prod) {
                                                        if(strtoupper($prod['RTYPE_TYPE_AS']) == strtoupper($type)) {
                                                            $pRate = ($prod['RTYPE_RATE'] == 0 || strtolower($prod['RTYPE_CURR']) != 'idr') ? 'Floating' : $prod['RTYPE_RATE'];
                                                            $categoryRates[] = strtoupper($pRate);
                                                        }
                                                    }
                                                    $categoryRatesStr = implode(',', array_unique($categoryRates));
                                                ?>
                                                <button class="btn btn-sm btn-outline-primary category-tab <?= ($category['selected'] ?? false)? "active" : ""; ?>" id="<?= $lowerType.$category['type'] ?>-tab" data-bs-toggle="tab" data-bs-target="#tab-<?= $lowerType.$category['type'] ?>" data-rates="<?= $categoryRatesStr ?>" data-category="<?= $category['type'] ?>" type="button" role="tab" aria-controls="tab-<?= $lowerType.$category['type'] ?>" aria-selected="<?= $key == 0? "true" : "false"; ?>"><?= strtoupper($category['type']) ?></button>
                                            <?php endforeach; ?>
                                        </div>
                                    </nav>
        
                                    <div class="tab-content profile-edit-tab">
                                        <?php foreach($accountTypes[ $lowerType ] ?? [] as $key => $category) : ?>
                                            <div class="tab-pane fade category-tab-pane <?= ($category['selected'] ?? false)? "show active" : ""; ?>" id="tab-<?= $lowerType.$category['type'] ?>" data-category="<?= $category['type'] ?>" role="tabpanel" aria-labelledby="tab-<?= $lowerType.$category['type'] ?>" tabindex="0">
                                                <div class="row">
                                                    <?php foreach($category['products'] as $accType) : ?>
                                                        <?php if(strtoupper($accType['RTYPE_TYPE_AS']) == strtoupper($type)) : ?>
                                                            <?php $productRate = ($accType['RTYPE_ISFLOATING'] == 1) ? 'Floating' : $accType['RTYPE_RATE']; ?>
                                                            <div class="col-md-6 product-item" data-rate="<?= strtoupper($productRate) ?>">
                                                                <input type="radio" name="account-type" id="<?= $accType['RTYPE_SUFFIX'] ?>" value="<?= $accType['RTYPE_SUFFIX'] ?>" data-category="<?= $category['type'] ?>" <?= ($accType['selected'] ?? false)? "checked" : ""; ?>>
                                                                <label for="<?= $accType['RTYPE_SUFFIX'] ?>" class="w-100 h-100 select-type">
                                                                    <div class="file-manager-card">
                                                                        <div class="top">
                                                                            <div class="part-icon">
                                                                                <span><?= strtoupper($accType['RTYPE_NAME']) ?></span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="bottom">
                                                                            <div class="left">
                                                                                <div class="d-flex flex-row mb-3">
                                                                                    <div><img src="<?= Config\Core\SystemInfo::app('CLIENT_URL') ?>/assets/icons/webp/dollar-circle.webp"></div>
                                                                                    <div style="margin-left:5px;">
                                                                                        <span class="file-quantity mb-1">Rate</span>
                                                                                        <span class="file-quantity mb-1"><?= $productRate ?></span>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="d-flex flex-row mb-3">
                                                                                    <div><img src="<?= Config\Core\SystemInfo::app('CLIENT_URL') ?>/assets/icons/webp/global.webp"></div>
                                                                                    <div style="margin-left:5px;">
                                                                                        <span class="file-quantity mb-1">Currency</span>
                                                                                        <span class="file-quantity mb-1"><?= $accType['RTYPE_CURR'] ?></span>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="d-flex flex-row mb-3">
                                                                                    <div style="margin-right:7px;"><img src="<?= Config\Core\SystemInfo::app('CLIENT_URL') ?>/assets/icons/webp/money-2.webp"></div>
                                                                                    <div>
                                                                                        <span class="file-quantity mb-1"><strong>Commission</strong></span>
                                                                                        <span class="file-quantity mb-1">$<?= $accType['RTYPE_KOMISI'] ?></span>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="d-flex flex-row mb-3">
                                                                                    <div style="margin-right:7px;"><img src="<?= Config\Core\SystemInfo::app('CLIENT_URL') ?>/assets/icons/webp/arrow-up-01.webp"></div>
                                                                                    <div>
                                                                                        <span class="file-quantity mb-1"><strong>Leverage</strong></span>
                                                                                        <span class="file-quantity mb-1"><?= $accType['RTYPE_LEVERAGE'] ?></span>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="d-flex flex-row mb-3">
                                                                                    <div style="margin-right:7px;"><img src="<?= Config\Core\SystemInfo::app('CLIENT_URL') ?>/assets/icons/webp/flash.webp"></div>
                                                                                    <div>
                                                                                        <span class="file-quantity mb-1"><strong>Free Swap</strong></span>
                                                                                        <span class="file-quantity mb-1"><?= $accType['RTYPE_SWAP'] ?></span>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="d-flex flex-row mb-3">
                                                                                    <div style="margin-right:7px;"><img src="<?= Config\Core\SystemInfo::app('CLIENT_URL') ?>/assets/icons/webp/activity.webp"></div>
                                                                                    <div>
                                                                                        <span class="file-quantity mb-1"><strong>Spread</strong></span>
                                                                                        <span class="file-quantity mb-1"><?= $accType['RTYPE_SPREAD'] ?></span>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="d-flex flex-row mb-3">
                                                                                    <div style="margin-right:7px;">
                                                                                        <img src="<?= Config\Core\SystemInfo::app('CLIENT_URL') ?>/assets/icons/webp/wallet-add.webp">
                                                                                    </div>
                                                                                    <div>
                                                                                        <span class="file-quantity mb-1"><strong>Min Deposit</strong></span>
                                                                                        <span class="file-quantity mb-1"><?= $accType['RTYPE_MINDEPOSIT'] ?></span>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="d-flex flex-row mb-3">
                                                                                    <div><img src="<?= Config\Core\SystemInfo::app('CLIENT_URL') ?>/assets/icons/webp/box-tick.webp"></div>
                                                                                    <div style="margin-left:5px;">
                                                                                        <span class="file-quantity mb-1">Min Trade</span>
                                                                                        <span class="file-quantity mb-1"><?= $accType['RTYPE_MINTRADE'] ?></span>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="d-flex flex-row mb-3">
                                                                                    <div style="margin-right:7px;"><img src="<?= Config\Core\SystemInfo::app('CLIENT_URL') ?>/assets/icons/webp/shield-tick.webp"></div>
                                                                                    <div>
                                                                                        <span class="file-quantity mb-1"><strong>Perlindungan Saldo Negatif</strong></span>
                                                                                        <span class="file-quantity mb-1">Aktif</span>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="d-flex flex-row mb-3">
                                                                                    <div style="margin-right:7px;"><img src="<?= Config\Core\SystemInfo::app('CLIENT_URL') ?>/assets/icons/webp/box.webp"></div>
                                                                                    <div>
                                                                                        <span class="file-quantity mb-1"><strong>Produk</strong></span>
                                                                                        <span class="file-quantity mb-1 mb-1">Forex, Metals, Futures</span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <!-- <div class="right">
                                                                                <span class="storage-used"></span>
                                                                            </div> -->
                                                                        </div>
                                                                    </div>
                                                                </label>
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <hr>
                    <div id="snk_cdd1" class="snk_cdd" style="display: none;">
                        <div style="font-size: 14px;"><span class="text-danger">*</span>Syarat dan ketentuan CDD Standar:</div>
                        <ol style="list-style-position: inside; padding-left: 0px; font-size: 13px;">
                            <li>Wajib memiliki NPWP.</li>
                            <li>Bisa nasabah perorangan dan Badan usaha.</li>
                            <li>Bisa membuka lebih dari 1 (satu) akun.</li>
                            <li>Posisi terbuka bisa lebih dari 1 (satu) lot.</li>
                            <li>Tidak ada batasan maksimum equity.</li>
                        </ol>
                    </div>

                    <div id="snk_cdd2" class="snk_cdd" style="display: none;">
                        <div style="font-size: 14px;"><span class="text-danger">*</span>Syarat dan ketentuan CDD Sederhana:</div>
                        <ol style="list-style-position: inside; padding-left: 0px; font-size: 13px;">
                            <li>Hanya boleh membuka 1 akun di pialang berjangka yang sama.</li>
                            <li>Hanya untuk nasabah perorangan.</li>
                            <li>Tidak wajib memiliki NPWP.</li>
                            <li>Deposit margin lebih kecil atau sama dengan Rp10.000.000,-.</li>
                            <li>Minimal deposit $100.</li>
                            <li>Maksimum posisi terbuka hanya 1 (satu) lot.</li>
                            <li>Profil tingkat risiko APU-PPT calon Nasabah masuk dalam kategori risiko rendah.</li>
                            <li>Pada saat harga penutupan equity tidak mencapai 25juta atau lebih.</li>
                        </ol>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex flex-row justify-content-end align-items-center gap-2 mt-25 flex-wrap">
                        <?php if($count_realaccount > 0) : ?>
                            <div class="form-check me-4">
                                <input class="form-check-input" type="checkbox" name="option" value="1" id="accountTypeOption" skip>
                                <label class="form-check-label" for="accountTypeOption">
                                    Saya menyatakan bahwa data saya sebelumnya sama dengan data sekarang
                                </label>
                            </div>
                        <?php endif; ?>

                        <?php if($prevPage) : ?>
                            <a href="<?= ($prevPage['page'])? ("/account/create?page=".$prevPage['page']) : "javascript:void(0)"; ?>" class="btn btn-secondary">Previous</a>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary">Next</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#acc-type').on('change', function() {
            let type = $(this).find('option:selected').data('type');
            $('.tab-categories').hide();
            $(`#nav-${type}`).show();
        }).change();
        
        $('#acc-rate').on('change', function() {
            let selectedRate = $(this).val().toUpperCase();
            
            // Filter product items berdasarkan rate yang dipilih
            $('.product-item').each(function() {
                let productRate = $(this).data('rate');
                if(productRate == selectedRate) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });

            // Filter category tabs - hanya tampilkan tab yang memiliki produk dengan rate yang dipilih
            $('.category-tab').each(function() {
                let categoryRates = $(this).data('rates').toString().split(',');
                if(categoryRates.includes(selectedRate)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });

            // Jika tab yang aktif disembunyikan, aktifkan tab pertama yang visible
            let activeTab = $('.category-tab.active:visible');
            if(activeTab.length === 0) {
                let firstVisibleTab = $('.category-tab:visible').first();
                if(firstVisibleTab.length > 0) {
                    firstVisibleTab.tab('show');
                }
            }

            // Uncheck semua radio button yang tersembunyi
            $('.product-item:hidden input[type="radio"]').prop('checked', false);
            
        }).change();
        
        // Auto-select produk pertama yang visible (opsional)
        let firstVisible = $('.product-item:visible').first().find('input[type="radio"]');
        if(firstVisible.length > 0 && $('.product-item:visible input[type="radio"]:checked').length === 0) {
            firstVisible.prop('checked', true);
        }

        $('.category-tab').on('shown.bs.tab', function (e) {
            // Uncheck semua radio button yang tersembunyi
            $('.product-item:hidden input[type="radio"]').prop('checked', false);
        });

        $("#cdd-type").on('change', function() {
            $(".snk_cdd").hide();
            let selectedCdd = $("#cdd-type").val();
            if(!selectedCdd) {
                $(".div-account-type").hide();
                return;
            }

            $(`#snk_cdd${selectedCdd}`).show();
            $(".div-account-type").show();
        }).change();

        $('#form-account-type').on('submit', function(event){
            event.preventDefault();
            let data = Object.fromEntries(new FormData(this).entries());
            Swal.fire({
                text: "Please wait...",
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            })
            
            $.post("/ajax/regol/accountType", data, function(resp) {
                if(!resp.success) {
                    Swal.fire(resp.alert);
                    return;
                }
                
                location.href = resp.redirect
            }, 'json')
        })

        $('.product-item').on('change', function(el) {
            let product = $(el.currentTarget).find('input[name="account-type"]').val();
            if(!product) {
                Swal.fire("Failed", "Invalid Product", "error");
                return;
            }

            $.post("/ajax/regol/updateAccountType", {product: product}, function(resp) {
                if(!resp.success) {
                    Swal.fire(resp.alert);
                    $('.product-item input[type="radio"]').prop('checked', false);
                    return;
                }
            }, 'json')
        })
    })
</script>