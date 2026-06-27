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

    // Click on the backdrop (outside the card) closes the modal
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeModal();
    });

    // Escape closes the modal
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' || e.keyCode === 27) closeModal();
    });

    // Keep Tab focus inside the modal while it is open
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
