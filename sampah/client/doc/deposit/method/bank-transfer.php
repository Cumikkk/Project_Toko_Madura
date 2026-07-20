<form action="" method="post" enctype="multipart/form-data" id="form-deposit-bank-transfer">
    <input type="hidden" name="key" value="<?= "deposit_" . uniqid() . App\Models\Helper::generateRandomString(5); ?>">
    <div class="row">
        <div class="col-md-12 mb-2">
            <label for="account" class="form-label required">Pilih Akun</label>
            <select name="account" id="account" class="form-control" required>
                <?php 
                    $allowedRights = [259, 355, 2307, 2311, 2401, 2403, 1100];
                    foreach(App\Models\Account::myAccount($user['MBR_ID']) as $account) :
                        if(in_array($account['rights'], $allowedRights)) :
                ?>
                    <option value="<?= md5(md5($account['ID_ACC'])) ?>" data-currency="<?= $account['RTYPE_CURR']; ?>">
                        <?= $account['ACC_LOGIN'] ?> (<?= (App\Models\Helper::formatCurrency($account['MARGIN_FREE'])) ?: '0' ?> USD)
                    </option>
                <?php 
                        endif;
                    endforeach;
                ?>
            </select>
        </div>

        <div class="col-md-12 mb-2">
            <label for="sender-bank" class="form-label required">Pilih Bank</label>
            <select name="sender-bank" id="sender-bank" class="form-control" required>
                <?php foreach(App\Models\MemberBank::list($user['MBR_ID'], [App\Models\MemberBank::$statusAccepted]) as $sender) : ?>
                    <option value="<?= md5(md5($sender['ID_MBANK'])) ?>"><?= implode(" / ", [$sender['MBANK_NAME'], $sender['MBANK_HOLDER'], $sender['MBANK_ACCOUNT']]) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-12 mb-2">
            <label for="receive-bank" class="form-label required">Pilih Bank Tujuan</label>
            <select name="receive-bank" id="receive-bank" class="form-control" required>
                <option value="">Pilih</option>
            </select>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-12 mb-2">
            <label for="amount" class="form-label required">Jumlah</label>
            <div class="input-group">
                <span class="input-group-text" id="currency">IDR</span>
                <input type="text" class="form-control amount-formatter" data-max="<?= str_pad("1", 15, "0", STR_PAD_RIGHT); ?>" name="amount" placeholder="0" required>
            </div>
        </div>
        <div class="col-md-12 mb-2">
            <label for="image" class="form-label">Bukti Transfer</label>
            <input type="file" name="image" id="image" class="dropify" accept="image/jpg, image/jpeg, iamge/png" data-allowed-file-extensions="png jpg jpeg">
        </div>
    </div>

    <div class="text-end">
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</form>
<script type="text/javascript">
    $(document).ready(function() {
        $('.dropify').dropify();
        $('#account').on('change', function() {
            let currency = $(this).find('option:selected').data('currency')
            $('#receive-bank').empty().append('<option>Loading...</option>')
            $.post("/ajax/post/deposit/bank-transfer/get-bank-destination", {currency: currency}, (resp) => {
                if(!resp.success) {
                    Swal.fire(resp.alert);
                    return false;
                }
                
                $('#currency').text(currency);
                $('#receive-bank').empty();
                resp.data.forEach((val, i) => {
                    $('#receive-bank').append(`<option value="${val.id}">${val.detail}</option>`);
                })
            }, 'json')
        }).change();

        $('#form-deposit-bank-transfer').on('submit', function(event) {
            event.preventDefault();
            let button = $(this).find('button[type="submit"]');
            let data = new FormData(this);
            
            Swal.fire({
                title: "Deposit Bank Transfer",
                text: "Konfirmasi untuk melanjutkan",
                icon: "question",
                showCancelButton: true,
                reverseButtons: true
            }).then((result) => {
                if(result.isConfirmed) {
                    button.addClass('loading')
                    Swal.fire({
                        text: "Loading...",
                        allowOutsideClick: false,
                        didOpen: function() {
                            Swal.showLoading();
                        }
                    })

                    $.ajax({
                        url: "/ajax/post/deposit/bank-transfer/create",
                        type: "post",
                        dataType: "json",
                        data: data,
                        contentType: false,
                        processData: false,
                        cache: false
                    }).done((resp) => {
                        button.removeClass('loading')
                        Swal.fire(resp.alert).then(() => {
                            if(resp.success) {
                                location.href = '/deposit';
                            }
                        })
                    })
                }
            })
        })
    })
</script>