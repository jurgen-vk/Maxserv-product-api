import $ from 'jquery';

//===[ ▼ Main ▼ ]==========================================================================================<editor-fold>

$('.l_main__nav__sidebar').found(function ($root) {
  initSidebar($root);
});

//===[ ▲ Main ▲ ]=========================================================================================</editor-fold>

//===[ ▼ Functions ▼ ]=====================================================================================<editor-fold>

function initSidebar($root) {
  $root.on('click', function (event) {
    if (event.detail !== 0 && $root[0].contains(document.activeElement)) {
      document.activeElement.blur();
    }
  });

  const underLg = window.matchMedia('(max-width: 64rem)');

  underLg.addEventListener('change', (event) => {
    if (!event.matches && $root[0].matches(':popover-open')) {
      $root[0].hidePopover();
    }
  });

  $root.on('toggle', (event) => {
    const isOpen = event.originalEvent.newState === 'open';
    const $content = $('.app-content');

    if (isOpen) {
      $content.attr('inert', '');
    } else {
      $content.removeAttr('inert');
    }
  });
}

//===[ ▲ Functions ▲ ]====================================================================================</editor-fold>




