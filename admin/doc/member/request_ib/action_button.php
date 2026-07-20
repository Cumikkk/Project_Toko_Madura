<?php if($permisAccept = $adminPermissionCore->isHavePermission($moduleId, "action")) : ?>
    <div class="modal fade" tabindex="-1" id="modalBecomeIB" aria-labelledby="label-modalBecomeIB">
        <div class="modal-dialog modal-dialog-top">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" aria-label="label-modalBecomeIB">Accept Request</h5>
                    <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal">&times;</button>
                </div>
                <form action="/ajax/post/member/request_ib/action" method="post" id="form-edit-BecomeIB">
                    <div class="modal-body">
                        <input type="hidden" name="id" readonly required>
                        <input type="hidden" name="type" value="accept" readonly required>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="becomeib_name" class="form-control-label mb-0">Name</label>
                                <input type="text" name="becomeib_name" id="becomeib_name" class="form-control" readonly required></input>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="becomeib_email" class="form-control-label mb-0">Email</label>
                                <input type="text" name="becomeib_email" id="becomeib_email" class="form-control" readonly required></input>
                            </div>
                        </div>
                        <div id="modal-fields-container" class="row mb-3">
                            
                        </div>
                    </div>
                    <div class="modal-footer text-end">
                        <button type="button" class="btnClose btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
        $sqlGetActiveUser = $db->query("
            SELECT
                tb_member.MBR_ID,
                tb_member.MBR_NAME,
                tb_member.MBR_EMAIL,
                tb_member.MBR_TYPE,
                tb_member.MBR_CODE,
                ss1.SLSSTRC_LEVEL,
                ss1.SLSSTRC_NAME,
                tb_sales_division.SLSDIVISION_NAME,
                (
                    SELECT MAX(ss2.SLSSTRC_LEVEL)
                    FROM tb_sales_structure ss2
                    WHERE ss2.SLSSTRC_DIV = ss1.SLSSTRC_DIV
                ) AS MAX_LEVEL
            FROM tb_member
            JOIN tb_sales_structure ss1 ON(ss1.ID_SLSSTRC = tb_member.MBR_TYPE)
            JOIN tb_sales_division ON(tb_sales_division.ID_SLSDIVISION = ss1.SLSSTRC_DIV)
            WHERE tb_member.MBR_TYPE > 0
            HAVING SLSSTRC_LEVEL < MAX_LEVEL
        ");
    ?>
    <script type="text/javascript">
        let modal = $('#modalBecomeIB')
        $(document).ready(function() {
            if(table_pending) {
                table_pending.on('draw.dt', function() {
                    $.each($('#table_pending tbody tr'), (i, tr) => {
                        let td = $(tr).find('td').eq(4);
                        if(td) {
                            let actionArea = td.find('.action');
                            if(actionArea && !actionArea.find('.btn-update').length && actionArea.data('data')) {
                                let data = JSON.parse(atob(actionArea.data('data')));
                                let dataArray = [];
                                for(i in data) {
                                    dataArray.push(`data-${i}="${data[i]}"`);
                                }
                                actionArea.append(`<a href="javascript:void(0)" class="btn btn-sm btn-success btn-update" data-type="accept" ${dataArray.join(" ")}><i class="fas fa-edit"></i></a>`);
                            }
                        }
                    })

                    $('.btn-update').off('click').on('click', function(evt) {
                        let target = $(evt.currentTarget);
                        if(target) {
                            let data = target.data();
                            let fieldsContainer = modal.find('#modal-fields-container');
                            
                            modal.find('input[name="id"]').val(data.id);
                            modal.find('input[name="becomeib_name"]').val(data.name);
                            modal.find('input[name="becomeib_email"]').val(data.email);
                            fieldsContainer.empty(); 
                            let htmlContent = '';
                            if (data.parent == 1000000000 && data.parent_type != '1') {
                                htmlContent = `
                                    <div class="col-md-12 mb-3">
                                        <label for="upline" class="form-control-label mb-0 required">Upline</label>
                                        <select name="upline" id="upline" class="form-control form-select" required>
                                            <option value="">Pilih</option>
                                            <?php if($sqlGetActiveUser && $sqlGetActiveUser->num_rows > 0) : ?>
                                                <?php foreach($sqlGetActiveUser->fetch_all(MYSQLI_ASSOC) as $upl) : ?>
                                                    <option value="<?= $upl['MBR_CODE'] ?>">
                                                        <?= '('.$upl['SLSDIVISION_NAME'].' - '.$upl['SLSSTRC_NAME'].') '.$upl['MBR_NAME'].' - '.$upl['MBR_EMAIL'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                `;
                            } else if (data.parent != 1000000000 && data.parent_type == '1') {
                                htmlContent = `
                                    <div class="col-md-12 mb-3">
                                        <label for="structure" class="form-control-label mb-0 required">Structure</label>
                                        <select name="structure" id="structure" class="form-control form-select" required>
                                            <option value="" selected disabled>Pilih</option>
                                            <?php 
                                                foreach(App\Models\SalesStructure::structure() as $struc) :
                                                    if($struc['SLSSTRC_UP'] == 1 && $struc['SLSSTRC_LEVEL'] == 1) :
                                            ?>
                                                <option value="<?= md5(md5($struc['ID_SLSSTRC'])) ?>">
                                                    <?= $struc['SLSSTRC_NAME'] ?>
                                                </option>
                                            <?php 
                                                    endif;
                                                endforeach;
                                            ?>
                                        </select>
                                    </div>
                                `;
                            } else {
                                htmlContent = ``;
                            }
                            fieldsContainer.append(htmlContent);
                            modal.modal('show');
                        }
                    })
                });
                $('#form-edit-BecomeIB').on('submit', function(event) {
                    event.preventDefault();
                    let target = $(event.currentTarget);
                    let button = target.find('button[type="submit"]');
                    let url = target.attr('action');
                    
                    let formData = target.serialize();

                    button.addClass('loading');
                    $.post(url, formData, function(resp) {
                        button.removeClass('loading');
                        modal.modal('hide');
                        Swal.fire(resp.alert).then(() => {
                            if (resp.success) {
                                table_pending.draw();
                            }
                        });
                    }, 'json');
                });
            }
        })
    </script>
<?php endif; ?>