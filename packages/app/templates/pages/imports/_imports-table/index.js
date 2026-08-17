import $ from 'jquery';
import { updateProgress } from '#app/components/ui/progress-bar/progress';
import mercure from '@core/sse/mercure';
import { Url } from '@app/utils/Url';
import { swapFragment } from '@app/api/swapFragment';

const $root = $('.p_imports__imports-table');

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

// Matches Twig's `|date('Y-m-d H:i')` exactly — checked directly rather than
// assumed: `new Date(...).toLocaleString('sv-SE', {...these options...})`
// produces "2026-08-17 14:30" for a 2026-08-17T14:30 input, same
// dash/space/colon layout and zero-padding as PHP's Y-m-d H:i.
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

// Matches Twig's `durationSeconds|date('H:i:s', timezone='UTC')` exactly — that filter
// treats the raw integer as a Unix timestamp, so PHP's H format (00-23) wraps every 24
// hours; replicated here with `% 24` for the same behavior on the same input.
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

// ---- 1: react to any URL change relevant to this table's own params ----
if ('navigation' in window) {
  const pageParam = $root.find('.c_ui_paginator').data('pageParam');
  const perPageParam = $root.find('.c_ui_paginator').data('perPageParam');
  const ownParams = ['search', 'sortBy', 'sortDir', 'type', 'statuses[]', 'startedFrom', 'startedTo', 'endedFrom', 'endedTo', pageParam, perPageParam];

  navigation.addEventListener('navigate', (event) => {
    if (!event.canIntercept) return;

    const originalUrl = new Url();
    const url = new Url(event.destination.url);

    if (url.pathname !== originalUrl.pathname) return;

    const relevant = ownParams.some((key) => (
      JSON.stringify(originalUrl.searchParams.getAll(key)) !== JSON.stringify(url.searchParams.getAll(key))
    ));

    if (!relevant) return;

    // focusReset defaults to 'after-transition', which resets focus to <body> once the
    // handler resolves — even though the search input itself is never touched by the swap
    event.intercept({handler: () => refresh(url.searchParams), focusReset: 'manual'});
  });
}

let refreshController = null;

function refresh(params = new Url().searchParams) {
  if (refreshController) refreshController.abort();
  refreshController = new AbortController();

  const $root = $('.p_imports__imports-table'); // fresh — .replaceWith() detaches the old node
  return swapFragment($root, '/imports', '_imports-table', params, {signal: refreshController.signal})
    .then((html) => {
      // A real swap means brand-new .import-row nodes just landed — none of them have a
      // subscription yet, since watchRows() only ever runs against whatever's in the DOM
      // at the moment it's called, not future nodes .replaceWith() brings in later.
      if (html != null) watchRows();
    })
    .catch((error) => {
      if (error.name !== 'AbortError') throw error;
    });
}

// ---- 2: live progress + a duration ticker for any row still in flight ----
function watchRows() {
  const $root = $('.p_imports__imports-table'); // fresh — see note above

  $root.find('.import-row').each(function () {
    const $row = $(this);
    const initialStatus = $row.data('status');

    if (initialStatus !== 'pending' && initialStatus !== 'started' && initialStatus !== 'running') {
      return;
    }

    const importId = $row.data('importId');
    const startedAt = new Date($row.data('startedAt')).getTime();

    const tick = () => {
      $row.find('.import-td-duration').text(formatDuration(Math.floor((Date.now() - startedAt) / 1000)));
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

watchRows();

// ---- 3: notice an import that started elsewhere (e.g. the Products page) while this page is open ----
mercure.subscribe('events/ImportStartedEvent', () => refresh());
