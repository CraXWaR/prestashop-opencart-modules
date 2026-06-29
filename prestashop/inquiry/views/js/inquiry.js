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

document.addEventListener('DOMContentLoaded', function () {
    var track = document.getElementById('inq-cat-track');
    var btnPrev = document.getElementById('inq-cat-prev');
    var btnNext = document.getElementById('inq-cat-next');
    if (!track || !btnPrev || !btnNext) return;

    function updateArrows() {
        btnPrev.classList.toggle('inq-cat-arrow-hidden', track.scrollLeft < 8);
        btnNext.classList.toggle('inq-cat-arrow-hidden',
            track.scrollLeft + track.clientWidth >= track.scrollWidth - 8);
    }

    function pillLeft(pill) {
        return pill.getBoundingClientRect().left
            - track.getBoundingClientRect().left
            + track.scrollLeft;
    }
    function pills() {
        return Array.prototype.slice.call(track.querySelectorAll('.inq-cat-pill'));
    }

    btnNext.addEventListener('click', function () {
        var list = pills();
        for (var i = 0; i < list.length; i++) {
            if (pillLeft(list[i]) > track.scrollLeft + 1) {
                track.scrollTo({ left: pillLeft(list[i]), behavior: 'smooth' });
                return;
            }
        }
    });

    btnPrev.addEventListener('click', function () {
        var list = pills();
        var target = 0;
        for (var i = 0; i < list.length; i++) {
            if (pillLeft(list[i]) < track.scrollLeft - 1) {
                target = pillLeft(list[i]);
            }
        }
        track.scrollTo({ left: target, behavior: 'smooth' });
    });

    track.addEventListener('scroll', updateArrows, { passive: true });
    window.addEventListener('resize', updateArrows);
    updateArrows();
});