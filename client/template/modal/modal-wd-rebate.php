<div class="modal fade" id="modal-wd-rebate" style="background-color: #0000008a;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Withdrawal Rebate Commission</h5>
                <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="post" id="form-withdraw-rebate">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <label for="user-bank" class="form-label required">Pilih Bank</label>
                            <select name="user-bank" id="user-bank" class="form-control" required>
                                <?php foreach(App\Models\User::myBank($user['MBR_ID']) as $sender) : ?>
                                    <option value="<?= md5(md5($sender['ID_MBANK'])) ?>"><?= implode(" / ", [$sender['MBANK_NAME'], $sender['MBANK_HOLDER'], $sender['MBANK_ACCOUNT']]) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-12 mb-2">
                            <label for="amount" class="form-label required">Jumlah</label>
                            <div class="input-group">
                                <span class="input-group-text">USD</span>
                                <input type="text" class="form-control amount-formatter" data-max="<?= str_pad("1", 10, "0", STR_PAD_RIGHT); ?>" name="amount" placeholder="0" required>
                            </div>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label for="otp" class="form-label required">Kode OTP</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="otp" max="10000" required>
                                <a href="javascript:void(0)" id="sendOtp" class="btn btn-secondary">Kirim OTP</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Buat</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#sendOtp').on('click', function() {
            Swal.fire({
                text: "Loading...",
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            })

            $.post("/ajax/post/withdrawal/send-otp", {}, (resp) => {
                Swal.fire(resp.alert);
            }, 'json');
        })

        $('#form-withdraw-rebate').on('submit', function(event) {
            event.preventDefault();
            let button = $(this).find('button[type="submit"]');
            button.addClass('loading');

            $.post("/ajax/post/withdrawal/wallet/wallet-rebate", $(this).serialize(), (resp) => {
                button.removeClass('loading');
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        location.reload()
                    }
                })
            }, 'json')
        })
    })
</script>