import $ from 'jquery';

$('.c_ui_input_range-slider').each(function () {
  const $root = $(this);
  const $minSlider = $root.find('[data-range-slider="min"]');
  const $maxSlider = $root.find('[data-range-slider="max"]');
  const $minNumber = $root.find('[data-range-number="min"]');
  const $maxNumber = $root.find('[data-range-number="max"]');
  const $minHidden = $root.find('[data-range-hidden="min"]');
  const $maxHidden = $root.find('[data-range-hidden="max"]');
  const $fill = $root.find('.fill');

  const min = parseFloat($minSlider.attr('min'));
  const max = parseFloat($minSlider.attr('max'));

  function percent(value) {
    return ((value - min) / (max - min)) * 100;
  }

  function updateFill() {
    const minValue = parseFloat($minSlider.val());
    const maxValue = parseFloat($maxSlider.val());
    $fill.css({ left: `${percent(minValue)}%`, right: `${100 - percent(maxValue)}%` });
  }

  function setMin(value) {
    value = Math.min(Math.max(value, min), parseFloat($maxSlider.val()));
    $minSlider.val(value);
    $minNumber.val(value);
    $minHidden.val(value);
  }

  function setMax(value) {
    value = Math.max(Math.min(value, max), parseFloat($minSlider.val()));
    $maxSlider.val(value);
    $maxNumber.val(value);
    $maxHidden.val(value);
  }

  $minSlider.on('input', function () {
    setMin(parseFloat(this.value));
    updateFill();
  });

  $maxSlider.on('input', function () {
    setMax(parseFloat(this.value));
    updateFill();
  });

  $minNumber.on('change', function () {
    setMin(parseFloat(this.value) || min);
    updateFill();
  });

  $maxNumber.on('change', function () {
    setMax(parseFloat(this.value) || max);
    updateFill();
  });

  updateFill();
});
