<div class="modal fade" id="modal-setting" style="background-color: #0000008a;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Commission</h5>
                <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="post" id="form-commission-setting">
                <div class="modal-body">
                    <div id="commission-spinner">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    <div id="commission-error" class="alert alert-danger"></div>
                    <div id="commission-content"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        let modal = $('#modal-setting')
        let modalSpinner = modal.find('#commission-spinner');
        let modalContent = modal.find('#commission-content');
        let modalError = modal.find('#commission-error');
         
        modal.on('hide.bs.modal', function() {
            modalSpinner.show();
            modalContent.hide();
        })

        modal.on('show.bs.modal', function(evt) {
            modalError.hide();
            modal.find('button[type="submit"]').removeClass('loading');

            let buttonClicked = $(evt.relatedTarget);
            if(!buttonClicked) {
                modalSpinner.hide();
                modalContent.append('Invalid Target').show();
                return false;    
            }

            let data = buttonClicked.data();
            $.ajax({
                url: "/ajax/post/commission/modal_view_setting",
                type: "post",
                dataType: "html",
                data: data
            }).done((html) => {
                modalSpinner.hide();
                modalContent.html(html).show();
            })
        })

        $('#form-commission-setting').on('submit', function(event) {
            event.preventDefault();
            let button = $(this).find('button[type="submit"]');
            button.addClass('loading');

            $.post("/ajax/post/commission/update_amount", $(this).serialize(), (resp) => {
                button.removeClass('loading');
                if(!resp.success) {
                    modalError.text(resp.message).show();
                    return;
                }

                modal.modal('hide')
                Swal.fire(resp.alert).then(() => location.reload());
            }, 'json')
        })
    })
</script>