import $ from 'jquery';

$.fn.hide = function () {
  return this.prop('hidden', true);
};

$.fn.show = function () {
  return this.prop('hidden', false);
};

$.fn.toggle = function (state) {
  const shouldShow = state !== undefined ? state : this.prop('hidden');
  return shouldShow ? this.show() : this.hide();
};
