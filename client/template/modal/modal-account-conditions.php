<div class="modal fade" id="modalAccountConditions" tabindex="-1" aria-labelledby="modalAccountConditionsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Account Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" id="myTeamForm">
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="racc" id="racc">
                        <input type="hidden" name="mbr" id="mbr">
                        <input type="hidden" name="max" id="max">
                        <div class="col-12">
                            <div class="mb-3">
                                <div class="alert alert-danger" id="reject-message"></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="slscondition_sales" class="form-label">Sales</label>
                                <input type="text" class="form-control" id="slscondition_sales" name="slscondition_sales" placeholder="Sales Name">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="slscondition_branch" class="form-label">Branch</label>
                                <input type="text" class="form-control" id="slscondition_branch" name="slscondition_branch" placeholder="Branch Name">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Max Commission</label>
                                <input type="text" class="form-control" placeholder="Max" id="maxCharge" value="" disabled>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="text-end mb-2">
                                <!-- <div class="btn btn-primary" id="addRowSlsconditions">Add Client</div> -->
                            </div>
                            <div id="modalAccountConditionsBody" class="d-flex flex-column gap-3"></div>
                            <div class="small text-muted" id="emptyClientNotice">Belum ada client yang ditambahkan.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
<style>
    #modalAccountConditions .modal-body {
        max-height: calc(100vh - 220px);
        overflow-y: auto;
        overflow-x: hidden;
    }

    @media (max-width: 576px) {
        #modalAccountConditions .modal-body {
            max-height: calc(100vh - 180px);
        }
    }
</style>
<script type="text/javascript">
    $(document).ready(function() {
        const accountConditionsModal = document.getElementById('modalAccountConditions');

        function updateClientCardsState() {
            const totalCards = $('#modalAccountConditionsBody .client-condition-card').length;
            $('#emptyClientNotice').toggle(totalCards === 0);

            $('#modalAccountConditionsBody .client-order').each(function(index) {
                $(this).text(`Client ${index + 1}`);
            });
        }

        function validateMaxDistribution() {
            const maxValue = Number($('#max').val()) || 0;
            const categories = [
                { name: 'slscom_forex', label: 'Forex' },
                { name: 'slscom_gold', label: 'Gold' },
                { name: 'slscom_index', label: 'Index' }
            ];

            for(const category of categories) {
                let total = 0;
                let invalid = false;

                $(`input[name="${category.name}[]"]`).each(function() {
                    const raw = String($(this).val() ?? '').trim();
                    if(raw === '') {
                        return;
                    }

                    const value = Number(raw);
                    if(isNaN(value) || value < 0 || value > maxValue) {
                        invalid = true;
                        return false;
                    }

                    total += value;
                });

                if(invalid) {
                    return `${category.label} must be a number between 0 and ${maxValue}`;
                }

                if(total > maxValue) {
                    return `Total ${category.label} exceeds max ${maxValue}`;
                }
            }

            return '';
        }

        $('#addRowSlsconditions').on('click', function() {
            return false;
            Swal.fire({
                target: accountConditionsModal,
                backdrop: false,
                title: "Masukkan email client",
                input: "text",
                inputAttributes: {
                    autocapitalize: "off"
                },
                showCancelButton: true,
                confirmButtonText: "Simpan",
                showLoaderOnConfirm: true,
                didOpen: () => {
                    const input = Swal.getInput();

                    if(input) {
                        input.focus();
                    }
                },
                preConfirm: async (email) => {
                    try {
                        $.post('/ajax/post/ib/my-teams/check-account', {email : email, mbr: $('#mbr').val()}, function(response) {
                            if(response.success) {
                                var htmlCondition = `<div class="card client-condition-card border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="mb-0 client-order"></h6>
                                            <!-- <button type="button" class="btn btn-sm btn-danger remove-row">Delete</button> -->
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-12 col-md-6">
                                                <label class="form-label mb-1">Client Name</label>
                                                <input type="email" class="form-control" name="email_client[]" placeholder="Email Client" value="${response.data.email}" readonly>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label mb-1">Posisi</label>
                                                <input type="text" class="form-control" name="posisi_client[]" placeholder="Posisi Client" value="${response.data.posisi}" readonly>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label mb-1">Forex</label>
                                                <input type="text" class="form-control input-number" name="slscom_forex[]" placeholder="Forex" value="">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label mb-1">Gold</label>
                                                <input type="text" class="form-control input-number" name="slscom_gold[]" placeholder="Gold" value="">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label mb-1">Index</label>
                                                <input type="text" class="form-control input-number" name="slscom_index[]" placeholder="Index" value="">
                                            </div>
                                        </div>
                                    </div>
                                </div>`;
                                $('#modalAccountConditionsBody').append(htmlCondition);
                                updateClientCardsState();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message
                                });
                            }
                        }, 'json');
                    } catch (error) {
                        Swal.showValidationMessage(`Request failed: ${error}`);
                    }
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
            });
        });

        $(document).on('click', '.remove-row', function() {
            return false;
            $(this).closest('.client-condition-card').remove();
            updateClientCardsState();
        });

        updateClientCardsState();

        $('#myTeamForm').submit(function(event) {
            event.preventDefault();

            const validationMessage = validateMaxDistribution();
            if(validationMessage) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: validationMessage
                });
                return;
            }

            Swal.fire({
                title: 'Submitting...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });

            const formData = $(this).serializeArray();
            $.post('/ajax/post/ib/my-teams/set-account-commission', formData, function(response) {
                Swal.close();
                if(response.success) {
                    $('#modalAccountConditions').modal('hide');
                    $('#table_available_account_conditions').DataTable().ajax.reload(null, false);
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            }, 'json');
        });
    });
</script>