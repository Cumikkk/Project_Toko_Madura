<?php
    use App\Models\Dpwd;
    use App\Models\Account;
    use App\Models\Helper;
    use App\Models\CompanyProfile;
    $data = Helper::getSafeInput($_GET);
    $companyProfile = CompanyProfile::profilePerusahaan();
    $mainOffice = CompanyProfile::getMainOffice();

    $realAccount = Account::realAccountDetail($data["acc"]);
    $realAccount = array_merge((Account::accoundCondition($realAccount["ID_ACC"]) ?? []), $realAccount);

    $depositData = Dpwd::findByRaccId($realAccount["ID_ACC"]);

    $amountIDR = ($depositData["DPWD_CURR_FROM"] == "IDR") ? $depositData['DPWD_AMOUNT_SOURCE'] : $depositData['DPWD_AMOUNT'];
    $amountUSD = ($depositData["DPWD_CURR_FROM"] == "USD") ? $depositData['DPWD_AMOUNT_SOURCE'] : $depositData['DPWD_AMOUNT'];
    $rate = $depositData['DPWD_RATE'];

    /** if IDR to IDR */
    // if($depositData['DPWD_CURR_FROM'] == "IDR") {
    //     $amountUSD = 0;
    //     $rate = 0;
    //     $convert = Account::accountConvertation([
    //         'account_id' => $realAccount['ID_ACC'],
    //         'amount' => $amountIDR,
    //         'from' => "IDR",
    //         'to' => "USD"
    //     ]);

    //     if(is_array($convert)) {
    //         $amountUSD = ($amountIDR / $convert['rate']);
    //         $rate = $convert['rate'];
    //     }
    // }
?>

<!DOCTYPE html>
<html>
    <head>
        <?php require_once(__DIR__  . "/style.php"); ?>
        <style>
            @page {
                margin-left: 50px;
                margin-right: 50px;
            }
        </style>
    </head>
    <body>
        <?php require_once(__DIR__  . "/header.php"); ?><hr>

        <div class="section" style="padding: 0px;">
            <h4 class="text-center" style="margin: 0px;">NOTA PENERIMAAN MARGIN</h4>
            <table class="table no-border" style="margin-top: 20px; font-size: 15px;">
                <tbody>
                    <tr>
                        <td width="30%" class="v-align-top">Nomor Akun</td>
                        <td width="3%" class="v-align-top">:</td>
                        <td class="v-align-top"><?= $realAccount['ACCCND_LOGIN'] ?></td>
                    </tr>
                    <tr>
                        <td width="30%" class="v-align-top">Nama Nasabah</td>
                        <td width="3%" class="v-align-top">:</td>
                        <td class="v-align-top"><?= $realAccount['ACC_FULLNAME'] ?></td>
                    </tr>
                    <tr>
                        <td width="30%" class="v-align-top">Jumlah Margin Masuk</td>
                        <td width="3%" class="v-align-top">:</td>
                        <td class="v-align-top" style="border-bottom: 1px solid black !important;">( <?= $realAccount['RTYPE_CURR_SYMBOL'].' '.Helper::formatCurrency((($realAccount["RTYPE_ISFLOATING"]) ? $amountUSD : $amountIDR), 0) ?>)</td>
                    </tr>
                    <tr>
                        <td width="30%" class="v-align-top">Terbilang</td>
                        <td width="3%" class="v-align-top">:</td>
                        <td class="v-align-top" style="border-bottom: 1px solid black !important;"><?= Helper::penyebut(intval((($realAccount["RTYPE_ISFLOATING"]) ? $amountUSD : $amountIDR))) ?> <?= (($realAccount["RTYPE_ISFLOATING"]) ? 'dolar' : 'rupiah') ?></td>
                    </tr>
                </tbody>
            </table>

            <p style="font-size: 15px; margin-left: 5px;">Detail Margin : </p>
            <table class="table no-border">
                <tbody>
                    <tr>
                        <td width="60%">
                            <table class="table" style="font-size: 15px; border: 1px solid black;">
                                <thead>
                                    <tr style="border: 1px solid black;">
                                        <td style="border: 1px solid black !important;" class="text-center">Nominal Margin dalam US Dollar</td>
                                        <td style="border: 1px solid black !important;" class="text-center">Rate <br> (dalam Rupiah)</td>
                                        <td style="border: 1px solid black !important;" class="text-center">Nominal Margin <br> dalam Rupiah</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="border: 1px solid black !important;" width="20%" class="v-align-top" align="right">
                                            <p style="margin-top: 0px; text-align: right;">
                                                <?= Helper::formatCurrency($amountUSD) ?>
                                            </p>
                                        </td>
                                        <td style="border: 1px solid black !important;" width="20%" class="v-align-top" align="right">
                                            <p style="margin-top: 0px; text-align: right;">
                                                <?= Helper::formatCurrency((($realAccount["RTYPE_ISFLOATING"]) ? 'Floating' : $rate)) ?>
                                            </p>    
                                        </td>
                                        <td style="border: 1px solid black !important;" width="20%" class="v-align-top" align="right">
                                            <p style="margin-top: 0px; text-align: right;">
                                                <?= Helper::formatCurrency((($realAccount["RTYPE_ISFLOATING"]) ? 0 : $amountIDR)) ?>
                                            </p>    
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid black !important;" colspan="2" class="text-center">Total</td>
                                        <td style="text-align: right; border: 1px solid black !important"><?= $realAccount['RTYPE_CURR_SYMBOL'].' '.Helper::formatCurrency((($realAccount["RTYPE_ISFLOATING"]) ? $amountUSD : $amountIDR), 0) ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                        <td width="3%"></td>
                        <td class="v-align-top">
                            <p style="margin: 0px;">Mengetahui,</p>
                            <p style="margin: 0px;"><?= $companyProfile["COMPANY_NAME"]; ?></p>
                            <div style="height: 80px;"></div>
                            <p style="margin: 0px; border-bottom: 1px solid black;"></p>
                        </td>
                    </tr>
                </tbody>
            </table>


            <!-- <div style="text-align:center;vertical-align: middle;padding: 10px 0 10px 0;">
                <table border="0" style="border-collapse: collapse;margin-top:5px;" width="100%">
                    <tr>
                        <td width="55%">
                            <div style="margin-bottom:5px;"> :</div>
                            <table border="0" style="border-collapse: collapse;" width="100%">
                                <tr>
                                    <td style="border: 1px solid black;text-align:center;">Nominal Margin dalam US Dollar</td>
                                    <td style="border: 1px solid black;text-align:center;"> 
                                        Rate 
                                        <div style="white-space: nowrap;">(dalam Rupiah)</div>
                                    </td>
                                    <td style="border: 1px solid black;text-align:center;"> Nominal Margin dalam Rupiah</td>
                                </tr>
                                <tr>
                                    <td style="border-left: 1px solid black;text-align:right;">'.number_format($AMOUNT_IDR/$rate, 2).'&nbsp;&nbsp;</td>
                                    <td style="border-left: 1px solid black;text-align:right;">'.number_format($AMOUNT_RATE, 0).'&nbsp;&nbsp;</td>
                                    <td style="border-left: 1px solid black;text-align:right;border-right: 1px solid black;">'.number_format($AMOUNT_IDR, 0).'&nbsp;&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="border-left: 1px solid black;">&nbsp;</td>
                                    <td style="border-left: 1px solid black;">&nbsp;</td>
                                    <td style="border-left: 1px solid black;border-right: 1px solid black;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="border-left: 1px solid black;border-bottom: 1px solid black;">&nbsp;</td>
                                    <td style="border-left: 1px solid black;border-bottom: 1px solid black;">&nbsp;</td>
                                    <td style="border-left: 1px solid black;border-right: 1px solid black;border-bottom: 1px solid black;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="text-align: center;border-left: 1px solid black;border-bottom: 1px solid black;">Total</td>
                                    <td style="border: 1px solid black;text-align:right;">Rp '.number_format($AMOUNT_IDR, 0).'&nbsp;&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                        <td width="5%">&nbsp;</td>
                        <td width="40%">
                            <p>Mengetahui,<br>
                            '.$web_name_full.'</p>
                            <br><br><br>
                            <div style="border-bottom: 1px solid black;"></div>
                        </td>
                    </tr>
                </table>
            </div> -->
        </div>
    </body>
</html>