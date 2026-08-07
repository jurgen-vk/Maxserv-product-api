import $ from 'jquery';

const $root = $('.nav-sidebar');

$root.on('click', function (event) {
  if (event.detail !== 0 && $root[0].contains(document.activeElement)) {
    document.activeElement.blur();
  }
});

// resizing past the breakpoint doesn't dismiss an open popover on its own —
// without this, the sidebar stays stuck in the top layer (native backdrop
// and all) even once desktop's own CSS says it should be back in normal flow
const underLg = window.matchMedia('(max-width: 64rem)');

underLg.addEventListener('change', (event) => {
  if (!event.matches && $root[0].matches(':popover-open')) {
    $root[0].hidePopover();
  }
});

// mobile only: popovers don't make the rest of the page inert the way
// <dialog>.showModal() does — a keyboard/screen-reader user could still
// reach the darkened .app-content behind the sidebar without this. This
// is the one place that reaches outside $root's own subtree, since
// .app-content is a separate, unrelated part of the page, not a
// descendant of the sidebar.
$root.on('toggle', (event) => {
  const isOpen = event.originalEvent.newState === 'open';
  const $content = $('.app-content');

  if (isOpen) {
    $content.attr('inert', '');
  } else {
    $content.removeAttr('inert');
  }
});
