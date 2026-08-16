function debounce(fn, wait) {
  let timer;

  function debounced(...args) {
    clearTimeout(timer);
    timer = setTimeout(() => fn.apply(this, args), wait);
  }

  debounced.cancel = () => clearTimeout(timer);

  return debounced;
}

export default debounce;
