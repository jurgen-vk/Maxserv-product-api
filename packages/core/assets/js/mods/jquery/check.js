import $ from 'jquery';

$.fn.found = function (callback) {
  if (this.length > 0) {
    callback.call(this, this);
  }
  return this;
};

$.fn.foundEach = function (callback) {
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
