import $ from 'jquery';

const GAP = 4; // px between trigger and panel
const VIEWPORT_MARGIN = 8; // px kept clear from the viewport edge

$('.c_ui_input_select').each(function () {
  const $root = $(this);
  const $trigger = $root.find('.trigger');
  const $panel = $root.find('.panel');
  const $search = $root.find('.select-search');
  const $options = $root.find('.options > li');
  const $input = $root.find('.select-input');
  const $value = $root.find('.value');

  // once shown, a popover is promoted to the top layer — its containing
  // block becomes the viewport, not this control, so CSS alone (short of
  // anchor positioning, which isn't universally supported yet) can't place
  // it relative to the trigger. Measuring here and flipping above when
  // there isn't room below mirrors what a native <select> does on its own.
  function positionPanel() {
    const trigger = $trigger[0].getBoundingClientRect();
    const panel = $panel[0];
    const panelHeight = panel.offsetHeight;
    const panelWidth = panel.offsetWidth;

    const fitsBelow = trigger.bottom + GAP + panelHeight <= window.innerHeight - VIEWPORT_MARGIN;
    const opensAbove = !fitsBelow && trigger.top - GAP - panelHeight >= VIEWPORT_MARGIN;

    panel.style.top = opensAbove ? '' : `${trigger.bottom + GAP}px`;
    panel.style.bottom = opensAbove ? `${window.innerHeight - trigger.top + GAP}px` : '';

    const left = Math.min(trigger.left, window.innerWidth - panelWidth - VIEWPORT_MARGIN);
    panel.style.left = `${Math.max(left, VIEWPORT_MARGIN)}px`;
  }

  $panel.on('toggle', (event) => {
    // jQuery's normalized event object doesn't carry over non-standard
    // native properties like ToggleEvent.newState — has to come from
    // originalEvent, or isOpen is always undefined-ly false
    const isOpen = event.originalEvent.newState === 'open';
    if (isOpen) {
      positionPanel();
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
