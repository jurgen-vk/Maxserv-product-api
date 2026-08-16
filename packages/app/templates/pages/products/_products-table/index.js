import $ from 'jquery';
import mercure from '@core/sse/mercure';
import bus from '@core/event-bus/EventBus';
import { ImportStartedEvent } from '@app/event/import/ImportStartedEvent';
import { Url } from '@app/utils/Url';
import { updateProgress } from '#app/components/ui/progress-bar/progress';
import { swapTable } from './swapTable';

const $root = $('.p_products__products-table');

let refreshController = null;

function refresh(params) {
  if (refreshController) refreshController.abort();
  refreshController = new AbortController();

  const $root = $('.p_products__products-table'); // fresh — see note above
  return swapTable($root, params, {signal: refreshController.signal}).catch((error) => {
    if (error.name !== 'AbortError') throw error;
  });
}

// ---- 1: react to any URL change relevant to this table's own params ----
if ('navigation' in window) {
  const $paginator = $root.find('.c_ui_paginator');
  const pageParam = $paginator.data('pageParam');
  const perPageParam = $paginator.data('perPageParam');
  const ownParams = ['search', 'sort', 'order', 'category', 'brand', 'minPrice', 'maxPrice', 'minRating', pageParam, perPageParam];

  navigation.addEventListener('navigate', (event) => {
    if (!event.canIntercept) return;

    const originalUrl = new Url();
    const url = new Url(event.destination.url);

    if (url.pathname !== originalUrl.pathname) return;

    const relevant = ownParams.some((key) => (
      originalUrl.searchParams.get(key) !== url.searchParams.get(key)
    ));

    if (!relevant) return;

    event.intercept({handler: () => refresh(Object.fromEntries(url.searchParams))});
  });
}

// ---- 2: import lifecycle — live progress while running, swap on completed/failed ----
function watchImport(importId) {
  mercure.subscribe(`imports/${importId}`, (update, unsubscribe) => {
    if (update.status === 'running') {
      const $root = $('.p_products__products-table'); // fresh — see note above
      const $row = $root.find('.progress-row');
      $row.show();
      updateProgress($row.find('.c_ui_progress-bar'), update.processed, update.total);
      return;
    }

    if (update.status === 'completed' || update.status === 'failed') {
      unsubscribe();
      const $root = $('.p_products__products-table'); // fresh — see note above
      $root.find('.progress-row').hide(); // immediate — doesn't wait on the fetch below, same as the button's own instant flip
      refresh(Object.fromEntries(new Url().searchParams));
    }
  });
}

const activeImportId = $root.data('activeImportId');
if (activeImportId) {
  watchImport(activeImportId);
}

// ---- 3: learn a freshly-started import's id from the button ----
bus.on(ImportStartedEvent, (event) => watchImport(event.import.id));
