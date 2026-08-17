import mercure from '@core/sse/mercure';
import cookie from '@core/storage/cookie';

mercure.configure(document.querySelector('link[rel="mercure"]').href);

const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
if (cookie.get('timezone') !== timezone) {
  cookie.set('timezone', timezone);
}