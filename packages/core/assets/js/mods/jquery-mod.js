import $ from 'jquery';

$.fn.hide = function () {
  return this.addClass('hidden').attr('aria-hidden', 'true');
};

$.fn.show = function () {
  return this.removeClass('hidden').removeAttr('aria-hidden');
};

$.fn.toggle = function (state) {
  const shouldShow = state !== undefined ? state : this.hasClass('hidden');
  return shouldShow ? this.show() : this.hide();
};
