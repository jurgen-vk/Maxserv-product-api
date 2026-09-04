const str = {
  /**
   * Generates a random string of the given length using letters and digits.
   * @param {number} length - how many characters the result should have.
   * @param {{caps?: boolean, smalls?: boolean, numbers?: boolean}} [options] - restricts which
   * character sets are used. If omitted, all three are used. If provided, only the sets
   * explicitly set to `true` are included — if none are, an empty string is returned.
   * @returns {string}
   */
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