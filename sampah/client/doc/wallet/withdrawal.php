<link rel="stylesheet" href="/assets/css/my-teams.css">
<section class="section">
    <div class="section-header">
        <h2 class="section-title">Form Withdrawal</h2>
    </div>
    <hr>

    <form action="" id="form-withdrawal-commission">
        <input type="hidden" name="key" value="<?= "withdrawal_" . uniqid(); ?>">
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="panel">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-12 mb-2">
                                <label for="payment_method" class="form-label required">Payment Method</label>
                                <select class="form-control" id="payment_method">
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>
                            <div class="col-12 mb-2">
                                <label for="client_bank" class="form-label required">Client Bank</label>
                                <select name="client_bank" id="client_bank" class="form-control" required>
                                    <option value="" selected disabled>Select bank</option>
                                    <?php foreach(App\Models\MemberBank::list($user['MBR_ID'], [App\Models\MemberBank::$statusAccepted]) as $sender) : ?>
                                        <option value="<?= md5(md5($sender['ID_MBANK'])) ?>"><?= implode(" / ", [$sender['MBANK_NAME'], $sender['MBANK_HOLDER'], $sender['MBANK_ACCOUNT']]) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 mb-2">
                                <label for="amount" class="form-label required">Withdrawal Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">USD</span>
                                    <input type="text" class="form-control amount-formatter" id="amount" name="amount" data-max="<?= str_pad("1", 10, "0", STR_PAD_RIGHT); ?>" placeholder="Enter amount to withdraw" required>
                                </div>
                                <small style="font-size: .750em;" class="text-success"><i>Remaining Balance: $<?= App\Models\User::wallet($user['MBR_ID']); ?></i></small>
                            </div>
                             <div class="col-md-12 mb-2">
                                <label for="otp" class="form-label required">Kode OTP</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="otp" max="10000" placeholder="****" required>
                                    <a href="javascript:void(0)" id="sendOtp" class="btn btn-secondary">Kirim OTP</a>
                                </div>
                            </div>
                            <div class="col-12 mb-2 text-end">
                                <button type="submit" class="btn btn-primary">Submit Withdrawal</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>

<script type="text/javascript">
    $(document).ready(function() {
        $('input[name="otp"]').on('input', function() {
            if(this.value.length > 4) {
                this.value = this.value.slice(0, 4);
            }
        });

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

        $('#form-withdrawal-commission').on('submit', function(event) {
            event.preventDefault();
            let button = $(this).find('button[type="submit"]');
            button.addClass('loading');

            $.ajax({
                url: "/ajax/post/withdrawal/wallet/wallet-rebate",
                type: "post",
                dataType: "json",
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function(resp) {
                    Swal.fire(resp.alert).then(() => {
                        if(resp.success) {
                            location.href = '/wallet';
                        }
                    })
                },
                error: function() {
                    Swal.fire("Error", "An error occurred while processing your request.", "error");
                }
            })
            .always(() => {
                button.removeClass('loading');
            });
        })
    })
</script>