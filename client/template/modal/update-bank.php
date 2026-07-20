<div class="modal fade" id="modal-edit-bank" style="background-color: #0000008a;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Bank</h5>
                <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="post" id="form-edit-bank" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <label for="bank-name" class="form-label">Nama Bank</label>
                            <select name="bank-name" class="form-control form-select">
                                <option value="" selected disabled>Pilih</option>
                                <?php foreach(App\Models\BankList::all() as $bank) : ?>
                                    <option value="<?= $bank['BANKLST_NAME'] ?>"><?= $bank['BANKLST_NAME'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label for="bank-number" class="form-label">No. Rekening</label>
                            <input type="text" class="form-control" data-kind="bankaccount" inputmode="numeric" name="bank-number" id="bank-number" placeholder="Nomor Rekening" required>
                        </div>
                         <div class="col-md-12 mb-2">
                            <label for="bank-number" class="form-label">Cover Buku Tabungan</label>
                            <input type="file" class="c-dropify" name="imagecover" id="imagecover" accept="image/jpg, image/jpeg, iamge/png" data-allowed-file-extensions="png jpg jpeg" data-max-file-size="2M">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="id" value="">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (() => {
        // Konfigurasi ringkas: title, pattern, minimal & maksimal KARAKTER (bukan hanya digit)
        const CONFIG = {
            "bankaccount": { 
                title: "Pastikan nomer bank benar", 
                pattern: "^\\d{10,16}$", 
                min: 10,  
                max: 16 
            },
        };


        // --- Filter nilai sesuai tipe ---
        function sanitizeByKind(val, kind) {
            if (kind === "nama") {
                // huruf latin + spasi . ' ’ -
                return (val || "").replace(/[^A-Za-zÀ-ÖØ-öø-ÿ .,'’\-]/g, "");
            }
            if (kind === "phone") {
                // angka + opsional satu '+' di depan
                const hasPlusFirst = (val || "").startsWith("+");
                const digitsOnly = (val || "").replace(/\D/g, "");
                return hasPlusFirst ? ("+" + digitsOnly) : digitsOnly;
            }
            // kodepos & npwp: angka saja
            return (val || "").replace(/\D/g, "");
        }

        // Blokir karakter tidak valid saat KETIK (paste dibersihkan di 'input')
        document.addEventListener("beforeinput", (e) => {
            const el = e.target;
            if (!el.matches('input[data-kind]')) return;

            const kind = el.dataset.kind;
            const t = e.inputType;
            const ch = e.data ?? "";

            if (t === "insertText") {
                if (kind === "nama") {
                    // izinkan huruf latin + spasi . ' ’ -
                    if (!/^[A-Za-zÀ-ÖØ-öø-ÿ .,'’\-]$/.test(ch)) e.preventDefault();
                } else if (kind === "phone") {
                    const selStart = el.selectionStart ?? 0;
                    const insertingPlus = ch === "+";
                    const alreadyPlus = el.value.includes("+");
                    const isDigit = /\d/.test(ch);
                    if (insertingPlus) {
                        if (selStart !== 0 || alreadyPlus) e.preventDefault();
                    } else if (!isDigit) {
                        e.preventDefault();
                    }
                } else {
                    // kodepos/npwp -> hanya digit
                    if (!/\d/.test(ch)) e.preventDefault();
                }
            }
        });

        // Terapkan aturan + balon error bawaan browser
        function applyRules(el, { showNow = false } = {}) {
            const kind = el.dataset.kind;
            const cfg = CONFIG[kind];
            if (!cfg) return;

            // sanitize
            const cleaned = sanitizeByKind(el.value, kind);
            if (cleaned !== el.value) el.value = cleaned;

            // atribut validasi
            el.setAttribute("title", cfg.title);
            el.setAttribute("pattern", cfg.pattern);
            el.setAttribute("minlength", String(cfg.min));
            el.setAttribute("maxlength", String(cfg.max));

            // cek validitas ringan untuk pesan cepat
            const val = el.value;
            let msg = "";
            if (val.length === 0) {
                msg = ""; // biarkan required
            } else if (val.length < cfg.min) {
                msg = `Minimal ${cfg.min} karakter.`;
            } else if (val.length > cfg.max) {
                msg = `Maksimal ${cfg.max} karakter.`;
            } else if (!(new RegExp(cfg.pattern).test(val))) {
                msg = cfg.title;
            }
            el.setCustomValidity(msg);

            if (showNow) el.reportValidity();
        }

        // keyup -> tampilkan balon sekarang
        document.addEventListener("keyup", (e) => {
            if (e.target.matches('input[data-kind]')) applyRules(e.target, { showNow: true });
        });

        // input -> handle paste/autofill
        document.addEventListener("input", (e) => {
            if (e.target.matches('input[data-kind]')) applyRules(e.target);
        });

        // init
        window.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll('input[data-kind]').forEach(el => applyRules(el));
        });
    })();
</script>
<script type="text/javascript">
    $(document).ready(function() {

        $('#modal-edit-bank').on('show.bs.modal', function(evt) {
            let target = $(evt.relatedTarget);
            if(target) {
                let data = target.data();
                $(this).find('input[name="id"]').val(data.id);
                $(this).find('select[name="bank-name"]').val(data.nama);
                $(this).find('input[name="bank-number"]').val(data.rekening);
                let imageCover = $('.c-dropify').dropify();
                imageCover = imageCover.data('dropify');
                imageCover.resetPreview();
                imageCover.clearElement();
                imageCover.settings.defaultFile = data.image;
                imageCover.destroy();
                imageCover.init();
            }
        })

        $('#form-edit-bank').on('submit', function(event) {
            event.preventDefault();
            let data = $(this).serialize();
            let button = $(this).find('button[type="submit"]');

            button.addClass('loading');
            $.ajax({
                url: "/ajax/post/profile/update-bank",
                type: "post",
                dataType: "json",
                data: new FormData(this),
                processData: false,
                contentType: false,
                cache: false
            }).done((resp) => {
                button.removeClass('loading');
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        location.reload();
                    }
                })
            })
        })
    })
</script>