import $ from 'jquery';

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
