const str = {
  random(length, options) {
    return randomString(length, options);
  }
};

function randomString(length, options = {}) {
  const caps = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  const smalls = 'abcdefghijklmnopqrstuvwxyz';
  const numbers = '0123456789';

  let characters = '';

  // Check if the user passed ANY options at all
  const hasOptions = Object.keys(options).length > 0;

  if (!hasOptions) {
    // If NO options were passed, combine everything (Default behavior)
    characters = caps + smalls + numbers;
  } else {
    // If options WERE passed, only include what is explicitly set to true
    if (options.caps === true) characters += caps;
    if (options.smalls === true) characters += smalls;
    if (options.numbers === true) characters += numbers;
  }

  // Safety check: if options were passed but none were set to true
  if (characters.length === 0) {
    return '';
  }

  // Generate the string
  let result = '';
  const charactersLength = characters.length;
  for (let i = 0; i < length; i++) {
    result += characters.charAt(Math.floor(Math.random() * charactersLength));
  }

  return result;
}

export { str };