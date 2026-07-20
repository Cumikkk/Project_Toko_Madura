<?php if($adminPermissionCore->isHavePermission($moduleId, "delete")) : ?>
<script>
$(function () {
  if (!table) return;

    $('#table-bank tbody').on('click', '.btn-delete', function (evt) {
        const $btn = $(this);

        let raw = $btn.attr('data-data');
        if (!raw) raw = $btn.closest('td').find('.action').attr('data-data');

        if (!raw) {
            console.error('data-data tidak ditemukan');
            Swal.fire({ icon:'error', text:'Data tidak ditemukan.' });
            return;
        }

        let data;
        try {
            data = JSON.parse(atob(String(raw)));
        } catch (e) {
            console.error('Decode Base64 gagal:', e, raw);
            Swal.fire({ icon:'error', text:'Data tidak valid.' });
            return;
        }

        Swal.fire({
            title: "Hapus bank",
            text: "Apakah anda yakin ingin menghapus bank ini?"+data.bank_name,
            icon: "question",
            showCancelButton: true,
            reverseButtons: true
        }).then((res) => {
            if (!res.isConfirmed) return;

            Swal.fire({
                text: "Loading...",
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.post("/ajax/post/tools/bank/delete", { id: data.idb, bank_name: data.bank_name }, (resp) => {
                Swal.fire(resp.alert).then(() => {
                    if (resp.success) table.draw(false);
                });
            }, 'json');
        });
    });

    table.on('draw.dt', function () {
        $('#table-bank tbody tr').each(function () {
            const $td = $(this).find('td').eq(2);
            const $action = $td.find('.action');
            if (!$td.length || !$action.length) return;

            if (!$action.find('.btn-delete').length) {
                const payload = $action.attr('data-data') || '';
                $action.append(
                `<a class="btn btn-sm btn-danger text-white btn-delete ms-1"
                    data-data="${payload}">
                    <i class="fas fa-trash"></i>
                </a>`
                );
            }
        });
    });
});
</script>
<?php endif; ?>
