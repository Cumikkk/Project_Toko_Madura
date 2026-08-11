// Loading Buttons
const buttonsTarget = document.querySelectorAll('form button[type="submit"]');
const observer = new MutationObserver((mutationList) => {
    for(const mutation of mutationList) {
        if(mutation.type === "attributes" && mutation.attributeName === "class") {
            const el = mutation.target;
            let isLoading = el.classList.contains('loading');
            if(isLoading) {
                el.dataset.originalText = el.textContent;
                el.textContent = "Loading...";
                el.setAttribute('disabled', 'true');
            }else {
                el.textContent = el.dataset.originalText;
                el.removeAttribute('disabled');
            }
        }
    }
})

buttonsTarget.forEach((val) => {
    observer.observe(val, {attributes: true});
})


// Required label
const requiredLabels = document.querySelectorAll('label.required');
requiredLabels.forEach((el) => {
    if(el.classList.contains('required')) {
        el.innerHTML = `${el.textContent} <span class="text-danger">*</span>`;  
    }
})

$(document).ready(function() {
    $('.amount-formatter').on('keyup', function(evt) {
        let input = $(evt.currentTarget);

        input.val( formatter( $(evt.currentTarget).val() ) )
        if(input.data('max')) {
            let max = parseFloat(input.data('max'));
            if(parseFloat(input.val().replaceAll(',', '')) > max && max > 0) {
                return input.get(0).setCustomValidity("Max " + formatter(max.toString()));
            }

            input.get(0).setCustomValidity("");
            return input.get(0).reportValidity();
        }
    })
    
    // Validasi input hanya angka
    $(document).on('input', '.input-number', function() {
        $(this).val($(this).val().replace(/[^0-9]/g, ''));
    });

    // Handle Responsive Sidebar Toggle (Mobile Drawer & Desktop Collapse)
    let sidebarDebounceTimer = 0;

    function toggleMobileSidebar(e) {
        const now = Date.now();
        if (now - sidebarDebounceTimer < 300) {
            return;
        }
        sidebarDebounceTimer = now;

        if ($(window).width() < 992) {
            $('#mainSidebar').toggleClass('mobile-open sidebar-mini active');
            $('#sidebarOverlay').toggleClass('show');
        } else {
            $('#mainSidebar').toggleClass('collapsed');
            $('body').toggleClass('expanded');
            $('.header').toggleClass('expanded');
        }
    }

    function closeMobileSidebar() {
        $('#mainSidebar').removeClass('mobile-open sidebar-mini active sidebar-open');
        $('#sidebarOverlay').removeClass('show');
    }

    $(document).on('click', '#navClose, .nav-close-btn button, .nav-close-btn', function(e) {
        if ($(window).width() < 992) {
            e.preventDefault();
            e.stopPropagation();
            toggleMobileSidebar(e);
        }
    });

    $(document).on('click', '#mobileSidebarClose, #sidebarOverlay', function(e) {
        e.preventDefault();
        closeMobileSidebar();
    });

    $(document).on('click', '.tm-maroon-sidebar .sidebar-link:not(.sidebar-logout-btn)', function() {
        if ($(window).width() < 992) {
            closeMobileSidebar();
        }
    });

    // =========================================================================
    // GLOBAL LOGOUT CONFIRMATION MODAL
    // =========================================================================
    $(document).on('click', 'a[href*="/logout"], a[href$="logout"], .sidebar-logout-btn, #logout', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const logoutUrl = $(this).attr('href') || '/logout';

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Konfirmasi Logout',
                text: 'Apakah Anda yakin ingin logout dari sistem?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#7D0A0A',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Logout',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-danger rounded-pill px-4',
                    cancelButton: 'btn btn-secondary rounded-pill px-4 me-2'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = logoutUrl;
                }
            });
        } else {
            if (confirm('Apakah Anda yakin ingin logout dari sistem?')) {
                window.location.href = logoutUrl;
            }
        }
    });
})

function formatter(angka, prefix = null){
    var number_string = angka.replace(/[^\.\d]/g, '').toString(),
    split   		= number_string.split('.'),
    sisa 			= split[0].length % 3,
    rupiah 			= split[0].substr(0, sisa),
    ribuan 			= split[0].substr(sisa).match(/\d{3}/gi);

    if(ribuan){
        separator = sisa ? ',' : '';
        rupiah += separator + ribuan.join(',');
    }

    rupiah = split[1] != undefined ? rupiah + '.' + split[1] : rupiah;
    return prefix == undefined ? rupiah : (rupiah ? 'Rp ' + rupiah : '');
}