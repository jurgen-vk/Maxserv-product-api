import $ from 'jquery';
import { notify } from '@core/informer/notify';

/**
 * Starts a single import of the given type and returns it.
 * @param {string} type - which import to run, e.g. `'products'`.
 * @returns {Promise<Object>} the created import record.
 */
async function runImport(type) {
  const imports = await runImports([type]);
  return imports[0];
}

/**
 * Starts one or more imports in a single request.
 * @param {string[]} types - the import types to run, e.g. `['products']`.
 * @returns {Promise<Object[]>} the created import records, one per type, in the same order.
 * @throws {TypeError} if `types` is not an array.
 */
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
