<div class="row">
    <div class="col-2"></div>
    <div class="col-8">
        <div class="panel rounded-0">
            <div class="panel">
                <div class="panel-body">
                    <h6>Informasi Demo Account</h6>
                    <table class="table table-bordered table-fixed">
                        <tbody>
                            <tr><td class="text-start"><b>Login Meta:</b> <span id="login"><?= $demoAccount['ACC_LOGIN'] ?? "" ?></span></td></tr>
                            <tr><td class="text-start"><b>Password Meta:</b> <span id="passw"><?= $demoAccount['ACC_PASS'] ?? "" ?></span></td></tr>
                            <tr><td class="text-start"><b>Investor Meta:</b> <span id="invst"><?= $demoAccount['ACC_INVESTOR'] ?? "" ?></span></td></tr>
                            <tr><td class="text-start"><b>Phone Meta:</b> <span id="phone"><?= $demoAccount['ACC_PASSPHONE'] ?? "" ?></span></td></tr>
                            <?php $returnGetDemoServer = (mysqli_query($db, "SELECT ID_RTYPE, RTYPE_GROUP, RTYPE_LEVERAGE, RTYPE_SERVER FROM tb_racctype WHERE UPPER(RTYPE_TYPE) = 'DEMO' LIMIT 1"))->fetch_assoc() ?? []; ?>
                            <tr><td class="text-start"><b>Server:</b> <span id="server"><?= $returnGetDemoServer['RTYPE_SERVER'] ?? "" ?></span></td></tr>
                        </tbody>
                    </table>
                    <p id="nte"></p>
                </div>
            </div>
            <?php if(empty($demoAccount)) : ?>
                <div class="panel-body border-top" id="button-create-demo">
                    <div class="btn-box d-flex justify-content-end gap-2">
                        <form action="" id="createDemo">
                            <input type="hidden" name="csrf_token" value="<?= uniqid() ?>">
                            <button type="submit" class="btn btn-sm btn-primary">Buat Demo Account <i class="fa-light fa-plus"></i></button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
            <div class="panel-footer">
                <div class="text-end mt-25">
                    <a href="<?= ($nextPage['page'])? ("/account/create?page=".$nextPage['page']) : "javascript:void(0)"; ?>" class="btn btn-primary">Berikutnya</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-2"></div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#createDemo').on('submit', function(event) {
            event.preventDefault();
            Swal.fire({
                text: "Please wait...",
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            })
            
            let object = Object.fromEntries(new FormData(this).entries())
            $.post("/ajax/regol/createDemo", object, function(resp) {
                if(!resp.success) {
                    Swal.fire("Failed", resp.error, "error");
                    return false;
                }

                $('#button-create-demo').hide();
                Swal.fire("Success", resp.message, "success")
                $('#login').text(resp.data.login);
                $('#passw').text(resp.data.passw);
                $('#invst').text(resp.data.invst);
                $('#phone').text(resp.data.phone);
                $('#nte').text(resp.data.mails);
            }, 'json')
        })
    })
</script>