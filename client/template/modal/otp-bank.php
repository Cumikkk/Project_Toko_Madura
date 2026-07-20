<div class="modal fade" id="modal-otp-bank" style="background-color: #0000008a;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">OTP Bank</h5>
                <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="post" id="form-otp-bank">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <label for="otp-bank" class="form-label">Enter OTP</label>
                            <input type="number" min="1000" max="9999" class="form-control" name="otp-bank" id="otp-bank" placeholder="Enter OTP" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="idotp" id="idotp" value="" readonly required>
                    <a href="javascript:void(0)" id="resendcode" class="btn btn-info">Resend</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {   
        $('#modal-otp-bank').on('show.bs.modal', function(evt) {
            let target = $(evt.relatedTarget);
            if(target) {
                let data = target.data();
                $(this).find('input[name="idotp"]').val(data.idotp);
            }
        })

        $('#form-otp-bank').on('submit', function(event) {
            event.preventDefault();
            let data = $(this).serialize();
            let button = $(this).find('button[type="submit"]');
        
            button.addClass('loading');

            $.ajax({
                url: "/ajax/post/profile/bank-otp-verification",
                type: "POST",
                dataType: "json",
                data: new FormData(this),
                contentType: false,
                processData: false,
                cache: false
            }).done(function(resp) {
                Swal.fire(resp.alert).then(() => {
                    location.reload();
                })
            })
        })

        $('#resendcode').on('click', function() {
            $.post("/ajax/post/profile/bank-otp-resend", {code: '1234'}, (resp) => {
                Swal.fire(resp.alert).then(() => {
                    location.reload();
                })
            }, 'json')
        })
    })
</script>