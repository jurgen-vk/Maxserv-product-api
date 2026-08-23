import $ from 'jquery';
import debounce from '@app/utils/debounce';
import { Url } from '@app/utils/Url';

$('.c_ui_input_search').each(function () {
  const $root = $(this);
  const $input = $root.find('.input');
  const $button = $root.find('.search-button');
  const $clear = $root.find('.clear');
  const name = $input.attr('name');
  const pageParam = $root.data('pageParam');

  function syncClearButton() {
    $clear.toggle($input.val() !== '');
  }

  // the one place that actually updates the URL — reached either natively
  // (the browser fires 'search' on Enter for free, type="search" already
  // does this) or by the three manual triggers below
  $input.on('search', function () {
    triggerSearchDebounced.cancel(); // a pending debounce shouldn't fire again after an explicit trigger already ran

    const url = new Url();
    const value = $(this).val();

    if (value) {
      url.searchParams.set(name, value);
    } else {
      url.searchParams.delete(name);
    }
    url.searchParams.delete(pageParam);
    url.push();
  });

  const triggerSearchDebounced = debounce(() => $input.trigger('search'), 500);

  $input.on('input', function () {
    syncClearButton();
    triggerSearchDebounced();
  });

  $button.on('click', () => $input.trigger('search'));

  $clear.on('click', () => {
    $input.val('').trigger('focus');
    syncClearButton();
    $input.trigger('search');
  });

  syncClearButton();
});
