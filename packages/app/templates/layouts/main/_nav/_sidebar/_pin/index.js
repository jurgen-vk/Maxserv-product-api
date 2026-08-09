import $ from 'jquery';
import cookie from '@core/storage/cookie';

const $root = $('.l_main__nav__sidebar__pin');

// reaches .l_main__nav__sidebar directly (not a descendant) — the pin
// button's whole job is controlling the sidebar it lives in
const $sidebar = $('.l_main__nav__sidebar');

$root.on('click', function () {
  const pinned = $(this).is('[data-pressed]');
  $sidebar.attr('data-pinned', !pinned);
  $(this).attr('data-pressed', !pinned ? '' : null);
  cookie.set('sidebarPinned', !pinned);
});
