import '@core/mods/jquery-mod';
import notify from '@core/informer/notify';

import.meta.glob('../../templates/**/*.js', {eager: true});

notify.success('Test Message');