<div class="panel">
    <div class="panel-header">
        <h5 class="panel-title">Form Pembuatan Tiket</h5>
    </div>
    <div class="panel-body">
        <form action="" method="post" id="form-create-ticket">
            <div class="row">
                <div class="col-md-12">
                    <label for="topic" class="form-label required">Topik</label>
                    <select name="topic" id="topic" class="form-select">
                        <?php foreach (\App\Models\Ticket::topicList() as $topic) : ?>
                            <option value="<?= $topic ?>"><?= ucfirst(str_replace('_', ' ', $topic)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-12">
                    <label for="subject" class="form-label required">Subjek</label>
                    <input type="text" name="subject" id="subject" class="form-control" placeholder="Subjek">
                </div>
                <div class="col-md-12">
                    <label for="desc" class="form-label">Deskripsi <small>(opsional)</small></label>
                    <textarea name="desc" id="desc" rows="5" class="form-control" placeholder="Deskripsi"></textarea>
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary btn-block w-100">Buat</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#form-create-ticket').on('submit', function(event) {
            event.preventDefault();
            let data = $(this).serialize();
            let button = $(this).find('button[type="submit"]');

            button.addClass('loading');
            $.ajax({
                url: "/ajax/post/ticket/create",
                method: "POST",
                data: data,
                dataType: "json",
                success: function(resp) {
                    if(!resp.success){
                        Swal.fire(resp.alert);
                        return false;
                    }

                    location.href = resp.data.redirect;
                },
                error: function() {
                    Swal.fire("Error", "An error occurred while creating the ticket", 'error')
                },
            })
            .always(() => {
                button.removeClass('loading');
            })
        })
    })
</script>