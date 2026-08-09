import $ from 'jquery';

$('.c_ui_media-carousel').each(function () {
  const $root = $(this);
  const $image = $root.find('.main img');
  const $thumbs = $root.find('.thumb');
  const $thumbsRow = $root.find('.thumbs');
  let index = 0;

  function show(newIndex) {
    if ($thumbs.length === 0) return;

    if (newIndex < 0) newIndex = $thumbs.length - 1;
    if (newIndex >= $thumbs.length) newIndex = 0;
    index = newIndex;

    const $thumb = $thumbs.eq(index);
    const $img = $thumb.find('img');
    $image.attr('src', $img.attr('src'));
    $image.attr('alt', $img.attr('alt'));
    $thumbs.removeAttr('data-active');
    $thumb.attr('data-active', '');
  }

  $thumbs.on('click', function () {
    show($(this).data('index'));
  });

  $root.find('.arrow.prev').on('click', () => show(index - 1));
  $root.find('.arrow.next').on('click', () => show(index + 1));

  $root.find('[data-carousel-scroll]').on('click', function () {
    const direction = $(this).data('carouselScroll');
    const amount = direction === 'next' ? 120 : -120;
    $thumbsRow.animate({ scrollLeft: `+=${amount}` }, 200);
  });
});
