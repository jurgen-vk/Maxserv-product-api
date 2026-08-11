import $ from 'jquery';

async function runImport(type) {
  try {
    const response = await $.ajax({
      url: '/imports',
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({imports: [type]})
    });

  } catch (error) {
    console.log(error);
    notify.danger('Could not start the import. Please try again.');
  }
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

  } catch (error) {
    console.log(error);
    notify.danger('Could not start the import. Please try again.');
  }
}

export {
  runImport,
  runImports
};