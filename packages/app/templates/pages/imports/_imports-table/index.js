import $ from 'jquery';
import { updateProgress } from '#app/components/ui/progress-bar';
import mercure from '@core/sse/mercure';
import { Url } from '@app/utils/Url';
import { swapFragment } from '@app/api/swapFragment';

//===[ ▼ Main ▼ ]==========================================================================================<editor-fold>

$('.p_imports__imports-table').found(function ($root) {
  startNavigationHandler($root);
  watchRows($root);
});
mercure.subscribe('events/ImportStartedEvent', () => refresh());

let refreshController = null;

//===[ ▲ Main ▲ ]=========================================================================================</editor-fold>

//===[ ▼ Functions ▼ ]=====================================================================================<editor-fold>

function startNavigationHandler($root) {
  const pageParam = $root.find('.c_ui_paginator').data('pageParam');
  const perPageParam = $root.find('.c_ui_paginator').data('perPageParam');
  const ownParams = [
    'search', 'sortBy', 'sortDir', 'type', 'statuses[]', 'startedFrom',
    'startedTo', 'endedFrom', 'endedTo', pageParam, perPageParam
  ];

  window.navigation.addEventListener('navigate', (event) => {
    if (!event.canIntercept) return;

    const originalUrl = new Url();
    const url = new Url(event.destination.url);

    if (url.pathname !== originalUrl.pathname) return;

    const relevant = ownParams.some((key) => (
      JSON.stringify(originalUrl.searchParams.getAll(key)) !== JSON.stringify(url.searchParams.getAll(key))
    ));

    if (!relevant) return;

    event.intercept({handler: () => refresh(url.searchParams), focusReset: 'manual'});
  });
}

const statusVariant = {
  pending: 'neutral',
  started: 'info',
  running: 'info',
  completed: 'success',
  failed: 'danger'
};

function updateBadge(badge, label, variant) {
  const $badge = $(badge);
  $badge.attr('data-variant', variant);
  $badge.text(label);
}

function formatDateTime(isoString) {
  const date = new Date(isoString);

  return date.toLocaleString('sv-SE', {
    hour12: false,
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  });
}

function formatDuration(seconds) {
  const hours = Math.floor(seconds / 3600) % 24;
  const minutes = Math.floor((seconds % 3600) / 60);
  const remainingSeconds = seconds % 60;

  const pad = (n) => String(n).padStart(2, '0');

  return `${pad(hours)}:${pad(minutes)}:${pad(remainingSeconds)}`;
}

function applyUpdate($row, update) {
  const percent = updateProgress($row.find('.c_ui_progress-bar'), update.processed, update.total);
  $row.find('.progress-text').text(`${percent}%`);
  $row.find('.import-td-processed').text(update.processed);
  $row.find('.import-td-total').text(update.total);

  if (update.status === 'running') {
    return;
  }

  $row.find('.c_ui_progress-bar').attr('data-variant', statusVariant[update.status]);

  const label = update.status.charAt(0).toUpperCase() + update.status.slice(1);
  updateBadge($row.find('.import-td-status .c_ui_badge'), label, statusVariant[update.status]);

  $row.find('.import-td-ended').text(formatDateTime(update.endedAt));
  $row.find('.import-td-duration').text(formatDuration(update.durationSeconds));
}

async function refresh(params = new Url().searchParams) {
  if (refreshController) refreshController.abort();
  refreshController = new AbortController();

  const $root = $('.p_imports__imports-table'); // fresh

  try {
    const $newRoot = await swapFragment(
      $root,
      '/imports',
      '_imports-table',
      params,
      {signal: refreshController.signal}
    );

    if ($newRoot.exist) watchRows($newRoot);
  } catch (error) {
    if (error.name !== 'AbortError') throw error;
  }
}

function watchRows($root = $('.p_imports__imports-table')) {
  $root.find('.import-row').each(function () {
    const $row = $(this);
    const initialStatus = $row.data('status');

    if (initialStatus !== 'pending' && initialStatus !== 'started' && initialStatus !== 'running') {
      return;
    }

    const importId = $row.data('importId');
    const startedAt = new Date($row.data('startedAt')).getTime();

    const tick = () => {
      $row
        .find('.import-td-duration')
        .text(formatDuration(Math.floor((Date.now() - startedAt) / 1000)));
    };
    tick();
    const tickerId = setInterval(tick, 1000);

    mercure.subscribe(`imports/${importId}`, (update, unsubscribe) => {
      console.log('mercure subscribed to: ' + importId);
      applyUpdate($row, update);

      if (update.status === 'completed' || update.status === 'failed') {
        clearInterval(tickerId);
        // refresh();
        unsubscribe();
      }
    });
  });
}

//===[ ▲ Functions ▲ ]====================================================================================</editor-fold>