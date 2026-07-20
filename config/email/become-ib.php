<p>Yth. Bapak/Ibu <?=  $name ?? "-" ?>,</p>

<p style="margin-bottom: 0px;">Kami informasikan bahwa permintaan menjadi IB telah kami setujui dengan referral link yang dapat digunakan:</p>
<ul>
    <?php if($referral) : ?>
        <?php foreach($referral as $ref) : ?>
            <li><?= $ref['type'] ?> $<?= $ref['commission'] ?></li>
        <?php endforeach; ?>
    <?php endif; ?>
</ul>

<p>Anda dapat mengakses Sales Portal di Platform <a href="<?= $app_url ?>">Kami</a>. Sekian informasi yang dapat kami sampaikan.
Terima Kasih.</p>

<p style="margin-bottom: 0px;">Hormat Kami,</p>
<p style="margin-top: 0px; margin-bottom: 0px;">PT RRFX Investasi Berjangka</p>