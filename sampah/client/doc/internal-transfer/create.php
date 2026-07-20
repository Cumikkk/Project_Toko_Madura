<?php if(count(App\Models\Account::myAccount($user['MBR_ID'])) <= 0) die("<script>location.href = '/internal-transfer'; </script>"); ?>

<div class="alert alert-primary">
    <p><span class="text-danger">*</span> Internal transfer hanya bisa dilakukan dengan syarat rate akun pengirim dan penerima sama</p>
</div>

<div class="row">
    <div class="col-md-6 mb-2">
        <div class="panel">
            <div class="panel-header">
                <div class="d-flex flex-column gap-1">
                    <h5 class="panel-title">Form Internal Transfer</h5>
                </div>
            </div>
            <div class="panel-body">
                <form action="" method="post" id="form-internal-transfer">
                    <input type="hidden" name="key" value=<?= base64_encode(uniqid()); ?>>
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <label for="from-account" class="form-label required">Pilih Akun Pengirim</label>
                            <select name="from-account" id="from-account" class="form-control" required>
                                <?php foreach(App\Models\Account::myAccount($user['MBR_ID']) as $account) : ?>
                                    <option value="<?= $account['ACC_LOGIN'] ?>"><?= $account['ACC_LOGIN'] ?> (<?= App\Models\Helper::formatCurrency($account['MARGIN_FREE']) ?> USD)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label for="to-account" class="form-label required">Pilih Akun Penerima</label>
                            <select name="to-account" id="to-account" class="form-control" required>
                                <option value="">Pilih</option>
                            </select>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="amount" class="form-label required">Jumlah</label>
                            <div class="input-group">
                                <spon class="input-group-text">USD</spon>
                                <input type="text" name="amount" id="amount" class="form-control amount-formatter" data-max="<?= str_pad("1", 10, "0", STR_PAD_RIGHT); ?>" required>
                            </div>
                        </div>
                    </div>
    
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#from-account').on('change', function() {
            $.post("/ajax/post/internal-transfer/fetch-account", {from: $(this).val()}, (resp) => {
                if(!resp.success) {
                    Swal.fire(resp.alert);
                    return false;
                }

                $('#to-account').empty().append('<option value="">Pilih</option>');
                resp.data.forEach((val, i) => {
                    $('#to-account').append(`<option value="${val.login}">${val.login} (${val.balance})</option>`);
                })
            }, 'json')
        }).change()

        $('#form-internal-transfer').on('submit', function(event) {
            event.preventDefault();
            let data = $(this).serialize();
            let button = $(this).find('button[type="submit"]');
            let from = $('#from-account').val();
            let to = $('#to-account').val();

            button.addClass('loading');
            Swal.fire({
                title: "Internal transfer",
                text: `Apakah anda yakin ingin melakukan internal transfer dari akun ${from} ke ${to} senilai ${$('#amount').val()} USD`,
                icon: "question",
                showCancelButton: true,
                reverseButtons: true
            }).then((result) => {
                if(!result.isConfirmed) {
                    button.removeClass('loading');
                    return false;
                }
                
                if(result.isConfirmed) {
                    Swal.fire({
                        text: "Loading...",
                        allowOutsideClick: false,
                        didOpen: function() {
                            Swal.showLoading();
                        }
                    })

                    $.post("/ajax/post/internal-transfer/create", data, (resp) => {
                        button.removeClass('loading');
                        Swal.fire(resp.alert).then(() => {
                            if(resp.success) {
                                location.href = '/internal-transfer'
                            }
                        })
                    }, 'json')
                    .fail(error => {
                        Swal.fire("Error", "Internal Server Error", "error");
                        button.removeClass('loading');
                    })
                }
            })
        })
    })
</script>