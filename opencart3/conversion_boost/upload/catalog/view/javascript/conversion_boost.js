function initCountdownTimer () {
    let countdown = $('.cbp-countdown');

    if (!countdown.length) {
        return;
    }

    let timer = setInterval(() => {
        if (remainingTime <= 0) {
            clearInterval(timer);
            countdown.hide();
            return;
        }

        let days = Math.floor(remainingTime / 86400);
        let hours = Math.floor((remainingTime % 86400) / 3600);
        let minutes = Math.floor((remainingTime % 3600) / 60);
        let seconds = Math.floor(remainingTime % 60);

        if (days < 1) {
            $('.cbp-days, .cbp-days + .cbp-separator').hide();
        } else {
            $('.cbp-days .cbp-count').text(days);
        }

        if ($('.cbp-countdown-inner').hasClass('cbp-flip')) {
            if (days >= 1) {
                flipCard($('.cbp-days .cbp-count'), String(days));
            }
            flipCard($('.cbp-hours .cbp-count'), String(hours).padStart(2, '0'));
            flipCard($('.cbp-minutes .cbp-count'), String(minutes).padStart(2, '0'));
            flipCard($('.cbp-seconds .cbp-count'), String(seconds).padStart(2, '0'));
        } else {
            if (days < 1) {
                $('.cbp-days, .cbp-days + .cbp-separator').hide();
            } else {
                $('.cbp-days .cbp-count').text(days);
            }
            $('.cbp-hours .cbp-count').text(String(hours).padStart(2, '0'));
            $('.cbp-minutes .cbp-count').text(String(minutes).padStart(2, '0'));
            $('.cbp-seconds .cbp-count').text(String(seconds).padStart(2, '0'));
        }

        remainingTime--;
    }, 1000)
}

function flipCard($element, newValue) {
    let current = $element.text();
    if (current === newValue) return;

    $element.attr('data-next', newValue);
    $element.closest('.cbp-block').addClass('flip');

    setTimeout(function() {
        $element.text(newValue);
        $element.closest('.cbp-block').removeClass('flip');
    }, 300);
}

function initStickyBar() {
    let stickyBar = $('.cbp-sticky-bar');

    if (!stickyBar.length) {
        return;
    }

    let addToCartBtn = $('#button-cart');

    if (!addToCartBtn.length) {
        return;
    }

    let ticking = false;

    $(window).on('scroll', function() {
        if (!ticking) {
            requestAnimationFrame(function() {
                let btnBottom = addToCartBtn[0].getBoundingClientRect().bottom;
                if (btnBottom < 0) {
                    stickyBar.addClass('cbp-visible');
                } else {
                    stickyBar.removeClass('cbp-visible');
                }
                ticking = false;
            });
            ticking = true;
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    initStickyBar();
});