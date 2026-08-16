import $ from 'jquery';

function initSelect(element) {
  const $root = $(element);
  const $select = $root.find('.select-input');

  if ($select.hasClass('select2-hidden-accessible')) {
    return;
  }

  const variant = $select.data('variant') || 'primary';
  const searchable = String($select.data('searchable')) === '1';
  const hasPlaceholder = Boolean($select.data('placeholder'));

  const $topLayerAncestor = $select.closest('[popover], dialog');
  const dropdownParent = $topLayerAncestor.length
    ? $topLayerAncestor
    : $(document.body);

  const animateDropdown = JSON.parse($select.attr('data-animate-dropdown') || '{}');

  $select.select2({
    dropdownParent,
    minimumResultsForSearch: searchable ? 0 : -1,
    allowClear: hasPlaceholder,
    containerCssClass: 'c_ui_input_select__container',
    dropdownCssClass: 'c_ui_input_select__dropdown',
    animateDropdown
  });

  function getDropdownEl() {
    return $select.data('select2').$dropdown.find('.c_ui_input_select__dropdown');
  }

  getDropdownEl().attr('data-variant', variant);

  getDropdownEl().on('scroll', function () {
    this.scrollTop = 0;
  });
}

function initSelects(root = document) {
  $(root).find('.c_ui_input_select').each(function () {
    initSelect(this);
  });
}

initSelects();

new MutationObserver((mutations) => {
  for (const mutation of mutations) {
    for (const node of mutation.addedNodes) {
      if (node.nodeType !== Node.ELEMENT_NODE) continue;

      if (node.matches('.c_ui_input_select')) {
        initSelect(node);
      } else {
        initSelects(node);
      }
    }
  }
}).observe(document.body, {childList: true, subtree: true});

// select2 only mirrors its underlying <select>'s value on 'change' — a
// native form.reset() updates the raw element but never fires that, so
// select2's own visual state would otherwise go stale after Clear filters.
$(document).on('reset', 'form', function () {
  $(this).find('.select-input').trigger('change');
});
