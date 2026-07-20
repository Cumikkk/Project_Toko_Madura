<?php
    $ACC_TYPE1  = 1;
    $typecont = 'Formulir Nomor 107.PBK.04.2 ';
    
?>
<style>
    strong {
        text-decoration: underline;
    }
</style>

<div class="row">
    <div class="col-md-9 mx-auto">
        <form method="post" id="form-dokumen-resiko">
            <input type="hidden" name="csrf_token" value="<?= uniqid(); ?>">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="text-center">
                                <h5>
                                    Formulir Dokumen Resiko untuk Transaksi <span class="text-capitalize">Kontrak <span class="title">Derivatif Dalam Sistem Perdagangan Alternatif.</span></span>
                                </h5>
                            </div>
                            <hr>
                            <div class="text-center fw-bold">DOKUMEN PEMBERITAHUAN ADANYA RISIKO<br>YANG HARUS DISAMPAIKAN OLEH PIALANG BERJANGKA<br>
                            UNTUK TRANSAKSI KONTRAK <span class="title text-uppercase">DERIVATIF DALAM SISTEM PERDAGANGAN ALTERNATIF</span></div>
                            <div class="mt-3">
                                <p class="text-justify">Dokumen Pemberitahuan Adanya Risiko ini disampaikan kepada Anda sesuai
                                dengan Pasal 50 ayat (2) Undang-Undang Nomor 32 Tahun 1997 tentang
                                Perdagangan Berjangka Komoditi sebagaimana telah diubah dengan Undang-Undang
                                Nomor 10 Tahun 2011 tentang Perubahan Atas Undang-Undang Nomor 32 Tahun 1997
                                Tentang Perdagangan Berjangka Komoditi.</p>

                                <p class="text-justify">Maksud dokumen ini adalah memberitahukan bahwa kemungkinan kerugian atau
                                keuntungan dalam perdagangan <span class="text-capitalize">Kontrak <span class="title">Derivatif Dalam Sistem Perdagangan Alternatif</span></span> bisa mencapai jumlah yang sangat
                                besar. Oleh karena itu, Anda harus berhati-hati dalam memutuskan untuk melakukan
                                transaksi, apakah kondisi keuangan Anda mencukupi.</p>
                                <ol id="docresk">
                                    <li class="text-justify mb-3">
                                        <strong>Perdagangan Kontrak Derivatif Dalam Sistem Perdagangan Alternatif belum tentu layak bagi semua investor.</strong>
                                        <br>
                                        <p>Anda dapat menderita kerugian dalam jumlah besar dan dalam jangka waktu
                                        singkat. Jumlah kerugian uang dimungkinkan dapat melebihi jumlah uang yang
                                        pertama kali Anda setor (Margin awal) ke Pialang Berjangka Anda.</p>
                                        <p>Anda mungkin menderita kerugian seluruh Margin dan Margin tambahan yang
                                        ditempatkan pada Pialang Berjangka untuk mempertahankan posisi Kontrak
                                        Derivatif Dalam Sistem Perdagangan Alternatif Anda.</p>
                                        <p>Hal ini disebabkan Perdagangan Berjangka sangat dipengaruhi oleh mekanisme
                                        leverage, dimana dengan jumlah investasi dalam bentuk yang relatif kecil dapat
                                        digunakan untuk membuka posisi dengan aset yang bernilai jauh lebih tinggi.
                                        Apabila Anda tidak siap dengan risiko seperti ini, sebaiknya Anda tidak melakukan
                                        perdagangan Kontrak Derivatif Dalam Sistem Perdagangan Alternatif.</p>
                                        <div class="text-left">
                                            <label style="cursor:pointer;">
                                                <input type="checkbox" <?php echo ((retnull("ACC_F_RESK", 0))) ? 'checked' : NULL ?> name="box[]" class="form-check-input ck-item" style="border: 1.5px solid black !important;" value="YA" required/> Saya sudah membaca dan memahami
                                            </label>
                                        </div> 

                                    </li>
                                    <li class="text-justify mb-3">
                                        <strong>Perdagangan Kontrak Derivatif Dalam Sistem Perdagangan Alternatif mempunyai risiko dan mempunyai
                                        kemungkinan kerugian yang tidak terbatas yang jauh lebih besar dari jumlah
                                        uang yang disetor (Margin) ke Pialang Berjangka.</strong>
                                        Kontrak Derivatif Dalam Sistem Perdagangan Alternatif sama
                                        dengan produk keuangan lainnya yang mempunyai risiko tinggi, Anda sebaiknya
                                        tidak menaruh risiko terhadap dana yang Anda tidak siap untuk menderita rugi,
                                        seperti tabungan pensiun, dana kesehatan atau dana untuk keadaan darurat,
                                        dana yang disediakan untuk pendidikan atau kepemilikan rumah, dana yang
                                        diperoleh dari pinjaman pendidikan atau gadai, atau dana yang digunakan untuk
                                        memenuhi kebutuhan sehari-hari.
                                        <div class="text-left">
                                            <label style="cursor:pointer;">
                                                <input type="checkbox" <?php echo ((retnull("ACC_F_RESK", 0))) ? 'checked' : NULL ?> name="box[]" class="form-check-input ck-item" style="border: 1.5px solid black !important;" value="YA" required /> Saya sudah membaca dan memahami
                                            </label>
                                        </div> 
                                    </li>
                                    <li class="text-justify mb-3">
                                        <strong>Berhati-hatilah terhadap pernyataan bahwa Anda pasti mendapatkan
                                        keuntungan besar dari perdagangan Kontrak Derivatif Dalam Sistem Perdagangan Alternatif.</strong> Meskipun perdagangan
                                        Kontrak Derivatif Dalam Sistem Perdagangan Alternatif dapat memberikan keuntungan yang besar dan cepat, namun
                                        hal tersebut tidak pasti, bahkan dapat menimbulkan kerugian yang besar dan
                                        cepat juga. Seperti produk keuangan lainnya, tidak ada yang dinamakan "pasti
                                        untung".
                                        <div class="text-left">
                                            <label style="cursor:pointer;">
                                                <input type="checkbox" <?php echo ((retnull("ACC_F_RESK", 0))) ? 'checked' : NULL ?> name="box[]" class="form-check-input ck-item" style="border: 1.5px solid black !important;" value="YA" required /> Saya sudah membaca dan memahami
                                            </label>
                                        </div> 
                                    </li>
                                    <li class="text-justify mb-3">
                                        <strong>Disebabkan adanya mekanisme leverage dan sifat dari transaksi 
                                        Kontrak Derivatif Dalam Sistem Perdagangan Alternatif, Anda dapat 
                                        merasakan dampak bahwa Anda menderita kerugian dalam waktu 
                                        cepat.</strong> Keuntungan maupun kerugian dalam transaksi akan langsung 
                                        dikredit atau didebet ke rekening Anda, paling lambat secara harian. 
                                        Apabila pergerakan di pasar terhadap Kontrak Derivatif Dalam Sistem Perdagangan Alternatif 
                                        menurunkan nilai posisi Anda dalam Kontrak 
                                        Derivatif Dalam Sistem Perdagangan Alternatif, <i>dengan kata lain 
                                        berlawanan dengan posisi yang Anda ambil</i>, Anda diwajibkan untuk 
                                        menambah dana untuk pemenuhan kewajiban Margin ke perusahaan 
                                        Pialang Berjangka. Apabila rekening Anda berada dibawah minimum 
                                        Margin yang telah ditetapkan Lembaga Kliring Berjangka atau Pialang 
                                        Berjangka, maka posisi Anda dapat dilikuidasi pada saat rugi, dan 
                                        Anda wajib menyelesaikan defisit (jika ada) dalam rekening Anda.
                                        <div class="text-left">
                                            <label style="cursor:pointer;">
                                                <input type="checkbox" <?php echo ((retnull("ACC_F_RESK", 0))) ? 'checked' : NULL ?> name="box[]" class="form-check-input ck-item" style="border: 1.5px solid black !important;" value="YA" required /> Saya sudah membaca dan memahami
                                            </label>
                                        </div> 
                                    </li>
                                    <li class="text-justify mb-3">
                                        <strong>Pada saat pasar dalam keadaan tertentu, Anda mungkin akan sulit 
                                        atau tidak mungkin melikuidasi posisi.</strong> Pada umumnya Anda harus 
                                        melakukan transaksi mengambil posisi yang berlawanan dengan 
                                        maksud melikuidasi posisi (<i>offset</i>) jika ingin melikuidasi posisi dalam 
                                        Kontrak Derivatif Dalam Sistem Perdagangan Alternatif. Apabila Anda 
                                        tidak dapat melikuidasi posisi Kontrak Derivatif Dalam Sistem Perdagangan Alternatif, 
                                        Anda tidak dapat merealisasikan keuntungan 
                                        pada nilai posisi tersebut atau mencegah kerugian yang lebih tinggi. 
                                        Kemungkinan tidak dapat melikuidasi dapat terjadi, antara lain: jika 
                                        perdagangan berhenti dikarenakan aktivitas perdagangan yang tidak 
                                        lazim pada Kontrak Derivatif atau subjek Kontrak Derivatif, atau terjadi kerusakan sistem pada Pialang Berjangka Peserta Sistem 
                                        Perdagangan Alternatif atau Pedagang Berjangka Penyelenggara 
                                        Sistem Perdagangan Alternatif. Bahkan apabila Anda dapat 
                                        melikuidasi posisi tersebut, Anda mungkin terpaksa melakukannya 
                                        pada harga yang menimbulkan kerugian besar.
                                        <div class="text-left">
                                            <label style="cursor:pointer;">
                                                <input type="checkbox" <?php echo ((retnull("ACC_F_RESK", 0))) ? 'checked' : NULL ?> name="box[]" class="form-check-input ck-item" style="border: 1.5px solid black !important;" value="YA" required /> Saya sudah membaca dan memahami
                                            </label>
                                        </div> 
                                    </li>
                                    <li class="text-justify mb-3">
                                        <strong>Pada saat pasar dalam keadaan tertentu, Anda mungkin akan sulit atau tidak
                                        mungkin mengelola risiko atas posisi terbuka Kontrak Derivatif Dalam Sistem Perdagangan Alternatif dengan cara
                                        membuka posisi dengan nilai yang sama namun dengan posisi yang
                                        berlawanan dalam kontrak bulan yang berbeda, dalam pasar yang berbeda atau
                                        dalam “subjek Kontrak Derivatif Dalam Sistem Perdagangan Alternatif” yang berbeda.</strong> Kemungkinan untuk tidak
                                        dapat mengambil posisi dalam rangka membatasi risiko yang timbul, contohnya:
                                        jika perdagangan dihentikan pada pasar yang berbeda disebabkan aktivitas
                                        perdagangan yang tidak lazim pada Kontrak Derivatif dalam Sistem Perdagangan Alternatif atau “Kontrak Derivatif dalam Sistem Perdagangan Alternatif”.
                                        <div class="text-left">
                                            <label style="cursor:pointer;">
                                                <input type="checkbox" <?php echo ((retnull("ACC_F_RESK", 0))) ? 'checked' : NULL ?> name="box[]" class="form-check-input ck-item" style="border: 1.5px solid black !important;" value="YA" required /> Saya sudah membaca dan memahami
                                            </label>
                                        </div> 
                                    </li>
                                    <li class="text-justify mb-3">
                                        <strong>Anda dapat menderita kerugian yang disebabkan kegagalan sistem informasi.</strong> 
                                        Sebagaimana yang terjadi pada setiap transaksi keuangan, 
                                        Anda dapat menderita kerugian jika amanat untuk melaksanakan transaksi Kontrak Derivatif Dalam Sistem Perdagangan Alternatif tidak dapat dilakukan 
                                        karena kegagalan sistem informasi di Bursa Berjangka, Pedagang Berjangka Penyelenggara Sistem Perdagangan Alternatif, 
                                        maupun sistem di Pialang Berjangka Peserta Sistem Perdagangan Alternatif yang mengelola posisi Anda. 
                                        Kerugian Anda akan semakin besar jika Pialang Berjangka yang mengelola posisi Anda tidak memiliki sistem informasi cadangan atau prosedur yang layak.
                                        <div class="text-left">
                                            <label style="cursor:pointer;">
                                                <input type="checkbox" <?php echo ((retnull("ACC_F_RESK", 0))) ? 'checked' : NULL ?> name="box[]" class="form-check-input ck-item" style="border: 1.5px solid black !important;" value="YA" required /> Saya sudah membaca dan memahami
                                            </label>
                                        </div> 
                                    </li>
                                    <li class="text-justify mb-3">
                                        <strong>Semua Kontrak Derivatif Dalam Sistem Perdagangan Alternatif mempunyai risiko, dan tidak ada strategi
                                        berdagang yang dapat menjamin untuk menghilangkan risiko tersebut.</strong> Strategi
                                        dengan menggunakan kombinasi posisi seperti spread, dapat sama berisiko seperti
                                        posisi long atau short. Melakukan Perdagangan Berjangka memerlukan
                                        pengetahuan mengenai Kontrak Derivatif Dalam Sistem Perdagangan Alternatif dan pasar berjangka.
                                        <div class="text-left">
                                            <label style="cursor:pointer;">
                                                <input type="checkbox" <?php echo ((retnull("ACC_F_RESK", 0))) ? 'checked' : NULL ?> name="box[]" class="form-check-input ck-item" style="border: 1.5px solid black !important;" value="YA" required /> Saya sudah membaca dan memahami
                                            </label>
                                        </div> 
                                    </li>
                                    <li class="text-justify mb-3">
                                        <strong>Strategi perdagangan harian dalam Kontrak Derivatif Dalam Sistem Perdagangan Alternatif dan produk lainnya
                                        memiliki risiko khusus.</strong> Seperti pada produk keuangan lainnya, pihak yang ingin
                                        membeli atau menjual Kontrak Derivatif Dalam Sistem Perdagangan Alternatif yang sama dalam satu hari untuk
                                        mendapat keuntungan dari perubahan harga pada hari tersebut (“<i>day traders</i>”)
                                        akan memiliki beberapa risiko tertentu antara lain jumlah komisi yang besar,
                                        risiko terkena efek pengungkit (“<i>exposure to leverage</i>”), dan persaingan dengan
                                        pedagang profesional. Anda harus mengerti risiko tersebut dan memiliki
                                        pengalaman yang memadai sebelum melakukan perdagangan harian (“<i>day
                                        trading</i>”).
                                        <div class="text-left">
                                            <label style="cursor:pointer;">
                                                <input type="checkbox" <?php echo ((retnull("ACC_F_RESK", 0))) ? 'checked' : NULL ?> name="box[]" class="form-check-input ck-item" style="border: 1.5px solid black !important;" value="YA" required /> Saya sudah membaca dan memahami
                                            </label>
                                        </div> 
                                    </li>
                                    <li class="text-justify mb-3">
                                        <strong>Menetapkan amanat bersyarat, Kontrak Derivatif Dalam Sistem Perdagangan Alternatif dilikuidasi pada
                                        keadaan tertentu untuk membatasi rugi (<i>stop loss</i>), mungkin tidak akan dapat
                                        membatasi kerugian Anda sampai jumlah tertentu saja.</strong> Amanat bersyarat
                                        tersebut mungkin tidak dapat dilaksanakan karena terjadi kondisi pasar yang
                                        tidak memungkinkan melikuidasi Kontrak Derivatif Dalam Sistem Perdagangan Alternatif.
                                        <div class="text-left">
                                            <label style="cursor:pointer;">
                                                <input type="checkbox" <?php echo ((retnull("ACC_F_RESK", 0))) ? 'checked' : NULL ?> name="box[]" class="form-check-input ck-item" style="border: 1.5px solid black !important;" value="YA" required /> Saya sudah membaca dan memahami
                                            </label>
                                        </div> 
                                    </li>
                                    <li class="text-justify mb-3">
                                        <strong>Anda harus membaca dengan seksama dan memahami Perjanjian Pemberian
                                        Amanat Nasabah dengan Pialang Berjangka Anda sebelum melakukan transaksi
                                        Kontrak Derivatif Dalam Sistem Perdagangan Alternatif.</strong>
                                        <div class="text-left">
                                            <label style="cursor:pointer;">
                                                <input type="checkbox" <?php echo ((retnull("ACC_F_RESK", 0))) ? 'checked' : NULL ?> name="box[]" class="form-check-input ck-item" style="border: 1.5px solid black !important;" value="YA" required /> Saya sudah membaca dan memahami
                                            </label>
                                        </div> 
                                    </li>
                                    <li class="text-justify mb-3">
                                        <strong>Pernyataan singkat ini tidak dapat memuat secara rinci seluruh risiko atau
                                        aspek penting lainnya tentang Perdagangan Berjangka. Oleh karena itu Anda
                                        harus mempelajari kegiatan Perdagangan Berjangka secara cermat sebelum
                                        memutuskan melakukan transaksi.</strong>
                                        <div class="text-left">
                                            <label style="cursor:pointer;">
                                                <input type="checkbox" <?php echo ((retnull("ACC_F_RESK", 0))) ? 'checked' : NULL ?> name="box[]" class="form-check-input ck-item" style="border: 1.5px solid black !important;" value="YA" required /> Saya sudah membaca dan memahami
                                            </label>
                                        </div> 
                                    </li>
                                    <li class="text-justify mb-3">
                                        <strong>Dokumen Pemberitahuan Adanya Risiko (<i>Risk Disclosure</i>) ini dibuat dalam
                                        Bahasa Indonesia.</strong>
                                        <div class="text-left">
                                            <label style="cursor:pointer;">
                                                <input type="checkbox" <?php echo ((retnull("ACC_F_RESK", 0))) ? 'checked' : NULL ?> name="box[]" class="form-check-input ck-item" style="border: 1.5px solid black !important;" value="YA" required /> Saya sudah membaca dan memahami
                                            </label>
                                        </div> 
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <label style="cursor:pointer;">
                            <input type="checkbox" id="check_all" class="form-check-input me-1" style="border: 1.5px solid black !important;" value="YA" /> 
                            Setujui Semua
                        </label>

                        <div class="col-12 mt-3 text-center">
                            <h6>PERNYATAAN MENERIMA PEMBERITAHUAN ADANYA RISIKO</h6>
                            <p>
                                Dengan mengisi kolom “YA” di bawah, saya menyatakan bahwa saya
                                telah menerima
                                “DOKUMEN PEMBERITAHUAN ADANYA RISIKO”
                                mengerti dan menyetujui isinya.
                            </p>
                        </div>
                        <div class="col-6 mt-3">
                            Pernyataan menerima/tidak<br>
                            <input type="radio" name="aggree" value="Ya" class="form-check-input radio_css" style="margin-top: 10px;" required <?= !empty($realAccount['ACC_F_RESK']) ? "checked" : "" ?>>
                            <label style="top: 0.5rem;position: relative;margin-bottom: 0;vertical-align: top;margin-right:1.5rem;">Ya</label>
                            <input type="radio" name="aggree" value="Tidak" class="form-check-input radio_css" style="margin-top: 10px;" required>
                            <label style="top: 0.5rem;position: relative;margin-bottom: 0;vertical-align: top;margin-right:1.5rem;">Tidak</label>
                        </div>
                        <div class="col-6 mt-3">
                            <div class="text-cemter">Menerima pada Tanggal</div>
                            <input type="text" name="agg_date" readonly required value="<?= retnull("ACC_F_RESK_DATE", date('Y-m-d H:i:s')) ?>" class="form-control text-center mb-3 <?= empty($realAccount['ACC_F_RESK_DATE'])? "realtime-date" : "" ?>">
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
    </div>
</div>

<script type="text/javascript">
    $(function () {
        const $checkAll = $('#check_all');
        const itemSelector = '.ck-item:not(:disabled)';

        function syncMaster() {
            const $items   = $(itemSelector);
            const total    = $items.length;
            const checked  = $items.filter(':checked').length;

            $checkAll.prop('checked', checked === total);
            $checkAll.prop('indeterminate', checked > 0 && checked < total);
        }

        $checkAll.on('change', function () {
            $(itemSelector).prop('checked', this.checked);
            syncMaster();
        });

        $(document).on('change', itemSelector, syncMaster);

        syncMaster();
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {

        $('#form-dokumen-resiko').on('submit', function(event) {
            event.preventDefault();

            Swal.fire({
                text: "Please wait...",
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            })
            
            $.ajax({
                url: "/ajax/regol/formulirDokumenResiko",
                type: "POST",
                dataType: "json",
                data: new FormData(this),
                processData: false,
                contentType: false,
                cache: false
            }).done(function(resp) {
                if(!resp.success) {
                    Swal.fire(resp.alert);
                    return;
                }
                
                location.href = resp.redirect
            })
        })
    })
</script>