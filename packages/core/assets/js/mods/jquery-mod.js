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

$.fn.found = function (callback) {
  if (this.length > 0) {
    this.each(function (index, element) {
      callback.call(element, $(element), index);
    });
  }
  return this;
};

// A property, not a method — `$('.foo').exist`, not `$('.foo').exist()`.
Object.defineProperty($.fn, 'exist', {
  get() {
    return this.length > 0;
  },
});
