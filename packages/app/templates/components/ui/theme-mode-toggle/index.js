import $ from 'jquery';
import cookie from '@core/storage/cookie';

const $root = $('.c_ui_theme-mode-toggle');
const $checkbox = $root.find('input.toggle');

if (!cookie.get('themeMode') && window.matchMedia('(prefers-color-scheme: dark)').matches) {
  $('html').attr('data-theme-mode', 'dark');
  $checkbox.prop('checked', true);
  cookie.set('themeMode', 'dark');
}

$checkbox.on('change', function () {
  const mode = this.checked ? 'dark' : 'light';

  $('html').attr('data-theme-mode', mode);
  cookie.set('themeMode', mode);
});