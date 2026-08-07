import $ from 'jquery';
import bus from '@core/event-bus/EventBus';
import { ImportStartedEvent } from '@app/event/import/ImportStartedEvent';
import notify from '@core/informer/notify';

export async function runImport(type) {
  try {
    const response = await $.ajax({
      url: '/imports',
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({imports: [type]})
    });

    bus.emit(new ImportStartedEvent(response.imports[0]));
  } catch (error) {
    console.log(error);
    notify.danger('Could not start the import. Please try again.');
  }
}

export async function runImports(types) {
  if (!Array.isArray(types)) {
    throw new TypeError('runImports: types must be an array');
  }

  try {
    const response = await $.ajax({
      url: '/imports',
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({imports: types})
    });

    response.imports.forEach((imp) => {
      bus.emit(new ImportStartedEvent(imp));
    });
  } catch (error) {
    notify.danger('Could not start the import. Please try again.');
  }
}