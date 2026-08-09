import $ from 'jquery';
import { runImport } from '@app/api/runImport';

const $root = $('.p_products__import-button');

const $buttonImport = $root.find('.btn-import');
const $buttonImporting = $root.find('.btn-importing');

$buttonImport.click(function () {
  $buttonImport.hide();
  $buttonImporting.show();

  runImport('products');
});