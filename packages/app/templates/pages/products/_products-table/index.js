import $ from 'jquery';
import mercure from '@core/sse/mercure';
import { Url } from '@app/utils/Url';
import { updateProgress } from '#app/components/ui/progress-bar';
import { swapFragment } from '@app/api/swapFragment';

//===[ ▼ Main ▼ ]==========================================================================================<editor-fold>

$('.p_products__products-table').found(function ($root) {
  startNavigationHandler($root);
  watchImport($root.data('activeImportId'));
});
mercure.subscribe('events/ImportStartedEvent', (data) => watchImport(data.id));

let refreshController = null;

//===[ ▲ Main ▲ ]=========================================================================================</editor-fold>

//===[ ▼ Functions ▼ ]=====================================================================================<editor-fold>

function startNavigationHandler($root) {
  const $paginator = $root.find('.c_ui_paginator');
  const pageParam = $paginator.data('pageParam');
  const perPageParam = $paginator.data('perPageParam');
  const ownParams = [
    'search', 'sortBy', 'sortDir', 'category', 'brand', 'minPrice', 'maxPrice',
    'minRating', pageParam, perPageParam
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

async function refresh(params = new Url().searchParams) {
  if (refreshController) refreshController.abort();
  refreshController = new AbortController();

  const $root = $('.p_products__products-table'); // fresh

  try {
    await swapFragment(
      $root,
      '/products',
      '_products-table',
      params,
      {signal: refreshController.signal}
    );
  } catch (error) {
    if (error.name !== 'AbortError') throw error;
  }
}

function watchImport(importId) {
  if (!importId) return;

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
      $root.find('.progress-row').hide();
      refresh();
    }
  });
}

//===[ ▲ Functions ▲ ]====================================================================================</editor-fold>
