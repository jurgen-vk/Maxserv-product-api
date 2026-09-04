import $ from 'jquery';
import TomSelect from 'tom-select';
import { str } from '@app/utils/str';

//===[ ▼ Main ▼ ]==========================================================================================<editor-fold>

const maxHeight = 200;

$('.c_ui_input_select').foundEach(function ($root) {
  initSelect($root, maxHeight);
});

$(document).watchEach('added subtree', '.c_ui_input_select', function ($root) {
  initSelect($root, maxHeight);
});

//===[ ▲ Main ▲ ]=========================================================================================</editor-fold>

//===[ ▼ Functions ▼ ]=====================================================================================<editor-fold>

//:::[ Init ]:::::
function initSelect($root, maxHeight) {
  const $select = $root.find('select');

  const select = $select.get(0);
  if (!select || select.tomselect) {
    return;
  }

  const {plugins: selectPlugins, ...selectSettings} = $select.data('settings') || {};
  const plugins = {
    change_listener: {}, ...selectPlugins
  };

  const variant = $root.data('variant');
  const randomString = str.random(16);

  const tomSelect = new TomSelect($select, {
    onInitialize: function () { onInitialize.call(this, randomString, variant); },
    render: {dropdown: function () { return renderDropdown.call(this, randomString); }},
    onDelete: function () { onDelete.call(this, selectSettings); },
    onDropdownOpen: function () { onDropdownOpen.call(this, maxHeight); },
    onDropdownClose: function () { onDropdownClose.call(this, maxHeight); },
    refreshThrottle: 0,
    sortField: 'text',
    ...selectSettings,
    plugins
  });
}

//:::[ Configure ]:::::
function onInitialize(randomString, variant) {
  const $wrapper = $(this.wrapper);
  const $control = $(this.control);
  $wrapper.attr('data-variant', variant);
  $control.addClass('input');

  $control.css({anchorName: `--ts-select-${randomString}`});

  if (this.settings.mode === 'single') {
    this.on('item_add', () => this.blur());
    this.on('clear', () => this.blur());
  }

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

//===[ ▲ Functions ▲ ]====================================================================================</editor-fold>
