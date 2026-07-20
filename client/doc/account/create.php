<?php

use App\Models\Account;
use App\Models\Helper;

try {
    $myAccounts = Account::myAccount($user['MBR_ID']);
    $demoAccount = Account::getDemoAccount($userid);
    $realAccount = Account::getProgressRealAccount($userid);
    $count_realaccount = (count($myAccounts) ?? 0);
    $firstPage = (empty($demoAccount)? "create-demo" : "account-type");
    $currentPage = Helper::form_input($_GET['page'] ?? $firstPage);
    $isHaveCddsAccount = Account::isHaveCddsAccount($user['MBR_ID']);

    /** jika sudah memiliki akun, check type nya cdd apa */
    if($isHaveCddsAccount) {
        die("<script>alert('Anda sudah memiliki akun dengan tipe CDD Sederhana, untuk membuka akun lain, silahkan melakukan upgrade ke CDD Standar'); location.href = '/account'; </script>");
    }

    /** Tidak bisa akses create account saat masih ada progress real account dalam prosess */
    if($realAccount && in_array($realAccount['ACC_STS'], [-1, 1])) {
        if(!empty($demoAccount)) {
            die("<script>alert('Akun anda sedang diprosess'); location.href = '/account'; </script>");
        }
    }
    
    function retnull($key, $default = 0){ 
        global $realAccount;
        return ($realAccount[ $key ] ?? $default ?? "");
    }
    
    $firstStep = [[]];
    if(empty($demoAccount)) {
        $firstStep[] = [
            'title' => "Buat Akun Demo",
            'success' => !empty($demoAccount),
            'page' => "create-demo",
            'show' => empty($demoAccount)
        ];
    }

    /** Step */
    $steps = array_merge($firstStep, [
        [
            'title' => "Rate & Jenis Real Account",
            'success' => !empty($realAccount),
            'page' => "account-type",
            'show' => true
        ],
        [
            'title' => "Profile Perusahaan Pialang",
            'success' => !empty($realAccount['ACC_F_PROFILE']),
            'page' => "profile-perusahaan",
            'show' => true
        ],
        [
            'title' => "Pernyataan Simulasi Perdagangan Berjangka",
            'success' => !empty($realAccount['ACC_F_SIMULASI']),
            'page' => "pernyataan-simulasi",
            'show' => true
        ],
        [
            'title' => "Pernyataan Pengalaman Transaksi Perdagangan Berjangka",
            'success' => !empty($realAccount['ACC_F_PENGLAMAN']),
            'page' => "pernyataan-pengalaman",
            'show' => true
        ],
        [
            'title' => "Pernyataan Pengungkapan #1",
            'success' => !empty($realAccount['ACC_F_DISC']),
            'page' => "pernyataan-pengungkapan-1",
            'show' => true
        ],
        [
            'title' => "SID Khusus Transaksi Produk Derivatif Keuangan",
            'success' => !empty($realAccount['ACC_F_SID']),
            'page' => "draft-sid",
            'show' => true
        ],
        [
            'title' => "Aplikasi Pembukaan Rekening",
            'success' => !empty($realAccount['ACC_F_APP']),
            'page' => "aplikasi-pembukaan-rekening",
            'show' => true
        ],
        [
            'title' => "Pernyataan Pengungkapan #2",
            'success' => !empty($realAccount['ACC_F_DISC2']),
            'page' => "pernyataan-pengungkapan-2",
            'show' => true
        ],
        [
            'title' => "Formulir Dokumen Resiko",
            'success' => !empty($realAccount['ACC_F_RESK']),
            'page' => "formulir-dokumen-resiko",
            'show' => true
        ],
        [
            'title' => "Pernyataan Pengungkapan #3",
            'success' => !empty($realAccount['ACC_F_DISC3']),
            'page' => "pernyataan-pengungkapan-3",
            'show' => true
        ],
        [
            'title' => "Perjanjian Pemberian Amanat",
            'success' => !empty($realAccount['ACC_F_PERJ']),
            'page' => "perjanjian-pemberian-amanat",
            'show' => true
        ],
        [
            'title' => "Peraturan Perdagangan",
            'success' => !empty($realAccount['ACC_F_TRDNGRULE']),
            'page' => "peraturan-perdagangan",
            'show' => true
        ],
        [
            'title' => "Pernyataan Bertanggung Jawab",
            'success' => !empty($realAccount['ACC_F_KODE']),
            'page' => "pernyataan-bertanggung-jawab",
            'show' => true
        ],
        [
            'title' => "Pernyataan Dana Nasabah",
            'success' => !empty($realAccount['ACC_F_DANA']),
            'page' => "pernyataan-dana-nasabah",
            'show' => true
        ],
        [
            'title' => "Pernyataan Pengungkapan #4",
            'success' => !empty($realAccount['ACC_F_DISC4']),
            'page' => "pernyataan-pengungkapan-4",
            'show' => true
        ],
        // [
        //     'title' => "Verifikasi Identitas",
        //     'success' => (($realAccount['ACC_DOC_VERIF'] ?? 0) == -1),
        //     'page' => "verifikasi-identitas",
        //     'show' => true
        // ],
        [
            'title' => "Kelengkapan Formulir",
            'success' => !empty($realAccount['ACC_F_CMPLT']),
            'page' => "kelengkapan-formulir",
            'show' => true
        ],
        [
            'title' => "Menunggu Konfirmasi Admin",
            'success' => !empty($realAccount['ACC_F_CMPLT']),
            'page' => "selesai",
            'show' => true
        ],
        // [
        //     'title' => "Deposit New Account",
        //     'success' => !empty($realAccount['ACC_F_CMPLT']),
        //     'page' => "deposit-new-account",
        //     'show' => ($realAccount && $realAccount['ACC_STS'] == 1 && $realAccount['ACC_WPCHECK'] >= 1)
        // ],
    ]);

    /** Pengecekan step selesai / pernah diisi, dan belum pernah diisi */
    $currIndex   = array_search($currentPage, array_column($steps, "page"));
    if($currIndex === FALSE) {
        die("<script>location.href = '/account/create?page=account-type'; </script>");
    }

    $currIndex  += 1; // Karena array pertama kosong jadi tidak terhitung saat menggunakan array_column
    $nextPage    = $steps[ $currIndex + 1 ] ?? [];
    $prevPage    = $steps[ $currIndex - 1 ] ?? [];


    /** Create Demo Wajib Success */
    if($steps[1]['success'] === FALSE && $currentPage != $firstPage) {
        die("<script>location.href = '/account/create?page={$firstPage}'; </script>");
    
    } else {
        /** Check Step sebelumnya sudah selesai / belum */
        $prevIndex  = max(1, $currIndex - 1);
        if($steps[ $prevIndex ]['success'] === FALSE) {
            foreach($steps as $key2 => $s) {
                if(empty($s)) {
                    continue;
                }

                if(in_array($currentPage, ['create-demo', 'account-type'])) {
                    break;
                }

                if($steps[ $key2 ]['success'] === FALSE) {
                    die("<script>location.href = '/account/create?page=".($steps[ $key2 ]['page'] ?? $firstPage)."'; </script>");
                }
            }
        }
    }

} catch (Exception $e) {
    throw $e;
}

?>

<link rel="stylesheet" href="/assets/css/regol.css">
<div class="row">
    <div class="col-12">
        <div class="panel">
            <div class="mt-2 mb-2 part text-center step-wizard" id="nav-tab" role="tablist">
                <ul class="step-wizard-list">
                    <?php foreach($steps as $step => $info) : ?>
                        <?php if(!empty($info) && $info['show']) : ?>
                            <?php $pageLink = ("/account/create?page=" . $info['page']); ?>
                            <li class="step-wizard-item">
                                <span class="progress-count">
                                    <button type="button" <?= (($steps[ ($step - 1) ]['success'] ?? false) === FALSE && $info['page'] != $firstPage)? "disabled" : "onclick='location.href = `{$pageLink}`'" ?> class="btn btn-sm text-dark btn-outline-primary <?= ($currentPage == $info['page'])? "active" : "" ?> <?= ($info['success'])? "done" : "" ?>" id="<?= $info['page'] ?>-tab" aria-selected="false">
                                        <?= $step ?>
                                    </button>
                                </span>
                              <span class="progress-label"><?= $info['title'] ?></span>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="panel-body rgl">
                <?php if($currIndex !== FALSE) : ?>
                    <?php if(file_exists(__DIR__ . "/regol/{$currentPage}.php")) : ?>
                        <?php require (__DIR__ . "/regol/{$currentPage}.php"); ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="/assets/css/floatingbutton.css">
<div class="floatbtn_scope_floatbtn">
    <!-- ================= VARIAN #1: Single Floating WhatsApp Button ================= -->
    <a class="floating-whatsapp_floatbtn" href="https://wa.me/6281119771818" target="_blank" rel="noopener" aria-label="Chat via WhatsApp">
        <span class="label_floatbtn">Chat WhatsApp</span>
        <!-- Ikon WhatsApp (SVG) -->
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path fill="currentColor" d="M20.52 3.48A11.88 11.88 0 0 0 12.02 0C5.4 0 .06 5.34.06 11.95c0 2.1.55 4.14 1.6 5.95L0 24l6.27-1.62a11.9 11.9 0 0 0 5.75 1.47h.01c6.61 0 11.95-5.34 11.95-11.95c0-3.19-1.24-6.18-3.46-8.42ZM12.03 21.4h-.01a9.4 9.4 0 0 1-4.79-1.31l-.34-.2l-3.72.96l.99-3.63l-.22-.37a9.4 9.4 0 0 1-1.45-4.94c0-5.19 4.22-9.41 9.41-9.41c2.51 0 4.87.98 6.64 2.75a9.36 9.36 0 0 1 2.76 6.66c0 5.19-4.23 9.4-9.42 9.4Zm5.38-7.04c-.29-.14-1.71-.84-1.97-.93c-.26-.1-.45-.14-.64.14c-.19.29-.73.93-.89 1.12c-.16.19-.33.21-.62.07c-.29-.14-1.22-.45-2.31-1.44c-.85-.76-1.42-1.7-1.58-1.98c-.16-.29-.02-.45.12-.59c.12-.12.29-.33.43-.5c.14-.17.19-.29.29-.48c.1-.19.05-.36-.02-.5c-.07-.14-.64-1.54-.88-2.1c-.23-.56-.47-.48-.64-.48c-.17 0-.36-.02-.55-.02c-.19 0-.5.07-.76.36c-.26.29-1.01.99-1.01 2.41c0 1.41 1.03 2.77 1.17 2.96c.14.19 2.04 3.11 4.94 4.22c.69.3 1.23.48 1.65.62c.69.22 1.32.19 1.81.12c.55-.08 1.71-.69 1.96-1.36c.24-.67.24-1.24.17-1.36c-.07-.12-.26-.19-.55-.33Z"/>
        </svg>
    </a>
</div>
<script src="/assets/js/floatingbutton.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        setInterval(() => {
            let now = new Date();
            let year = now.getFullYear();
            let month = String(now.getMonth() + 1).padStart(2, '0');
            let day = String(now.getDate()).padStart(2, '0');
            let hours = String(now.getHours()).padStart(2, '0');
            let minutes = String(now.getMinutes()).padStart(2, '0');
            let seconds = String(now.getSeconds()).padStart(2, '0');
            
            let formattedDateTime = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;

            $('.realtime-date').val(formattedDateTime);
        }, 1000)

        $('.amount-formatter').on('keyup', function(evt) {
            $(evt.currentTarget).val( formatter( $(evt.currentTarget).val() ) )
        })

        $('ul.step-wizard-list').animate({
            scrollLeft: $('.progress-count button.active').offset().left - 300
        }, 1000)

        $("div.rgl").find(`input:not([required]):not([type="hidden"]):not([skip]), select:not([required])`).each((i, e) => {
            if($(e).is(':visible')){
                switch ($(e).parent().prop('tagName')) {
                    case 'TD':
                        $(e).parents('tr').find('td:first-child').html(`${$(e).parents('tr').find('td:first-child').html()} <span>(Opsional)</span>`);
                        break;
                    case 'DIV':
                        switch ($(e).parent().find('label').length) {
                            case 1:
                                $(e).parent().find('label').html(`${$(e).parent().find('label').html()} <span>(Opsional)</span>`);
                                break;
                            case 0:
                                $(e).parents().find(`label[for="${$(e).attr('id')}"]`).html(`${$(e).parents().find(`label[for="${$(e).attr('id')}"]`).html()} <span>(Opsional)</span>`);
                                
                                break;
                        
                            default: false; break;
                        }
                        break;
                
                    default: false; break;
                }
            }
        });
        
        $(`select`).on('change', function(e){
            let vlnnyaElm = $(this).find(':selected');
            if(vlnnyaElm.attr('data-vlnny')){
                Swal.fire({
                    title: "Pilihan Lainnya",
                    icon: "info",
                    input: "text",
                    inputLabel: `Silahkan isi data input pada "${vlnnyaElm.attr('data-vlnny')}"`,
                    customClass: {
                        inputLabel: 'swal2-title fs-6',
                        inputValue: 'swal2-title'
                    },
                    allowEscapeKey: false,
                    showCancelButton: false,
                    allowOutsideClick : false,
                    reverseButtons: true,
                    inputValidator: (value) => {
                        if (!value) {
                            return "You need to write something!";
                        }
                    }
                }).then((value) => {
                    if(value.isConfirmed) {
                        vlnnyaElm.val(value.value);
                        vlnnyaElm.html(`Lainnya('${value.value}')`);
                    }
                });
            }
        });
    });

    function formatter(angka, prefix = null){
        var number_string = angka.replace(/[^\.\d]/g, '').toString(),
        split   		= number_string.split('.'),
        sisa     		= split[0].length % 3,
        rupiah     		= split[0].substr(0, sisa),
        ribuan     		= split[0].substr(sisa).match(/\d{3}/gi);
        // tambahkan titik jika yang di input sudah menjadi angka ribuan
        if(ribuan){
            separator = sisa ? ',' : '';
            rupiah += separator + ribuan.join(',');
        }

        rupiah = split[1] != undefined ? rupiah + '.' + split[1] : rupiah;
        return prefix == undefined ? rupiah : (rupiah ? prefix + rupiah : '');
    }
</script>