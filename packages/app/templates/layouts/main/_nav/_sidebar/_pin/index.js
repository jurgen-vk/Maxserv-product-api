import $ from 'jquery';
import cookie from '@core/storage/cookie';

$('.l_main__nav__sidebar__pin').found(function ($root) {
  const $sidebar = $('.l_main__nav__sidebar');

  $root.on('click', function () {
    const pinned = $(this).is('[data-pressed]');
    $sidebar.attr('data-pinned', !pinned);
    $(this).attr('data-pressed', !pinned ? '' : null);
    cookie.set('sidebarPinned', !pinned);
  });
});
