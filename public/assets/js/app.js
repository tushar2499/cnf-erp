// NAS Group ERP — Global JS

// CSRF for all AJAX
$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

// Init all Select2 on page
$(function () {
    $('[data-select2]').each(function () {
        $(this).select2({
            theme: 'bootstrap-5',
            width: '100%',
        });
    });

    // Sidebar toggle (mobile: slide in/out, desktop: collapse to icons)
    var isMobile = function () { return window.innerWidth <= 768; };

    // Restore desktop collapse state from localStorage
    if (!isMobile() && localStorage.getItem('sidebarCollapsed') === '1') {
        $('body').addClass('sidebar-collapsed');
    }

    $('#sidebarToggle').on('click', function () {
        if (isMobile()) {
            $('.sidebar').toggleClass('open');
        } else {
            $('body').toggleClass('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', $('body').hasClass('sidebar-collapsed') ? '1' : '0');
        }
    });
});

// Generic AJAX form handler
// Usage: <form data-ajax-form data-success="reload|redirect:/url|toast">
$(document).on('submit', '[data-ajax-form]', function (e) {
    e.preventDefault();
    const $form = $(this);
    const $btn  = $form.find('[type=submit]');
    const successAction = $form.data('success') || 'reload';

    $form.find('.is-invalid').removeClass('is-invalid');
    $form.find('.invalid-feedback').remove();

    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

    $.ajax({
        url:         $form.attr('action'),
        method:      $form.attr('method') || 'POST',
        data:        new FormData($form[0]),
        processData: false,
        contentType: false,
    })
    .done(function (res) {
        Swal.fire({ icon: 'success', title: res.message || 'Saved!', timer: 1800, showConfirmButton: false });
        if (successAction === 'reload') {
            setTimeout(() => location.reload(), 1900);
        } else if (successAction.startsWith('redirect:')) {
            setTimeout(() => (location.href = successAction.split(':')[1]), 1900);
        }
    })
    .fail(function (xhr) {
        if (xhr.status === 422) {
            const errors = xhr.responseJSON.errors;
            $.each(errors, function (field, messages) {
                const $input = $form.find('[name="' + field + '"]');
                $input.addClass('is-invalid');
                $input.after('<div class="invalid-feedback">' + messages[0] + '</div>');
            });
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Something went wrong.' });
        }
    })
    .always(function () {
        $btn.prop('disabled', false).html($btn.data('label') || 'Save');
    });
});

// Confirm delete
$(document).on('click', '[data-confirm-delete]', function (e) {
    e.preventDefault();
    const $el  = $(this);
    const url  = $el.attr('href') || $el.data('url');
    const name = $el.data('name') || 'this record';

    Swal.fire({
        title: 'Delete ' + name + '?',
        icon:  'warning',
        showCancelButton:  true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Yes, delete',
    }).then(function (result) {
        if (result.isConfirmed) {
            $.post(url, { _method: 'DELETE' }, function (res) {
                Swal.fire({ icon: 'success', title: res.message || 'Deleted!', timer: 1500, showConfirmButton: false });
                $el.closest('tr').fadeOut(400, function () { $(this).remove(); });
            }).fail(function () {
                Swal.fire({ icon: 'error', title: 'Failed to delete.' });
            });
        }
    });
});
