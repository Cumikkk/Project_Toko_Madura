<?php
use App\Models\Admin;

?>
<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">List Admin</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
            <li class="breadcrumb-item"><a href="./admin/view">Admin</a></li>
            <li class="breadcrumb-item active" aria-current="page"><a href="javascript:void(0);">List</a></li>
        </ol>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-header">
                <div class="d-flex justify-content-between mb-2">
                    <h5 class="card-title">List Admin</h5>
                    <a href="/admin/list/create" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Create Admin</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table" class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100">
                        <thead>
                            <tr class="text-center">
                                <th>Date</th>
                                <th>User</th>
                                <th>Name</th>
                                <th>Level</th>
                                <th>Status</th>
                                <th width="15%">#</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#table').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            scrollX: true,
            order: [[0, 'desc']],
            ajax: {
                url: "/ajax/datatable/admin/list/view",
                contentType: "application/json",
                type: "GET",
            },
            lengthMenu: [
                [10, 50, 100, -1],
                [10, 50, 100, "All"]
            ],
            drawCallback: function() {
                $('.delete').on('click', function(el) {
                    let target = $(el.currentTarget),
                        data = target.data();

                    Swal.fire({
                        title: "Hapus Admin?",
                        text: "Apakah anda yakin ingin menghapus admin ini?",
                        icon: "question",
                        showCancelButton: true,
                        reverseButtons: true
                    }).then((result) => {
                        if(result.isConfirmed) {
                            Swal.fire({
                                text: "Loading...",
                                didOpen: function() {
                                    Swal.showLoading();
                                }
                            })
                            
                            $.post("/ajax/post/admin/list/delete", data, (resp) => {
                                Swal.fire(resp.alert).then(() => {
                                    if(resp.success) {
                                        location.reload();
                                    }
                                })
                            }, 'json')
                        }
                    })
                })
            }
        });
    });
</script>