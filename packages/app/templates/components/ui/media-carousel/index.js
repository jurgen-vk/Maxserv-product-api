import $ from 'jquery';

$('.c_ui_media-carousel').each(function () {
  const $root = $(this);
  const $slides = $root.find('.main .slides');
  const $slideEls = $slides.find('.slide');
  const $thumbs = $root.find('.thumb');
  const $thumbsRow = $root.find('.thumbs');
  const $scrollPrev = $root.find('.thumbs-scroll.prev');
  const $scrollNext = $root.find('.thumbs-scroll.next');
  const $mainPrev = $root.find('.main .arrow.prev');
  const $mainNext = $root.find('.main .arrow.next');
  let index = 0;

  function setActive(newIndex) {
    index = newIndex;
    $thumbs.removeAttr('data-active');

    const $thumb = $thumbs.eq(index);
    $thumb.attr('data-active', '');
    $thumb[0].scrollIntoView({behavior: 'smooth', inline: 'nearest', block: 'nearest'});
  }

  function slideTo(newIndex) {
    if ($slideEls.length === 0) return;

    newIndex = Math.max(0, Math.min($slideEls.length - 1, newIndex));
    // the target is already known here, so update the active thumbnail immediately
    // rather than waiting for scrollend — that only fires once the smooth-scroll
    // animation (plus any snap settling) has fully finished, which lags well behind
    // the click itself
    setActive(newIndex);
    $slideEls.eq(newIndex)[0].scrollIntoView({behavior: 'smooth', inline: 'start', block: 'nearest'});
  }

  // scrollend is still needed for the user's own free drag — there we don't know
  // the destination in advance, so this is the only point we can sync from. For
  // slideTo()-driven navigation this just confirms what's already set, as a no-op.
  function onMainSlidesSettled() {
    if ($slides.length === 0) return;

    const el = $slides[0];
    const width = el.clientWidth;
    if (width === 0) return;

    const settledIndex = Math.round(el.scrollLeft / width);
    if (settledIndex !== index) setActive(settledIndex);
  }

  function updateMainArrows() {
    if ($slides.length === 0) return;

    const el = $slides[0];
    const maxScroll = el.scrollWidth - el.clientWidth;

    $mainPrev.toggle(el.scrollLeft > 1);
    $mainNext.toggle(el.scrollLeft < maxScroll - 1);
  }

  function updateScrollArrows() {
    if ($thumbsRow.length === 0) return;

    const el = $thumbsRow[0];
    const maxScroll = el.scrollWidth - el.clientWidth;

    $scrollPrev.toggle(el.scrollLeft > 1);
    $scrollNext.toggle(el.scrollLeft < maxScroll - 1);
  }

  $thumbs.on('click', function () {
    slideTo($(this).data('index'));
  });

  $mainPrev.on('click', () => slideTo(index - 1));
  $mainNext.on('click', () => slideTo(index + 1));

  $root.find('[data-carousel-scroll]').on('click', function () {
    const direction = $(this).data('carouselScroll');
    const amount = direction === 'next' ? 160 : -160;
    // relies on the .thumbs `scroll-behavior: smooth` CSS for the animation itself —
    // jQuery's own .animate() had a noticeable startup delay compared to native scrolling
    $thumbsRow[0].scrollLeft += amount;
  });

  $slides.on('scrollend', onMainSlidesSettled);
  $slides.on('scroll', updateMainArrows);
  $thumbsRow.on('scroll', updateScrollArrows);
  $(window).on('resize', () => {
    updateScrollArrows();
    updateMainArrows();
  });

  updateScrollArrows();
  updateMainArrows();
});