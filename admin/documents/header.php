<?php
    use App\Models\CompanyProfile;
    $COMPANY_PRF     = CompanyProfile::profilePerusahaan();
    $COMPANY_MOF     = CompanyProfile::getMainOffice();
?>
            <table width="100%" style="border-collapse: collapse;">
                <tr>
                    <td width="30%" style="vertical-align:top;"><img style="object-fit: cover; max-height: 100%; max-width: 100%;" src="data:image/png;base64,<?= base64_encode(file_get_contents($logo_pdf)); ?>"></td>
                    <td width="10%">&nbsp;</td>
                    <td style="text-align:right;">
                        <!-- <strong><?= $COMPANY_PRF['COMPANY_NAME'] ?></strong>
                        <p style="font-size: 12px; margin: 0px;">
                            <?= $COMPANY_MOF['OFC_ADDRESS'] ?><br>
                            Telp. <?= $COMPANY_MOF['OFC_PHONE'] ?>, Fax. <?= $COMPANY_MOF['OFC_FAX'] ?>
                        </p> -->
                    </td>
                </tr>
            </table>