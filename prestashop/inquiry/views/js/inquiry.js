document.addEventListener('DOMContentLoaded', function () {
    var trigger = document.getElementById('inq-trigger');
    var overlay = document.getElementById('inq-modal-overlay');
    var modal = document.getElementById('inq-modal');
    var closeBtn = document.getElementById('inq-modal-close');

    if (!trigger || !overlay || !modal) return;

    var lastFocused = null;

    function openModal() {
        lastFocused = document.activeElement;
        overlay.classList.add('open');
        overlay.setAttribute('aria-hidden', 'false');
        trigger.setAttribute('aria-expanded', 'true');
        document.body.classList.add('inq-modal-open');

        var firstField = modal.querySelector('input, textarea');
        if (firstField) firstField.focus();
    }

    function closeModal() {
        if (!overlay.classList.contains('open')) return;
        overlay.classList.remove('open');
        overlay.setAttribute('aria-hidden', 'true');
        trigger.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('inq-modal-open');
        if (lastFocused && typeof lastFocused.focus === 'function') {
            lastFocused.focus();
        }
    }

    trigger.addEventListener('click', openModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);

    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeModal();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' || e.keyCode === 27) closeModal();
    });

    overlay.addEventListener('keydown', function (e) {
        if (e.key !== 'Tab' && e.keyCode !== 9) return;

        var focusable = modal.querySelectorAll(
            'a[href], button:not([disabled]), input:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
        );
        if (!focusable.length) return;

        var first = focusable[0];
        var last = focusable[focusable.length - 1];

        if (e.shiftKey && document.activeElement === first) {
            e.preventDefault();
            last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first.focus();
        }
    });
});

window.addEventListener('load', function () {
    if (!window.jQuery || !jQuery.fn || !jQuery.fn.select2) return;

    var $sel = jQuery('#inq-cat-select');
    if (!$sel.length || $sel.hasClass('select2-hidden-accessible')) return;

    $sel.select2({
        width: '100%',
        placeholder: $sel.data('placeholder'),
        dropdownCssClass: 'inq-select2-drop'
    });

    $sel.closest('.inq-cat-filter').addClass('inq-cat-enhanced');

    $sel.on('change', function () {
        if (this.form) this.form.submit();
    });
});
