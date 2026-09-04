import $ from 'jquery';

//===[ ▼ Classes ▼ ]=======================================================================================<editor-fold>

class RangeSlider {
  constructor(el) {
    this.#$root = $(el);

    this.#$minSlider = this.#$root.find('[data-range-slider="min"]');
    this.#$maxSlider = this.#$root.find('[data-range-slider="max"]');
    this.#$minNumber = this.#$root.find('[data-range-number="min"] input');
    this.#$maxNumber = this.#$root.find('[data-range-number="max"] input');
    this.#$minHidden = this.#$root.find('[data-range-hidden="min"]');
    this.#$maxHidden = this.#$root.find('[data-range-hidden="max"]');
    this.#$fill = this.#$root.find('.fill');

    this.#min = parseFloat(this.#$minSlider.attr('min'));
    this.#max = parseFloat(this.#$minSlider.attr('max'));

    this.#bindEvents();
    this.#updateFill();
  }

  #$root;
  #$minSlider;
  #$maxSlider;
  #$minNumber;
  #$maxNumber;
  #$minHidden;
  #$maxHidden;
  #$fill;
  #min;
  #max;

  get min() {
    return parseFloat(this.#$minHidden.val());
  }

  set min(value) {
    this.#setMin(parseFloat(value));
    this.#updateFill();
  }

  get max() {
    return parseFloat(this.#$maxHidden.val());
  }

  set max(value) {
    this.#setMax(parseFloat(value));
    this.#updateFill();
  }

  get bounds() {
    return {min: this.#min, max: this.#max};
  }

  #bindEvents() {
    this.#$minSlider.on('input', (e) => {
      this.#setMin(parseFloat(e.target.value));
      this.#updateFill();
    });

    this.#$maxSlider.on('input', (e) => {
      this.#setMax(parseFloat(e.target.value));
      this.#updateFill();
    });

    this.#$minNumber.on('input change', (e) => {
      this.#setMin(parseFloat(e.target.value) || this.#min);
      this.#updateFill();
    });

    this.#$maxNumber.on('input change', (e) => {
      this.#setMax(parseFloat(e.target.value) || this.#max);
      this.#updateFill();
    });
  }

  #percent(value) {
    return ((value - this.#min) / (this.#max - this.#min)) * 100;
  }

  #updateFill() {
    this.#$fill.css({
      left: `${this.#percent(this.min)}%`,
      right: `${100 - this.#percent(this.max)}%`
    });
  }

  #setMin(value) {
    value = Math.min(Math.max(value, this.#min), this.max);
    this.#$minHidden.val(value);
    this.#$minSlider.val(value);
    this.#$minNumber.val(value);
  }

  #setMax(value) {
    value = Math.max(Math.min(value, this.#max), this.min);
    this.#$maxHidden.val(value);
    this.#$maxSlider.val(value);
    this.#$maxNumber.val(value);
  }
}

//===[ ▲ Classes ▲ ]======================================================================================</editor-fold>

export {
  RangeSlider
};
