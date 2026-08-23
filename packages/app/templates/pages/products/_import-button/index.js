import $ from 'jquery';
import { runImport } from '@app/api/runImport';
import mercure from '@core/sse/mercure';

$('.p_products__import-button').found(async function ($root) {
  const $buttonImport = $root.find('.btn-import');
  const $buttonImporting = $root.find('.btn-importing');

  function setImportingState(isImporting) {
    $buttonImport.toggle(!isImporting);
    $buttonImporting.toggle(isImporting);
  }

  function watchImport(importId) {
    mercure.subscribe(`imports/${importId}`, (update, unsubscribe) => {
      if (update.status === 'completed' || update.status === 'failed') {
        unsubscribe();
        setImportingState(false);
      }
    });
  }

  const activeImportId = $root.data('activeImportId');
  if (activeImportId) {
    watchImport(activeImportId);
  }

  $buttonImport.on('click', async () => {
    setImportingState(true);

    try {
      const importRecord = await runImport('products');
      watchImport(importRecord.id);
    } catch (error) {
      setImportingState(false);
    }
  });
});


