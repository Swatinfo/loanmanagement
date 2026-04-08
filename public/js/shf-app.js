/**
 * SHF App — jQuery-based utilities
 * Replaces Alpine.js behaviors for Phase 1 views
 */
$(function () {

    // 0. Disable HTML5 validation on all forms — use server-side + inline errors
    $('form').attr('novalidate', 'novalidate');

    // 0b. Default radio auto-checks adjacent checkbox (for multi-select with default pattern)
    $(document).on('change', 'input[type="radio"]', function() {
        var $cb = $(this).closest('label').find('input[type="checkbox"]');
        if ($cb.length && !$cb.is(':checked')) {
            $cb.prop('checked', true);
        }
    });

    // 0c. Auto-expand textareas (fallback for browsers without field-sizing: content)
    if (!CSS.supports('field-sizing', 'content')) {
        function autoExpand(el) {
            el.style.height = 'auto';
            el.style.height = el.scrollHeight + 'px';
        }
        $(document).on('input', 'textarea.shf-input, textarea.shf-input-sm', function () {
            autoExpand(this);
        });
        $('textarea.shf-input, textarea.shf-input-sm').each(function () { autoExpand(this); });
    }

    // 1. Toast auto-dismiss
    $('.shf-toast-wrapper [data-auto-dismiss]').each(function () {
        var $toast = $(this);
        var delay = parseInt($toast.data('auto-dismiss'), 10) || 5000;
        // Fade in
        $toast.css({ opacity: 0, transform: 'translateY(20px)' })
              .animate({ opacity: 1 }, 300)
              .css('transform', 'translateY(0)');
        // Auto-dismiss after delay
        setTimeout(function () {
            $toast.animate({ opacity: 0 }, 300, function () {
                $toast.remove();
            });
        }, delay);
    });

    // Toast close button
    $(document).on('click', '.shf-toast-close', function () {
        var $toast = $(this).closest('[data-auto-dismiss]');
        $toast.animate({ opacity: 0 }, 200, function () {
            $toast.remove();
        });
    });

    // 2. Password toggle — .shf-password-toggle
    $(document).on('click', '.shf-password-toggle', function () {
        var targetId = $(this).data('target');
        var $input = $('#' + targetId);
        var isPassword = $input.attr('type') === 'password';
        $input.attr('type', isPassword ? 'text' : 'password');
        // Toggle eye icons
        $(this).find('.shf-eye-open').toggle(!isPassword);
        $(this).find('.shf-eye-closed').toggle(isPassword);
    });

    // 3. "Saved" message fade — .shf-saved-msg
    $('.shf-saved-msg').each(function () {
        var $msg = $(this);
        setTimeout(function () {
            $msg.fadeOut(400, function () { $msg.remove(); });
        }, 2000);
    });

    // 4. Modal auto-show on page load (for validation errors)
    $('[data-bs-show-on-load="true"]').each(function () {
        var modal = new bootstrap.Modal(this);
        modal.show();
    });

    // 5. SweetAlert confirm for delete forms (.shf-confirm-delete)
    $(document).on('submit', '.shf-confirm-delete', function (e) {
        e.preventDefault();
        var form = this;
        Swal.fire({
            title: $(form).data('confirm-title') || 'Are you sure?',
            text: $(form).data('confirm-text') || 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'Cancel'
        }).then(function (result) {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

});
