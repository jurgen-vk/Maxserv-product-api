import $ from 'jquery';

//===[ ▼ Main ▼ ]==========================================================================================<editor-fold>

$(document).on('change', '.c_ui_input_date input', function () {
  $(this).css('color', $(this).val() ? 'var(--color-text-1)' : 'var(--color-text-13)');
});

//===[ ▲ Main ▲ ]=========================================================================================</editor-fold>
