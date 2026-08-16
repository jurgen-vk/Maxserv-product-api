import $ from 'jquery';
import { runImport } from '@app/api/runImport';
import mercure from '@core/sse/mercure';
import bus from '@core/event-bus/EventBus';
import { ImportStartedEvent } from '@app/event/import/ImportStartedEvent';

const $root = $('.p_products__import-button');
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
    bus.emit(new ImportStartedEvent(importRecord));
    watchImport(importRecord.id);
  } catch (error) {
    setImportingState(false);
  }
});
