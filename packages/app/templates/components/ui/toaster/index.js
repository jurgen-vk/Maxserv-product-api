import $ from 'jquery';
import bus from '@core/event-bus/EventBus';
import mercure from '@core/sse/mercure';
import { NotificationEvent } from '@core/event/NotificationEvent';
import toast from '#app/components/ui/toaster/_toast';

const $root = $('.c_ui_toaster');

async function pushNotification(event) {
  $root.append(await toast.create(event));
}

bus.on(NotificationEvent, pushNotification);

mercure.subscribe('notifications', (notification) => {
  pushNotification(
    new NotificationEvent(
      notification.message,
      notification.type,
      notification.icon,
      notification.duration
    )
  );
});

const serverNotifications = $root.data('serverNotifications') || [];
serverNotifications.forEach(
  (notification) => pushNotification(
    new NotificationEvent(
      notification.message,
      notification.type,
      notification.icon,
      notification.duration
    )
  )
);
$root.removeAttr('data-server-notifications');
