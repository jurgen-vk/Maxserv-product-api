import $ from 'jquery';

export function notify(message, type = 'default', duration = 4000) {
  const template = $('#notification-template').get(0);
  const $notification = $(template.content.cloneNode(true)).find('.notification');

  if (type !== 'default') {
    $notification.addClass(type);
  }

  $notification.find('.message').text(message);
  $notification.find('.close').on('click', () => dismiss($notification));

  $('#notifications-container').append($notification);
  setTimeout(() => dismiss($notification), duration);
}

function dismiss($notification) {
  $notification.addClass('dismissing');
  $notification.on('animationend', () => $notification.remove());
}
