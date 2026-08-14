import $ from 'jquery';
import { Url } from '@app/utils/Url';

$('.c_ui_paginator').each(function () {
  const $root = $(this);
  const pageParam = $root.data('pageParam');
  const perPageParam = $root.data('perPageParam');

  $root.on('click', '.page-link', function (event) {
    event.preventDefault();
    const url = new Url();
    url.searchParams.set(pageParam, $(this).data('page'));
    url.push();
  });

  $root.on('change', '.per-page-select .select-input', function () {
    const url = new Url();
    url.searchParams.set(perPageParam, $(this).val());
    url.searchParams.delete(pageParam); // changing page size resets to page 1
    url.push();
  });
});
