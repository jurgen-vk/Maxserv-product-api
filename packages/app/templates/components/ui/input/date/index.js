import $ from 'jquery';

$('.c_ui_input_date').found(async function ($root) {
  $root.find('input').on('input', function (event) {
    $(this).css('color', $(this).val() ? 'var(--color-text-1)' : 'var(--color-text-13)');
  });
});