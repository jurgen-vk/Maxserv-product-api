import $ from 'jquery';
import debounce from '@app/utils/debounce';
import { Url } from '@app/utils/Url';

//===[ ▼ Main ▼ ]==========================================================================================<editor-fold>

const debounceTime = 500; // In milliseconds

$('.c_ui_input_search').foundEach(function ($root) {
  initSearch($root);
});

//===[ ▲ Main ▲ ]=========================================================================================</editor-fold>

//===[ ▼ Functions ▼ ]=====================================================================================<editor-fold>

function initSearch($root) {
  const $input = $root.find('.input');
  const $button = $root.find('.search-button');
  const $clear = $root.find('.clear');
  const name = $input.attr('name');
  const pageParam = $root.data('pageParam');

  $input.on('search', function () {
    triggerSearchDebounced.cancel();

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

  const triggerSearchDebounced = debounce(() => $input.trigger('search'), debounceTime);

  $input.on('input', function () {
    $clear.toggle($input.val() !== '');
    triggerSearchDebounced();
  });

  $button.on('click', () => $input.trigger('search'));

  $clear.on('click', () => {
    $input.val('').trigger('focus');
    $clear.toggle($input.val() !== '');
    $input.trigger('search');
  });

  $clear.toggle($input.val() !== '');
}

//===[ ▲ Functions ▲ ]====================================================================================</editor-fold>
