import $ from 'jquery';
import { runImport } from '@app/api/runImport';

const $root = $('.import-buttons-wrapper');

const $buttonImport = $root.find('.btn-import');
const $buttonImporting = $root.find('.btn-importing');

$buttonImport.click(function () {
  $buttonImport.hide();
  $buttonImporting.show();

  runImport('products');
});