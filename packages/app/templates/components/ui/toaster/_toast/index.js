import $ from 'jquery';
import icon from '@core/ui/icon';

export default {
  async create(event) {
    const $root = $('.c_ui_toaster__toast-template');
    return await createToast($root, event);
  }
};

async function createToast($template, event) {
  const $toast = $($template.prop('content')).clone(true, true).find('.c_ui_toaster__toast');

  $toast.attr('data-type', event.type);
  $toast.find('.icon-slot').html(await icon(event.icon));
  $toast.find('.message').text(event.message);

  $toast.find('.close').on('click', () => dismiss($toast));
  $toast.on('animationend', () => endAnimation($toast));

  scheduleDismiss($toast, event.duration);

  return $toast;
}

function dismiss($toast) {
  $toast.attr('data-animation-state', 'out');
}

function scheduleDismiss($toast, duration) {
  let timer = setTimeout(() => dismiss($toast), duration);

  $toast.on('mouseenter', () => clearTimeout(timer));
  $toast.on('mouseleave', () => {
    timer = setTimeout(() => dismiss($toast), duration);
  });
}

function endAnimation($toast) {
  const state = $toast.attr('data-animation-state');

  if (state === 'out') {
    $toast.attr('data-animation-state', 'remove');
  } else if (state === 'remove') {
    $toast.remove();
  }
}
