import $ from 'jquery';

function updateProgress(progressComponent, current, total) {
  const $progressComponent = $(progressComponent);
  const percent = total > 0 ? Math.round((current / total) * 100) : 0;

  $progressComponent.find('.fill').css('--progress', `${percent}%`);

  return percent;
}

export { updateProgress };