<!DOCTYPE html>
<html>
    <head>
        <?php require_once(__DIR__  . "/../style.php"); ?>
        <style>
            ul.sub-btr {
                counter-reset: item;   
                list-style: none;
            }
            ul.sub-btr > li::before {
                display: inline-block;
                content: "(" counter(item) ")";
                counter-increment: item;
                /* width: 2em; */
                margin-left: -2em;
                margin-top: 5%;
            }
            ul.sub-btr-rum {
                counter-reset: itm;   
                list-style: none;
            }
            ul.sub-btr-rum > li::before {
                display: inline-block;
                content: counter(itm, lower-roman) ").";
                counter-increment: itm;
                /* width: 2em; */
                margin-left: -2em;
                margin-top: 5%;
            }
        </style>
    </head>
    <body>
        <?php require_once(__DIR__  . "/../header.php"); ?><hr>

        <div class="section">
            <div>
                <?php
                    if(file_exists($file = CRM_ROOT.App\Models\Regol::urlTradingRule(str_replace('pdf', 'html', $realAccount['RTYPE_FILE'])))){
                        include($file);
                    }
                ?>
            </div>
            
            <div style="margin-top:25px;text-align:center;">
                <p class="mt-4 mb-0">Biaya yang dikenakan setiap pelaksanaan transaksi: <b>$<?= $realAccount['RTYPE_KOMISI'] ?></b></p>
                <p>
                    Dengan mengisi kolom “YA” di bawah ini, saya menyatakan bahwa saya 
                    telah membaca tentang PERATURAN PERDAGANGAN (TRADING  RULES), 
                    mengerti dan menerima ketentuan dalam bertransaksi 
                </p>
            </div>
            <div style="text-align:center;margin-top:10px;margin-left:25%">
                <table>
                    <tr>
                        <td>Pernyataan menerima/tidak </td>
                        <td style="vertical-align: top;"><div style="margin:0px 5px;">:</div></td>
                        <td><strong>YA</strong></td>
                    </tr>
                    <tr>
                        <td>Menerima pada Tanggal</td>
                        <td style="vertical-align: top;"><div style="margin:0px 5px;">:</div></td>
                        <td><strong><?= date('Y-m-d H:i:s', strtotime($realAccount["ACC_F_TRDNGRULE_DATE"])) ?></strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </body>
</html>