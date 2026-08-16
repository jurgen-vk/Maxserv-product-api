import $ from 'jquery';
import { Url } from '@app/utils/Url';

$(document).on('click', '.sort-link', function (event) {
  event.preventDefault();

  const pageParam = $(this).closest('.p_products__products-table').find('.c_ui_paginator').data('pageParam');

  const url = new Url();
  url.searchParams.set('sort', $(this).data('column'));
  url.searchParams.set('order', $(this).data('order'));
  url.searchParams.delete(pageParam);
  url.push();
});
