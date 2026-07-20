<div style="font-family: Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.6; max-width: 480px; margin: auto;">
    
    <!-- Icon Header -->
    <div style="text-align: center; margin-bottom: 15px;">
        <div style="font-size: 40px;">⚠️</div>
        <strong style="font-size: 16px; color: #e67e22;">Limit Margin Tercapai</strong>
    </div>

    <p>Halo <?php echo $name ?? ""; ?>,</p>

    <p>
        Akun Anda saat ini telah mencapai batas maksimum margin yang diperbolehkan.
        Agar dapat terus melakukan aktivitas trading, silakan lakukan upgrade akun ke
        <strong>CDD Standar</strong>.
    </p>

    <!-- Account Info Box -->
    <div style="background: #f4f8fb; border: 1px solid #d6e4f0; padding: 12px 15px; border-radius: 6px; margin: 15px 0;">
        <strong style="display: block; margin-bottom: 8px;">📊 Informasi Akun</strong>

        <table style="width: 100%; font-size: 13px; border-collapse: collapse;">
            <tr>
                <td style="padding: 4px 0; color: #555;">Nomor Akun</td>
                <td style="padding: 4px 0; text-align: right;"><strong><?= $accountNumber ?? ""; ?></strong></td>
            </tr>
            <tr>
                <td style="padding: 4px 0; color: #555;">Tipe Akun</td>
                <td style="padding: 4px 0; text-align: right;"><strong><?= $accountType ?? ""; ?></strong></td>
            </tr>
            <tr>
                <td style="padding: 4px 0; color: #555;">Limit Margin</td>
                <td style="padding: 4px 0; text-align: right; color: #e67e22;"><strong><?= $limitMargin ?? "Limit Tercapai"; ?></strong></td>
            </tr>
        </table>
    </div>

    <!-- Info Box -->
    <div style="background: #fff6e5; border: 1px solid #f5c26b; padding: 12px; border-radius: 6px; margin: 15px 0;">
        <strong>⚡ Informasi:</strong><br>
        Upgrade dapat dilakukan dengan memperbarui formulir pembukaan akun melalui dashboard Anda.
    </div>

    <!-- Button -->
    <div style="text-align: center; margin: 20px 0;">
        <a href="<?php echo $upgradeUrl ?? '#'; ?>" style="background: #e67e22; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">
            Lengkapi data sekarang
        </a>
    </div>
</div>