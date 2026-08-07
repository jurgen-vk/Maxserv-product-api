import $ from 'jquery';
import bus from '@core/event-bus/EventBus';
import { NotificationEvent } from '@core/event/NotificationEvent';
import { icon } from '@core/ui/icon';

const $root = $('.notification-container');

async function render(event) {
  const template = $('#notification-template').get(0);
  const $notification = $(template.content.cloneNode(true)).find('.notification');

  $notification.attr('data-type', event.type);
  $notification.find('.icon-slot').html(await icon(event.icon));
  $notification.find('.message').text(event.message);
  $notification.find('.close').on('click', () => dismiss($notification));

  $root.append($notification);
  setTimeout(() => dismiss($notification), event.duration);
}

function dismiss($notification) {
  $notification.css('--notification-height', `${$notification.outerHeight()}px`);
  $notification.addClass('dismissing');
  $notification.on('animationend', () => $notification.remove());
}

bus.on(NotificationEvent, render);

const mercureUrl = document.querySelector('meta[name="mercure-url"]').content;
const source = new EventSource(
  mercureUrl + '?topic=' + encodeURIComponent('notifications')
);

source.onmessage = (event) => {
  const data = JSON.parse(event.data);
  render(new NotificationEvent(data.type, data.message, data.icon, data.duration));
};

const serverNotifications = $root.data('server-notifications') || [];
serverNotifications.forEach(
  (entry) => render(new NotificationEvent(entry.type, entry.message, entry.icon, entry.duration)),
);
$root.removeAttr('data-server-notifications');
