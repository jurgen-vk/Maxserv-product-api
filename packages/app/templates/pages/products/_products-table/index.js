import $ from 'jquery';
import mercure from '@core/sse/mercure';
import { Url } from '@app/utils/Url';
import { updateProgress } from '#app/components/ui/progress-bar';
import { swapFragment } from '@app/api/swapFragment';

const $root = $('.p_products__products-table');

let refreshController = null;

function refresh(params = new Url().searchParams) {
  if (refreshController) refreshController.abort();
  refreshController = new AbortController();

  const $root = $('.p_products__products-table'); // fresh — .replaceWith() detaches the old node
  return swapFragment($root, '/products', '_products-table', params, {signal: refreshController.signal}).catch((error) => {
    if (error.name !== 'AbortError') throw error;
  });
}

// ---- 1: react to any URL change relevant to this table's own params ----
if ('navigation' in window) {
  const $paginator = $root.find('.c_ui_paginator');
  const pageParam = $paginator.data('pageParam');
  const perPageParam = $paginator.data('perPageParam');
  const ownParams = ['search', 'sortBy', 'sortDir', 'category', 'brand', 'minPrice', 'maxPrice', 'minRating', pageParam, perPageParam];

  window.navigation.addEventListener('navigate', (event) => {
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

// ---- 2: import lifecycle — live progress while running, swap on completed/failed ----
function watchImport(importId) {
  mercure.subscribe(`imports/${importId}`, (update, unsubscribe) => {
    if (update.status === 'running' || update.status === 'started' || update.status === 'pending') {
      const $root = $('.p_products__products-table'); // fresh
      const $row = $root.find('.progress-row');
      $row.show();
      updateProgress($row.find('.c_ui_progress-bar'), update.processed, update.total);
      return;
    }

    if (update.status === 'completed' || update.status === 'failed') {
      unsubscribe();
      const $root = $('.p_products__products-table'); // fresh
      $root.find('.progress-row').hide(); // immediate — doesn't wait on the fetch below, same as the button's own instant flip
      refresh();
    }
  });
}

const activeImportId = $root.data('activeImportId');
if (activeImportId) {
  watchImport(activeImportId);
}

// ---- 3: learn a freshly-started import's id via mercure instead of the local EventBus ----
mercure.subscribe('events/ImportStartedEvent', (data) => watchImport(data.id));
// mercure.subscribe('events/ImportStartedEvent', () => console.log('import has started'));
