import $ from 'jquery';
import TomSelect from 'tom-select';
import { str } from '@app/utils/str';

//===[ ▼ Main ▼ ]============================================================================================<editor-fold>

$('.c_ui_input_select').found(async function ($root) {
  const maxHeight = 200;
  initSelects(maxHeight);
  observeSelects(maxHeight);
});

//===[ ▲ Main ▲ ]===========================================================================================</editor-fold>

//===[ ▼ Functions ▼ ]============================================================================================<editor-fold>

//:::[ Init ]:::::
function initSelect(element, maxHeight) {
  const $element = $(element);
  const $select = $element.find('select');

  const select = $select.get(0);
  if (!select || select.tomselect) {
    return;
  }

  const variant = $element.data('variant');
  const settings = $select.data('settings');
  const randomString = str.random(16);

  const tom = new TomSelect($select, {
    onInitialize: function () { onInitialize.call(this, randomString, variant); },
    render: {dropdown: function () { return renderDropdown.call(this, randomString); }},
    onDelete: function () { onDelete.call(this, settings); },
    onDropdownOpen: function () { onDropdownOpen.call(this, maxHeight); },
    onDropdownClose: function () { onDropdownClose.call(this, maxHeight); },
    refreshThrottle: 0,
    ...settings
  });
}

function initSelects(maxHeight) {
  $(document).find('.c_ui_input_select').each(function () {
    initSelect(this, maxHeight);
  });
}

function observeSelects(maxHeight) {
  const observer = new MutationObserver((mutations) => {
    for (const mutation of mutations) {
      for (const node of mutation.addedNodes) {
        if (node.nodeType !== Node.ELEMENT_NODE) continue;

        if (node.matches('.c_ui_input_select')) {
          initSelect(node, maxHeight);
        }

        // const nestedSelects = node.querySelectorAll('.c_ui_input_select');
        // nestedSelects.forEach((select) => initSelect(select, maxHeight));

        $(node).find('.c_ui_input_select').each(function () {
          initSelect(this, maxHeight);
        });
      }
    }
  });

  observer.observe(document.body, {childList: true, subtree: true});
  return observer;
}

//:::[ Configure ]:::::
function onInitialize(randomString, variant) {
  const $wrapper = $(this.wrapper);
  const $control = $(this.control);
  $wrapper.attr('data-variant', variant);
  $control.addClass('input');

  $control.css({anchorName: `--ts-select-${randomString}`});

  const $popoverButton = $('<button>', {
    type: 'button',
    class: 'invisible',
    popovertarget: `popover-select-${randomString}`
  });

  $control.before($popoverButton);
  $popoverButton.append($control);
}

function renderDropdown(randomString) {
  const $dropdown = $('<div>', {
    id: `popover-select-${randomString}`,
    class: 'input',
    popover: '',
    css: {
      positionAnchor: `--ts-select-${randomString}`
    }
  });

  return $dropdown.prop('outerHTML');
}

function onDelete(settings) {
  return settings?.allowEmptyOption !== false;
}

function onDropdownOpen(maxHeight) {
  configureDropdownState.call(this, 'open', maxHeight);
}

function onDropdownClose(maxHeight) {
  configureDropdownState.call(this, 'close', maxHeight);
}

//:::[ Common ]:::::
function configureDropdownState(state, maxHeight) {
  const $wrapper = $(this.wrapper);
  const $dropdown = $(this.dropdown);

  $dropdown.css({'display': '', 'visibility': ''});

  // I'm doing the animating partially via js. Using the display grid trick
  // creates an inconsistency I can't get rid of in css, namely that 1fr measures
  // the entire height of the container even though it is capped with a max height.
  // This results in inconsistent animation timing, which is slightly noticeable,
  // so this fixes it, using as little js as possible. The js just makes measurements
  // and sets css variables that css can use to animate the dropdown.

  const currentState = $dropdown.attr('data-state') ?? 'closed';

  if (currentState !== 'opening' && currentState !== 'closing') {
    this.refreshOptions(false);
    $dropdown.attr('data-state', 'open');
    const naturalHeight = $dropdown.outerHeight();
    $dropdown.attr('data-state', currentState);

    const height = Math.min(naturalHeight, maxHeight);
    $dropdown.css('--DD-MAX-HEIGHT', `${maxHeight}px`);
    $dropdown.css('--DD-HEIGHT', `${height}px`);
  }

  const currentHeight = $dropdown.outerHeight();
  $dropdown.css('--DD-CURRENT-HEIGHT', `${currentHeight}px`);

  $dropdown.off('animationend animationcancel');

  if (state === 'open') {
    this.dropdown.showPopover();

    $dropdown.attr('data-state', 'opening');

    const area = $dropdown.css('position-area');
    $wrapper.attr('data-position-area', area);

    $dropdown.on('animationend animationcancel', function handler(event) {
      if (event.originalEvent.animationName !== 'selectOpen') return;
      $dropdown.off('animationend animationcancel', handler);
      $dropdown.attr('data-state', 'open');
    });
    return;
  }

  if (state === 'close') {
    $dropdown.css({'display': '', 'visibility': ''});
    $wrapper.attr('data-position-area', false);

    this.dropdown.hidePopover();

    $dropdown.attr('data-state', 'closing');

    const area = $dropdown.css('position-area');
    $wrapper.attr('data-position-area', area);

    $dropdown.on('animationend animationcancel', function handler(event) {
      if (event.originalEvent.animationName !== 'selectClose') return;
      $dropdown.off('animationend animationcancel', handler);
      $dropdown.attr('data-state', 'closed');
    });

    return;
  }
}

//===[ ▲ Functions ▲ ]===========================================================================================</editor-fold>
