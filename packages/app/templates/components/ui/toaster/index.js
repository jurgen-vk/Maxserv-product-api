import $ from 'jquery';
import bus from '@core/event-bus/EventBus';
import { NotificationEvent } from '@core/event/NotificationEvent';
import toast from '~app/components/ui/toaster/_toast';

const $root = $('.c_ui_toaster');

async function pushNotification(event) {
  $root.append(await toast.create(event));
}

bus.on(NotificationEvent, pushNotification);

const mercureUrl = $('meta[name="mercure-url"]').attr('content');
const source = new EventSource(
  mercureUrl + '?topic=' + encodeURIComponent('notifications')
);

source.onmessage = (event) => {
  const data = JSON.parse(event.data);
  pushNotification(
    new NotificationEvent(data.message, data.type, data.icon, data.duration)
  );
};

const serverNotifications = $root.data('server-notifications') || [];
serverNotifications.forEach(
  (entry) => pushNotification(
    new NotificationEvent(
      entry.message,
      entry.type,
      entry.icon,
      entry.duration
    )
  )
);
$root.removeAttr('data-server-notifications');
