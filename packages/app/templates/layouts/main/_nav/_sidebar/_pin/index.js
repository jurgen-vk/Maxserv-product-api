import $ from 'jquery';
import cookie from '@core/storage/cookie';

const $root = $('.sidebar-pin');
const $sidebar = $('.nav-sidebar');

$root.on('click', function () {
  const pinned = $(this).attr('aria-pressed') === 'true';
  $sidebar.attr('data-pinned', !pinned);
  $(this).attr('aria-pressed', !pinned);
  cookie.set('sidebarPinned', !pinned);
});
