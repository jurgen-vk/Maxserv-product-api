import $ from 'jquery';
import { Url } from '@app/utils/Url';

function fieldNames($form) {
  return new Set([...$form[0].elements].filter((element) => element.name).map((element) => element.name));
}

function clearFilters($form) {
  const pageParam = $form.data('pageParam');

  $form[0].reset();
  $form.find('.input').trigger('change');

  const url = new Url();
  fieldNames($form).forEach((name) => url.searchParams.delete(name));
  url.searchParams.delete(pageParam);
  url.push();

  document.getElementById($form.attr('id')).hidePopover();
}

$('.c_ui_filter-panel').each(function () {
  const $form = $(this);
  const pageParam = $form.data('pageParam');
  const popoverId = $form.attr('id');

  $form.on('submit', function (event) {
    event.preventDefault();

    const url = new Url();
    fieldNames($form).forEach((name) => url.searchParams.delete(name));

    new FormData(this).forEach((value, key) => {
      if (value) url.searchParams.append(key, value);
    });

    url.searchParams.delete(pageParam);
    url.push();

    document.getElementById(popoverId).hidePopover();
  });

  $form.find('.filter-clear').on('click', function (event) {
    event.preventDefault();
    clearFilters($form);
  });
});

// A "Clear filters" trigger can also live outside the panel entirely — e.g. a
// table's own empty state — pointing at its panel via data-clear-target,
// the same by-id-reference convention popovertarget already uses on this page.
$(document).on('click', '.filter-clear[data-clear-target]', function (event) {
  event.preventDefault();
  clearFilters($(`#${$(this).data('clearTarget')}`));
});
