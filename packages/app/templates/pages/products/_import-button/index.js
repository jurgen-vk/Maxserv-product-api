import $ from 'jquery';
import { runImport } from '@app/api/runImport';
import mercure from '@core/sse/mercure';

//===[ ▼ Main ▼ ]==========================================================================================<editor-fold>

$('.p_products__import-button').found(function ($root) {
  initButton($root);
});

//===[ ▲ Main ▲ ]=========================================================================================</editor-fold>

//===[ ▼ Functions ▼ ]=====================================================================================<editor-fold>

function initButton($root) {
  const $buttonImport = $root.find('.btn-import');

  $buttonImport.on('click', async () => {
    setImportingState($root, true);

    try {
      const importRecord = await runImport('products');
      watchImport($root, importRecord.id);
    } catch (error) {
      setImportingState($root, false);
    }
  });

  watchImport($root, $root.data('activeImportId'));
}

function setImportingState($root, isImporting) {
  $root.find('.btn-import').toggle(!isImporting);
  $root.find('.btn-importing').toggle(isImporting);
}

function watchImport($root, importId) {
  if (!importId) return;

  mercure.subscribe(`imports/${importId}`, (update, unsubscribe) => {
    if (update.status === 'completed' || update.status === 'failed') {
      unsubscribe();
      setImportingState($root, false);
    }
  });
}

//===[ ▲ Functions ▲ ]====================================================================================</editor-fold>