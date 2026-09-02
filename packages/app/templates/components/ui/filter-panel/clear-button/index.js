import $ from 'jquery';
import { getFieldNames } from '#app/components/ui/filter-panel';
import { Url } from '@app/utils/Url';

//===[ ▼ Main ▼ ]==========================================================================================<editor-fold>

$(document).on('click', '.c_ui_filter-panel_clear-button .filter-clear', function (event) {
  event.preventDefault();
  const $button = $(this);
  const $root = $button.closest('.c_ui_filter-panel_clear-button');
  const filterPanelId = $root.data('filterPanel');
  const $filterPanel = $(`#${filterPanelId}`);

  clearFilter($filterPanel);
});

//===[ ▲ Main ▲ ]=========================================================================================</editor-fold>

//===[ ▼ Functions ▼ ]=====================================================================================<editor-fold>

function clearFilter($form) {
  const pageParam = $form.data('pageParam');

  $form[0].reset();
  $form.find('.input').fire('change');

  const url = new Url();
  getFieldNames($form).forEach((name) => url.searchParams.delete(name));
  url.searchParams.delete(pageParam);
  url.push();

  $form[0].hidePopover();
}

//===[ ▲ Functions ▲ ]====================================================================================</editor-fold>