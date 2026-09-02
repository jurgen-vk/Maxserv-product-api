import $ from 'jquery';
import { Url } from '@app/utils/Url';

//===[ ▼ Main ▼ ]==========================================================================================<editor-fold>

$(document).on('submit', '.c_ui_filter-panel', function (event) {
  event.preventDefault();
  submitFilter($(this));
});

//===[ ▲ Main ▲ ]=========================================================================================</editor-fold>

//===[ ▼ Functions ▼ ]=====================================================================================<editor-fold>

function getFieldNames($form) {
  return new Set([...$form[0].elements].filter((element) => element.name).map((element) => element.name));
}

function submitFilter($form) {
  const pageParam = $form.data('pageParam');
  const url = new Url();

  getFieldNames($form).forEach(
    (name) => url.searchParams.delete(name)
  );

  new FormData($form[0]).forEach((value, key) => {
    if (value && typeof value === 'string') {
      url.searchParams.append(key, value);
    }
  });

  url.searchParams.delete(pageParam);
  url.push();

  $form[0].hidePopover();
}

//===[ ▲ Functions ▲ ]====================================================================================</editor-fold>

export { getFieldNames };