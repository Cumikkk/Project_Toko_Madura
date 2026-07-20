<div class="table-repsonsive">
    <table class="table table-hover table-striped">
        <tbody>
            <tr>
                <td>1.</td>
                <td>FORMULIR PBK. CDD. 01 </td>
                <td>
                    Profile Perusahaan <br>
                    <small><?php echo $web_name_full ?> adalah Perusahaan Pialang yang bergerak di bidang perdagangan kontrak derivatif komoditi, Indeks Saham dan Foreign Exchange.</small>
                </td>
                <td><a target="_blank" href="/export/profile-perusahaan?acc=<?php echo $id_acc; ?>" class="btn btn-primary d-flex align-items-center"><i class="fa fa-eye"></i>&nbsp;View</a></td>
            </tr>
            <tr>
                <td>2.</td>
                <td>FORMULIR PBK. CDD. 02</td>
                <td>
                    Pernyataan Telah Melakukan Simulasi perdagangan berjangka komoditi<br>
                    <small>Calon Nasabah diwajibkan untuk memiliki demo account <?php echo $web_name_full ?> sebagai sarana untuk melakukan simulasi transaksi di <?php echo $web_name_full ?>.</small>
                </td>
                <td><a target="_blank" href="/export/pernyataan-simulasi?acc=<?php echo $id_acc; ?>" class="btn btn-primary d-flex align-items-center"><i class="fa fa-eye"></i>&nbsp;View</a></td>
            </tr>
            <tr>
                <td>3.</td>
                <td>FORMULIR PBK. CDD. 03</td>
                <td>
                    Pernyataan telah berpengalaman melaksanakan transaksi perdagangan berjangka komoditi<br>
                    <small>Dalam hal calon nasabah telah berpengalaman dalam melaksanakan transaksi dalam Perdagangan Berjangka Komoditi, Nasabah memberikan pernyataan dengan Surat Pernyataan Telah Berpengalaman Melaksanakan Transaksi Perdagangan Berjangka Komoditi.</small>
                </td>
                <td><a target="_blank" href="/export/pernyataan-pengalaman?acc=<?php echo $id_acc; ?>" class="btn btn-primary d-flex align-items-center"><i class="fa fa-eye"></i>&nbsp;View</a></td>
            </tr>
            <tr>
                <td>4.</td>
                <td>FORMULIR PBK. CDD. 04</td>
                <td>
                    Aplikasi Pembukaan Rekening Transaksi secara Elektronik On-line<br>
                    <small>Seluruh data isian dalam Aplikasi Pembukaan Rekening Transaksi Secara Elektronik On-line Dalam Sistem Perdagangan Alternatif wajib di isi sendiri oleh Nasabah, dan Nasabah bertanggung jawab atas kebenaran informasi yang diberikan dalam mengisi dokumen ini.</small>
                </td>
                <td><a target="_blank" href="/export/aplikasi-pembukaan-rekening?acc=<?php echo $id_acc; ?>" class="btn btn-primary d-flex align-items-center"><i class="fa fa-eye"></i>&nbsp;View</a></td>
            </tr>
            <tr>
                <td>5.</td>
                <td>FORMULIR PBK. CDD. 05</td>
                <td>
                    Document pemberitahuan adanya resiko<br>
                    <small>Maksud dokumen ini adalah memberitahukan bahwa kemungkinan kerugian atau keuntungan dalam perdagangan Kontrak Berjangka bisa mencapai jumlah yang sangat besar. Oleh karena itu, Anda harus berhati-hati dalam memutuskan untuk melakukan transaksi, apakah kondisi keuangan Anda mencukupi.</small>
                </td>
                <td><a target="_blank" href="/export/pemberitahuan-adanya-risiko?acc=<?php echo $id_acc; ?>" class="btn btn-primary d-flex align-items-center"><i class="fa fa-eye"></i>&nbsp;View</a></td>
            </tr>
            <tr>
                <td>6.</td>
                <td>FORMULIR PBK. CDD. 06</td>
                <td>
                    Perjanjian pemberian amanat secara elektronik on-line untuk transaksi kontrak berjangka
                    <small>Perjanjian kontrak berjangka dan sepakat untuk mengadakan Perjanjian Pemberian Amanat untuk melakukan transaksi penjualan maupun pembelian Kontrak</small>
                </td>
                <td><a target="_blank" href="/export/perjanjian-pemberian-amanat?acc=<?php echo $id_acc; ?>" class="btn btn-primary d-flex align-items-center"><i class="fa fa-eye"></i>&nbsp;View</a></td>
            </tr>
            <tr>
                <td>7.</td>
                <td>FORMULIR PBK. CDD. 07</td>
                <td>
                    Peraturan Perdagangan (Trading Rules)<br>
                    <small>Peraturan Perdagangan (Trading Rules) dalam siste, aplikasi penerimaan nasabah secara elektronik On-Line</small>
                </td>
                <td><a target="_blank" href="<?php echo App\Models\Regol::urlTradingRule($progressAccount['RTYPE_FILE']); ?>" class="btn btn-primary d-flex align-items-center"><i class="fa fa-eye"></i>&nbsp;View</a></td>
            </tr>
            <tr>
                <td>8.</td>
                <td>FORMULIR PBK. CDD. 08</td>
                <td>
                    Pernyataan bertanggung jawab<br>
                    <small>Pernyataan bertanggung jawab atas kode akses transaksi nasabah(Personal Access Password)</small>
                </td>
                <td><a target="_blank" href="/export/personal-access-password?acc=<?php echo $id_acc; ?>" class="btn btn-primary d-flex align-items-center"><i class="fa fa-eye"></i>&nbsp;View</a></td>
            </tr>
            <tr>
                <td>9.</td>
                <td>FORMULIR PBK. CDD. 09</td>
                <td>
                    Formulir Penyataan Dana Nasabah<br>
                    <small>Proses Penerimaan Nasabah Secara Elektronik Online</small>
                </td>
                <td><a target="_blank" href="/export/pernyataan-dana-nasabah?acc=<?php echo $id_acc; ?>" class="btn btn-primary d-flex align-items-center"><i class="fa fa-eye"></i>&nbsp;View</a></td>
            </tr>
            <tr>
                <td>10.</td>
                <td>WP CONFIRM</td>
                <td>
                    Surat Pernyataan<br>
                    <small>Proses Penerimaan Nasabah Secara Elektronik Online</small>
                </td>
                <td><a target="_blank" href="/export/surat-pernyataan?acc=<?php echo $id_acc; ?>" class="btn btn-primary d-flex align-items-center"><i class="fa fa-eye"></i>&nbsp;View</a></td>
            </tr>
            <tr>
                <td>11.</td>
                <td>KELENGKAPAN FORMULIR</td>
                <td>
                    Kelengkapan Formulir<br>
                    <small>Proses Penerimaan Nasabah Secara Elektronik Online</small>
                </td>
                <td><a target="_blank" href="/export/kelengkapan-formulir?acc=<?php echo $id_acc; ?>" class="btn btn-primary d-flex align-items-center"><i class="fa fa-eye"></i>&nbsp;View</a></td>
            </tr>
        </tbody>
    </table>
</div>