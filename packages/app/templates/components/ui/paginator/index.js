import $ from 'jquery';
import { Url } from '@app/utils/Url';

$(document).on('click', '.c_ui_paginator .page-link', function (event) {
  event.preventDefault();

  const pageParam = $(this).closest('.c_ui_paginator').data('pageParam');

  const url = new Url();
  url.searchParams.set(pageParam, $(this).data('page'));
  url.push();
});

$(document).on('change', '.c_ui_paginator .per-page-select .input', function () {
  const $paginator = $(this).closest('.c_ui_paginator');
  const pageParam = $paginator.data('pageParam');
  const perPageParam = $paginator.data('perPageParam');

  const url = new Url();
  url.searchParams.set(perPageParam, $(this).val());
  url.searchParams.delete(pageParam);
  url.push();
});
