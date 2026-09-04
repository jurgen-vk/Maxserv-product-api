import $ from 'jquery';
import { Url } from '@app/utils/Url';

//===[ ▼ Main ▼ ]==========================================================================================<editor-fold>

$(document).on('click', '.c_ui_table__sortable-th .sort-link', function (event) {
  event.preventDefault();

  const pageParam = $(this).closest('.c_ui_table').data('pageParam');
  const currentDir = $(this).data('dir');
  const nextDir = currentDir === 'ASC' ? 'DESC' : 'ASC';

  const url = new Url();
  url.searchParams.set('sortBy', $(this).data('column'));
  url.searchParams.set('sortDir', nextDir);
  url.searchParams.delete(pageParam);
  url.push();
});

//===[ ▲ Main ▲ ]=========================================================================================</editor-fold>


