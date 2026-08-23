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

$.fn.outerHtml = function (value) {
  if (value === undefined) {
    return this[0] ? this[0].outerHTML : undefined;
  }

  return this.each(function () {
    this.outerHTML = value;
  });
};

$.fn.innerHtml = function (value) {
  if (value === undefined) {
    return this.html();
  }

  return this.html(value);
};

$.fn.found = function (callback) {
  if (this.length > 0) {
    this.each(function (index, element) {
      callback.call(element, $(element), index);
    });
  }
  return this;
};

Object.defineProperty($.fn, 'exist', {
  get() {
    return this.length > 0;
  }
});
