import $ from 'jquery';
import { updateProgress } from '#app/components/ui/progress-bar/progress';
import { fetchFragment } from '@core/api/fragmentFetcher';
import notify from '@core/informer/notify';
import mercure from '@core/sse/mercure';

const $root = $('.p_products');
const $tableContainer = $('.products-table-container');

let refreshController = null;

function buildUrl(params) {
  const url = new URL(window.location.href);

  Object.entries(params).forEach(([key, value]) => {
    if (value == null || value === '') {
      url.searchParams.delete(key);
    } else {
      url.searchParams.set(key, value);
    }
  });

  url.searchParams.delete('fragments');
  return url;
}

async function refreshTable(params = {}, {pushState = false} = {}) {
  if (refreshController) refreshController.abort();
  refreshController = new AbortController();

  try {
    const table = await fetchFragment('/products', '_products-table', {
      params,
      signal: refreshController.signal
    });

    if (table != null) {
      $tableContainer.html(table);
    }

    const url = buildUrl(params);
    if (pushState) {
      window.history.pushState({}, '', url);
    } else {
      window.history.replaceState({}, '', url);
    }
  } catch (error) {
    if (error.name !== 'AbortError') throw error;
  }
}

// ---- search (debounced typing = replaceState, explicit search = pushState via table:search) ----
$root.find('.c_ui_input_search').on('table:search', (event, value) => {
  refreshTable({search: value, page: null}, {pushState: true});
});

// ---- filter panel: apply routes through the same AJAX path, no full reload ----
$root.find('.filter-form').on('submit', function (event) {
  event.preventDefault();

  const params = Object.fromEntries(new FormData(this));
  params.page = null;

  refreshTable(params, {pushState: true});
  $('#products-filter-panel').get(0).hidePopover();
});

// ---- clear filters: same AJAX path as Apply (no full reload), but each
//      widget is reset first through the same public events it already
//      listens to — clicking its own "placeholder" option, or setting a
//      range number back to its min/max and firing 'change' — rather than
//      reaching into any widget's internal state directly ----
// delegated on the page (not bound directly) since this same action also
// appears in the table's empty state, which gets replaced on every refresh
$root.on('click', '.filter-clear', function (event) {
  event.preventDefault();

  const $form = $root.find('.filter-form');

  $form.find('.c_ui_input_select .select-input').each(function () {
    $(this).val('').trigger('change');
  });

  $form.find('.c_ui_input_range-slider').each(function () {
    const $minNumber = $(this).find('[data-range-number="min"]');
    const $maxNumber = $(this).find('[data-range-number="max"]');
    $minNumber.val($minNumber.attr('min')).trigger('change');
    $maxNumber.val($maxNumber.attr('max')).trigger('change');
  });

  $form.find('input[name="minRating"]').val('');

  refreshTable(
    {category: null, brand: null, minPrice: null, maxPrice: null, minRating: null, page: null},
    {pushState: true}
  );
  $('#products-filter-panel').get(0).hidePopover();
});

// ---- sort headers + pagination + per-page live inside the swapped table
//      fragment, so these are delegated on the stable container, not bound
//      directly (the elements themselves get replaced on every refresh) ----
$tableContainer.on('click', '.sort-link', function (event) {
  event.preventDefault();
  refreshTable({sort: $(this).data('column'), order: $(this).data('order'), page: null}, {pushState: true});
});

// ---- import trigger: resume-on-navigation via data-* attributes rendered
//      from the activeImport entity, no JSON blob ----
const $importTrigger = $('#products-import-trigger');
const $importStateToggle = $importTrigger.closest('.c_ui_state-toggle'); // TODO: I refactored this, so I should swap this out for the new thing

function setImportingState(isImporting) {
  $importStateToggle.attr('data-state', isImporting ? 'loading' : 'idle');
  // re-queried fresh each time, not cached — the progress bar now lives
  // inside the table fragment, which gets replaced on every refresh
  $root.find('.progress-bar').toggle(isImporting);
}

function watchImport(importId) {
  mercure.subscribe(`imports/${importId}`, (update, unsubscribe) => {
    if (update.status === 'running') {
      updateProgress($root.find('.progress-bar'), update.processed, update.total);
      return;
    }

    if (update.status === 'completed') {
      unsubscribe();
      setImportingState(false);
      refreshTable(Object.fromEntries(new URLSearchParams(window.location.search)));
      return;
    }

    if (update.status === 'failed') {
      unsubscribe();
      setImportingState(false);
    }
  });

  // Deliberately no onerror handling: a dropped connection is usually transient
  // (network blip, tab backgrounded) — EventSource reconnects on its own, so
  // tearing down the UI here would turn a recoverable hiccup into a false
  // failure. A real, terminal failure arrives as the explicit 'failed'
  // status above instead.
}

const activeImportId = $importTrigger.data('activeImportId');
if (activeImportId) {
  // the button's own disabled/label state is already correct from SSR (no
  // flash), but the progress bar's visibility is only ever toggled by
  // setImportingState() — it needs calling here too, not just updateProgress()
  setImportingState(true);
  updateProgress($root.find('.progress-bar'), $importTrigger.data('processed'), $importTrigger.data('total'));
  watchImport(activeImportId);
}

$importTrigger.on('click', async () => {
  setImportingState(true);

  try {
    const response = await fetch('/imports', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({imports: ['products']})
    });

    if (!response.ok) {
      throw new Error('Import failed to start');
    }

    const {imports} = await response.json();

    // the progress bar only exists in the table's own markup when the
    // server considers an import active — this table fragment was
    // rendered before that was true, so refresh it to pick up the new row
    // before trying to target the bar it contains
    await refreshTable(Object.fromEntries(new URLSearchParams(window.location.search)));
    updateProgress($root.find('.progress-bar'), 0, 0);
    $(document).trigger('import:started', [{importId: imports[0].id}]);
    watchImport(imports[0].id);
  } catch (error) {
    setImportingState(false);
    notify.danger('Could not start the import. Please try again.');
  }
});
