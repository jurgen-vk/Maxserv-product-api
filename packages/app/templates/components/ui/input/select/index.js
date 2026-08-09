import $ from 'jquery';

$('.c_ui_input_select').each(function () {
  const $root = $(this);
  const $trigger = $root.find('.trigger');
  const $panel = $root.find('.panel');
  const $search = $root.find('.select-search');
  const $options = $root.find('.options > li');
  const $input = $root.find('.select-input');
  const $value = $root.find('.value');

  $panel.on('toggle', (event) => {
    const isOpen = event.newState === 'open';
    if (isOpen) {
      $search.trigger('focus');
    } else {
      $search.val('');
      $options.show();
    }
  });

  $search.on('input', function () {
    const query = $(this).val().toLowerCase();
    $options.each(function () {
      const $option = $(this);
      $option.toggle($option.text().toLowerCase().includes(query));
    });
  });

  $options.on('click', function () {
    const $option = $(this);
    $input.val($option.data('value')).trigger('change');
    $value.text($option.text().trim());
    $options.removeAttr('data-selected');
    $option.attr('data-selected', '');
    $panel.get(0).hidePopover();
  });
});
