import $ from 'jquery';

$.fn.outerHtml = function (value) {
  if (value === undefined) {
    return this[0] ? this[0].outerHTML : undefined;
  }

  if (typeof value === 'string') {
    return this.each(function () {
      this.outerHTML = value;
    });
  }

  return this.replaceWith(value);
};

$.fn.innerHtml = function (value) {
  if (value === undefined) {
    return this.html();
  }

  return this.html(value);
};
