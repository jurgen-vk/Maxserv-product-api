import $ from 'jquery';

//===[ ▼ Functions ▼ ]=====================================================================================<editor-fold>

function updateProgress(progressFragment, current, total) {
  const $progressFragment = $(progressFragment);
  const percent = total > 0 ? Math.round((current / total) * 100) : 0;

  $progressFragment.find('.fill').css('--progress', `${percent}%`);

  return percent;
}

//===[ ▲ Functions ▲ ]====================================================================================</editor-fold>

export { updateProgress };