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

    $(document).on('click', '.tm-maroon-sidebar .sidebar-link', function() {
        if ($(window).width() < 992) {
            closeMobileSidebar();
        }
    });
})

function formatter(angka, prefix = null){
    var number_string = angka.replace(/[^\.\d]/g, '').toString(),
    split   		= number_string.split('.'),
    sisa     		= split[0].length % 3,
    rupiah     		= split[0].substr(0, sisa),
    ribuan     		= split[0].substr(sisa).match(/\d{3}/gi);
    // tambahkan titik jika yang di input sudah menjadi angka ribuan
    if(ribuan){
        separator = sisa ? ',' : '';
        rupiah += separator + ribuan.join(',');
    }

    rupiah = split[1] != undefined ? rupiah + '.' + split[1] : rupiah;
    return prefix == undefined ? rupiah : (rupiah ? prefix + rupiah : '');
}