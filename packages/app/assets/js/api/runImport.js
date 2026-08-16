import $ from 'jquery';
import notify from '@core/informer/notify';

async function runImport(type) {
  const imports = await runImports([type]);
  return imports[0];
}

async function runImports(types) {
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
    return response.imports;
  } catch (error) {
    notify.danger('Could not start the import. Please try again.');
    throw error;
  }
}

export {
  runImport,
  runImports
};
