/**
 * SHF App — jQuery-based utilities
 * Replaces Alpine.js behaviors for Phase 1 views
 */
$(function () {

    // 0. Disable HTML5 browser validation bubbles — we use SHF.validateForm instead
    $('form').attr('novalidate', 'novalidate');

    /**
     * SHF.validateForm — lightweight client-side form validation.
     *
     * @param {jQuery} $form — the form (or container) element
     * @param {Object} rules — { fieldName: { required, maxlength, minlength, min, max, email, numeric, pattern, patternMsg, dateFormat, custom } }
     * @returns {boolean} true if valid
     *
     * Usage:
     *   if (!SHF.validateForm($('#myForm'), { customer_name: { required: true, maxlength: 255 } })) return;
     */
    window.SHF = window.SHF || {};

    SHF.validateForm = function ($form, rules) {
        // Clear previous errors
        $form.find('.shf-validation-error').remove();
        $form.find('.is-invalid').removeClass('is-invalid');

        var errors = [];

        $.each(rules, function (fieldName, rule) {
            var $field = $form.find('[name="' + fieldName + '"]');
            // For hidden+display pairs (amount fields), also check the raw hidden
            if (!$field.length) return;

            // For radio/checkbox groups, get checked value
            var val;
            if ($field.is(':radio')) {
                val = $form.find('[name="' + fieldName + '"]:checked').val() || '';
            } else if ($field.is(':checkbox') && !$field.is('[type="hidden"]')) {
                val = $field.is(':checked') ? $field.val() : '';
            } else {
                val = ($field.val() || '').toString().trim();
            }

            var label = rule.label || fieldName.replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
            var err = null;

            if (rule.required && !val) {
                err = label + ' is required.';
            } else if (val) {
                if (rule.maxlength && val.length > rule.maxlength) {
                    err = label + ' must not exceed ' + rule.maxlength + ' characters.';
                }
                if (!err && rule.minlength && val.length < rule.minlength) {
                    err = label + ' must be at least ' + rule.minlength + ' characters.';
                }
                if (!err && rule.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                    err = label + ' must be a valid email address.';
                }
                if (!err && rule.numeric) {
                    var numVal = parseFloat(val.replace(/,/g, ''));
                    if (isNaN(numVal)) {
                        err = label + ' must be a number.';
                    } else {
                        if (rule.min !== undefined && numVal < rule.min) {
                            err = label + ' must be at least ' + rule.min + '.';
                        }
                        if (!err && rule.max !== undefined && numVal > rule.max) {
                            err = label + ' must not exceed ' + SHF.formatIndianNumber(rule.max) + '.';
                        }
                    }
                }
                if (!err && rule.pattern && !rule.pattern.test(val)) {
                    err = rule.patternMsg || (label + ' format is invalid.');
                }
                if (!err && rule.dateFormat === 'd/m/Y' && !/^\d{2}\/\d{2}\/\d{4}$/.test(val)) {
                    err = label + ' must be in dd/mm/yyyy format.';
                }
            }
            // Custom validator function
            if (!err && rule.custom) {
                err = rule.custom(val, $field, $form);
            }

            if (err) {
                errors.push({ field: fieldName, message: err, $field: $field });
            }
        });

        if (errors.length) {
            // Inline style for error text — immune to CSS caching
            var errStyle = 'display:block;width:100%;margin-top:4px;font-size:0.8rem;color:#dc3545;font-weight:500;';

            $.each(errors, function (_, e) {
                var $field = e.$field;
                var $target = $field;

                // For radio groups, target the first radio's container
                if ($field.is(':radio')) {
                    $target = $form.find('[name="' + e.field + '"]').first().closest('.form-check, [class*="col-"]');
                }
                // For hidden fields (amount), target the visible display input
                if ($field.is(':hidden') && $field.siblings('.shf-amount-input').length) {
                    $target = $field.siblings('.shf-amount-input');
                }

                // Add red border
                $target.addClass('is-invalid');
                // Also set inline border for cached CSS fallback
                if (!$target.is(':radio') && !$target.is(':hidden')) {
                    $target.css({ 'border-color': '#dc3545', 'box-shadow': '0 0 0 3px rgba(220,53,69,0.15)' });
                }

                // Build error label with inline styles
                var $feedback = $('<div class="shf-validation-error" style="' + errStyle + '">' + e.message + '</div>');

                // Append to the parent column (most reliable) — fallback to after field
                var $col = $target.closest('[class*="col-"]');
                if ($col.length) {
                    $col.append($feedback);
                } else {
                    // If inside a positioned/grouped wrapper, place error outside it
                    var $wrapper = $target.closest('.position-relative, .input-group');
                    if ($wrapper.length) {
                        $wrapper.after($feedback);
                    } else {
                        $target.after($feedback);
                    }
                }
            });

            // Scroll to & focus first error field
            var $first = errors[0].$field;
            if ($first.is(':hidden') && $first.siblings('.shf-amount-input').length) {
                $first = $first.siblings('.shf-amount-input');
            }
            var $scrollTo = $first.closest('[class*="col-"]');
            if (!$scrollTo.length) $scrollTo = $first;
            if ($scrollTo.length && $scrollTo.is(':visible')) {
                $('html, body').animate({ scrollTop: $scrollTo.offset().top - 120 }, 300);
                $first.focus();
            }
            return false;
        }
        return true;
    };

    /**
     * SHF.validateBeforeAjax — validate, then post via AJAX.
     * Returns the jqXHR promise or false if validation fails.
     */
    SHF.validateBeforeAjax = function ($container, rules, url, data) {
        if (!SHF.validateForm($container, rules)) return false;
        return $.post(url, data);
    };

    // 0a. Clear validation errors on field input/change
    $(document).on('input change', '.is-invalid', function () {
        var $el = $(this);
        $el.removeClass('is-invalid').css({ 'border-color': '', 'box-shadow': '' });
        // Remove error text from parent column or nearby siblings
        $el.closest('[class*="col-"]').find('.shf-validation-error').remove();
        $el.siblings('.shf-validation-error').remove();
        $el.closest('.input-group').siblings('.shf-validation-error').remove();
        $el.closest('.shf-amount-wrap').siblings('.shf-validation-error').remove();
    });

    // 0b. Default radio auto-checks adjacent checkbox (for multi-select with default pattern)
    $(document).on('change', 'input[type="radio"]', function () {
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

    // 5. Global collapsible section handler (filters, etc.)
    $(document).on('click', '.shf-collapsible[data-target]', function () {
        var $this = $(this);
        var $target = $($this.data('target'));
        // If CSS class was hiding it, switch to jQuery-managed hidden state
        if ($target.hasClass('shf-filter-body-collapse')) {
            $target.removeClass('shf-filter-body-collapse').hide();
        }
        var isOpen = $target.is(':visible');
        $target.slideToggle(200);
        // Toggle open class for arrow rotation via CSS
        $this.toggleClass('shf-filter-open', !isOpen);
        // Show/hide filter count badge when collapsing/expanding
        var $badge = $this.find('.shf-filter-count');
        if ($badge.length) {
            $badge.toggleClass('shf-collapse-hidden', !isOpen);
        }
    });

    // 6. Auto-collapse filters on mobile (for dynamically shown tabs)
    window.shfCollapseFiltersOnMobile = function () {
        if (window.innerWidth >= 768) return;
        $('.shf-filter-collapse[data-target]').each(function () {
            var $target = $($(this).data('target'));
            if ($target.is(':visible') && !$target.is(':animated')) {
                $target.hide();
                $(this).removeClass('shf-filter-open');
            }
        });
    };

    // On desktop, ensure filters start expanded
    if (window.innerWidth >= 768) {
        $('.shf-filter-collapse').addClass('shf-filter-open');
        $('.shf-filter-body-collapse').removeClass('shf-filter-body-collapse');
    }

    // 7. Indian Amount Formatting — auto-init .shf-amount-input fields
    window.SHF = window.SHF || {};

    SHF.formatIndianNumber = function (num) {
        if (isNaN(num) || num === 0) return '0';
        var s = Math.floor(Math.abs(num)).toString();
        if (s.length <= 3) return s;
        var last3 = s.slice(-3);
        var rest = s.slice(0, -3);
        var formatted = rest.replace(/\B(?=(\d{2})+(?!\d))/g, ',');
        return formatted + ',' + last3;
    };

    SHF.numberToWordsEn = function (num) {
        if (num === 0) return 'Zero';
        var ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
            'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        var tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        function tw(n) { if (n < 20) return ones[n]; return tens[Math.floor(n / 10)] + (n % 10 ? ' ' + ones[n % 10] : ''); }
        function th(n) { if (n >= 100) return ones[Math.floor(n / 100)] + ' Hundred' + (n % 100 ? ' ' + tw(n % 100) : ''); return tw(n); }
        var r = '';
        if (num >= 10000000) { r += th(Math.floor(num / 10000000)) + ' Crore '; num %= 10000000; }
        if (num >= 100000) { r += tw(Math.floor(num / 100000)) + ' Lakh '; num %= 100000; }
        if (num >= 1000) { r += tw(Math.floor(num / 1000)) + ' Thousand '; num %= 1000; }
        if (num > 0) r += th(num);
        return r.trim() + ' Rupees';
    };

    SHF.numberToWordsGu = function (num) {
        if (num === 0) return 'શૂન્ય';
        var gu = [
            '', 'એક', 'બે', 'ત્રણ', 'ચાર', 'પાંચ', 'છ', 'સાત', 'આઠ', 'નવ',
            'દસ', 'અગિયાર', 'બાર', 'તેર', 'ચૌદ', 'પંદર', 'સોળ', 'સત્તર', 'અઢાર', 'ઓગણીસ',
            'વીસ', 'એકવીસ', 'બાવીસ', 'ત્રેવીસ', 'ચોવીસ', 'પચ્ચીસ', 'છવ્વીસ', 'સત્તાવીસ', 'અઠ્ઠાવીસ', 'ઓગણત્રીસ',
            'ત્રીસ', 'એકત્રીસ', 'બત્રીસ', 'તેંત્રીસ', 'ચોંત્રીસ', 'પાંત્રીસ', 'છત્રીસ', 'સાડત્રીસ', 'આડત્રીસ', 'ઓગણચાલીસ',
            'ચાલીસ', 'એકતાલીસ', 'બેતાલીસ', 'તેતાલીસ', 'ચુંમ્માલીસ', 'પિસ્તાલીસ', 'છેંતાલીસ', 'સુડતાલીસ', 'અડતાલીસ', 'ઓગણપચાસ',
            'પચાસ', 'એકાવન', 'બાવન', 'ત્રેપન', 'ચોપન', 'પંચાવન', 'છપ્પન', 'સત્તાવન', 'અઠ્ઠાવન', 'ઓગણસાઈઠ',
            'સાઈઠ', 'એકસઠ', 'બાસઠ', 'ત્રેસઠ', 'ચોસઠ', 'પાંસઠ', 'છાસઠ', 'સડસઠ', 'અડસઠ', 'ઓગણોસિત્તેર',
            'સિત્તેર', 'એકોતેર', 'બોંતેર', 'તોંતેર', 'ચુંમોતેર', 'પંચોતેર', 'છોંતેર', 'સીતોતેર', 'ઇઠોતેર', 'ઓગણએંસી',
            'એંસી', 'એક્યાસી', 'બ્યાસી', 'ત્યાસી', 'ચોરાસી', 'પંચાસી', 'છયાસી', 'સત્યાસી', 'અઠયાસી', 'નેવ્યાસી',
            'નેવું', 'એકણું', 'બાણું', 'ત્રાણું', 'ચોરાણું', 'પંચાણું', 'છન્નું', 'સતાણું', 'અઠ્ઠાણું', 'નવ્વાણું'
        ];
        function tw(n) { return gu[n] || ''; }
        function th(n) { if (n >= 100) return gu[Math.floor(n / 100)] + ' સો' + (n % 100 ? ' ' + tw(n % 100) : ''); return tw(n); }
        var r = '';
        if (num >= 10000000) { r += th(Math.floor(num / 10000000)) + ' કરોડ '; num %= 10000000; }
        if (num >= 100000) { r += tw(Math.floor(num / 100000)) + ' લાખ '; num %= 100000; }
        if (num >= 1000) { r += tw(Math.floor(num / 1000)) + ' હજાર '; num %= 1000; }
        if (num > 0) r += th(num);
        return r.trim() + ' રૂપિયા';
    };

    SHF.bilingualAmountWords = function (num) {
        return SHF.numberToWordsEn(num) + ' / ' + SHF.numberToWordsGu(num);
    };

    // Auto-init all .shf-amount-input fields
    SHF.initAmountFields = function () {
        $('.shf-amount-input').each(function () {
            if ($(this).data('shf-amount-bound')) return;
            $(this).data('shf-amount-bound', true);
            var $input = $(this);
            var $wrap = $input.closest('.shf-amount-wrap');
            var $hidden = $wrap.find('.shf-amount-raw');
            var $words = $wrap.find('[data-amount-words]');

            function update() {
                var raw = parseInt($input.val().replace(/[^0-9]/g, ''), 10);
                if (isNaN(raw) || $input.val().trim() === '') {
                    $input.val('');
                    $hidden.val('');
                    if ($words.length) $words.text('');
                } else {
                    $input.val(raw === 0 ? '0' : SHF.formatIndianNumber(raw));
                    $hidden.val(raw);
                    if ($words.length) $words.text(raw > 0 ? SHF.bilingualAmountWords(raw) : '');
                }
            }

            $input.on('input', update);
            // Init on load if value exists
            if ($input.val()) update();
        });
    };

    SHF.initAmountFields();

    // ─── Mobile FAB expand/collapse ────────────────────────────────────
    // Toggles body.shf-fab-open when the main FAB is tapped; backdrop and
    // Escape key close it. Pill-link clicks close and navigate normally.
    var $fabMain = $('#shfFabMain');
    if ($fabMain.length) {
        var closeFab = function () {
            $('body').removeClass('shf-fab-open');
            $fabMain.attr('aria-expanded', 'false');
        };
        $fabMain.on('click', function (e) {
            e.stopPropagation();
            var open = $('body').toggleClass('shf-fab-open').hasClass('shf-fab-open');
            $fabMain.attr('aria-expanded', open ? 'true' : 'false');
        });
        $(document).on('click', '.shf-fab-backdrop', closeFab);
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape' && $('body').hasClass('shf-fab-open')) {
                closeFab();
            }
        });
    }

});
