import $ from 'jquery';

//===[ ▼ observe ▼ ]=======================================================================================<editor-fold>

$.fn.observe = function (config, callback) {
  const observer = new MutationObserver(callback);
  this.each(function () {
    observer.observe(this, config);
  });
  return this;
};

//===[ ▲ observe ▲ ]======================================================================================</editor-fold>

//===[ ▼ watch / unwatch ▼ ]===============================================================================<editor-fold>

//---[ ▼ Variables ▼ ]-------------------------------------------------------------------------------------<editor-fold>

const FLAG_KEYS = [
  'added', 'removed', 'childlist', 'attributes', 'subtree',
  'attributeOldValue', 'characterData', 'characterDataOldValue'
];

// Node -> { observer, records: [{selector, callback, types: {added: true, removed: 'ns', ...}}], probedNodes }
const watchers = new WeakMap();

// user callback -> its per-element wrapper, reused across watchEach calls so $.fn.watch's
// reference-equality dedup (r.callback === callback) still finds the existing record
const watchEachWrappers = new WeakMap();

//---[ ▲ Variables ▲ ]------------------------------------------------------------------------------------</editor-fold>
//---[ ▼ Helper Functions ▼ ]------------------------------------------------------------------------------<editor-fold>

function parseTypes(input, impliesAttributes = true) {
  if (input === undefined) {
    console.error('$.fn.watch/unwatch: options is required, got nothing');
    return {};
  }

  const types = {};
  let defaultNamespace;

  const assign = (name, value) => {
    if (name === 'all') {
      for (const key of FLAG_KEYS) {
        if (key !== 'childlist') types[key] = value;
      }
      return;
    }
    if (!FLAG_KEYS.includes(name)) {
      console.error(`$.fn.watch/unwatch: unknown type "${name}", ignoring it`);
      return;
    }
    types[name] = value;
  };

  if (typeof input === 'string') {
    const inputs = input.trim().split(/\s+/).filter(Boolean);

    for (const token of inputs) {
      if (token.startsWith('attributeFilter:')) {
        (types.attributeFilter ??= []).push(token.slice('attributeFilter:'.length));
        continue;
      }

      if (token.startsWith('namespace:')) {
        defaultNamespace = token.slice('namespace:'.length);
        continue;
      }

      const [name, namespace] = token.split('.');
      assign(name, namespace || true);
    }
  } else {
    if (typeof input.namespace === 'string') defaultNamespace = input.namespace;

    const inputAll = input.all;

    if (inputAll !== undefined && inputAll !== null && inputAll !== false) {
      assign('all', typeof inputAll === 'string' ? inputAll : true);
    } else {
      for (const key of FLAG_KEYS) {
        const value = input[key];

        if (value !== undefined && value !== null && value !== false) {
          types[key] = typeof value === 'string' ? value : true;
        }
      }
    }

    if (input.attributeFilter) {
      if (Array.isArray(input.attributeFilter)) {
        input.attributeFilter.forEach(item => {
          if (typeof item !== 'string') {
            console.error(
              `$.fn.watch/unwatch: invalid attributeFilter item skipped, expected string, got ${typeof item}`,
              item
            );
          }
        });

        types.attributeFilter = input.attributeFilter.filter(item => typeof item === 'string');
      } else {
        console.error('$.fn.watch/unwatch: attributeFilter must be an array, ignoring it');
      }
    }

    const knownKeys = new Set([...FLAG_KEYS, 'all', 'attributeFilter', 'namespace']);

    for (const key of Object.keys(input)) {
      if (!knownKeys.has(key)) {
        console.error(`$.fn.watch/unwatch: unknown option "${key}", ignoring it`);
      }
    }
  }

  if (types.childlist && !types.added && !types.removed) {
    types.added = types.childlist;
    types.removed = types.childlist;
  }
  delete types.childlist;

  if (types.attributeFilter && !types.attributes && impliesAttributes) {
    types.attributes = true;
  }
  if (types.attributeOldValue && !types.attributes && impliesAttributes) {
    types.attributes = true;
  }
  if (types.characterDataOldValue && !types.characterData && impliesAttributes) {
    types.characterData = true;
  }

  if (defaultNamespace) {
    for (const key of FLAG_KEYS) {
      if (key === 'childlist') continue;
      if (types[key] === true) types[key] = defaultNamespace;
    }
  }

  return types;
}

function normalizeSelector(selector) {
  if (typeof selector === 'string' || selector === undefined) return selector;
  if (selector instanceof $) return selector.toArray();
  if (selector instanceof Node) return [selector];
  return selector;
}

function isSameSelector(a, b) {
  if (a === b) return true;
  if (Array.isArray(a) && Array.isArray(b)) {
    return a.length === b.length && a.every((el, i) => el === b[i]);
  }
  return false;
}

function resolveAncestorMatches(node, selector, root) {
  const candidates = typeof selector === 'string'
    ? (root.matches?.(selector)
      ? [root, ...root.querySelectorAll(selector)]
      : root.querySelectorAll(selector))
    : selector;

  return [...candidates].filter((candidate) => candidate.contains(node));
}

function dedupe(nodes) {
  return [...new Set(nodes)];
}

function resolveSubtreeMatches(node, parent, selector, root) {
  const ancestorMatches = resolveAncestorMatches(parent, selector, root);

  if (node.nodeType !== Node.ELEMENT_NODE) {
    return ancestorMatches;
  }

  if (typeof selector === 'string') {
    const selfMatch = node.matches(selector) ? [node] : [];
    const innerMatches = [...node.querySelectorAll(selector)];
    return [...ancestorMatches, ...selfMatch, ...innerMatches];
  }

  const arrayMatches = selector.filter((candidate) => candidate === node || node.contains(candidate));
  return [...ancestorMatches, ...arrayMatches];
}

function filterOutInternalMutations(mutationList, probedNodes) {
  const real = [];
  const toUntag = [];
  for (const record of mutationList) {
    if (record.type === 'childList') {
      const nodes = [...record.addedNodes, ...record.removedNodes];
      if (nodes.length && nodes.every((n) => probedNodes.has(n))) {
        toUntag.push(...nodes);
        continue;
      }
    }
    real.push(record);
  }
  for (const n of toUntag) probedNodes.delete(n);
  return real;
}

function* eachRestoredRemoval(mutationList, probedNodes) {
  const records = mutationList.filter((r) => r.type === 'childList');
  if (!records.length) return;

  try {
    for (const record of [...records].reverse()) {
      for (const node of record.addedNodes) {
        probedNodes.add(node);
        node.parentNode?.removeChild(node);
      }
      if (record.removedNodes.length) {
        for (const node of record.removedNodes) {
          probedNodes.add(node);
          record.target.insertBefore(node, record.nextSibling);
        }
        yield record;
      }
    }
  } finally {
    for (const record of records) {
      for (const node of record.addedNodes) record.target.insertBefore(node, record.nextSibling);
      for (const node of record.removedNodes) node.parentNode?.removeChild(node);
    }
  }
}

function queueSubtreeMatches(calls, root, record, nodeList, parent) {
  const nodes = Array.from(nodeList);
  const targets = record.selector
    ? dedupe(nodes.flatMap((n) => resolveSubtreeMatches(n, parent, record.selector, root)))
    : nodes;

  if (targets.length) calls.push({record, targets});
}

function queueAncestorMatches(calls, root, record, nodeList) {
  const nodes = Array.from(nodeList);
  const targets = record.selector
    ? dedupe(nodes.flatMap((n) => resolveAncestorMatches(n, record.selector, root)))
    : nodes;

  if (targets.length) calls.push({record, targets});
}

function dispatch(node, entry, rawMutationList) {
  const mutationList = filterOutInternalMutations(rawMutationList, entry.probedNodes);
  if (!mutationList.length) return;

  const calls = [];

  if (entry.records.some((r) => r.types.removed)) {
    for (const record of eachRestoredRemoval(mutationList, entry.probedNodes)) {
      for (const watcher of entry.records) {
        if (watcher.types.removed) {
          queueSubtreeMatches(calls, node, watcher, record.removedNodes, record.target);
        }
      }
    }
  }

  for (const record of entry.records) {
    for (const mutation of mutationList) {
      if (mutation.type === 'childList' && record.types.added) {
        queueSubtreeMatches(calls, node, record, mutation.addedNodes, mutation.target);
      } else if (mutation.type === 'attributes' && record.types.attributes) {
        queueAncestorMatches(calls, node, record, [mutation.target]);
      } else if (mutation.type === 'characterData' && record.types.characterData) {
        if (mutation.target.parentElement) {
          queueAncestorMatches(calls, node, record, [mutation.target.parentElement]);
        }
      }
    }
  }

  for (const {record, targets} of calls) {
    record.callback.call(targets, $(targets), mutationList);
  }
}

function getEntry(node) {
  let entry = watchers.get(node);
  if (!entry) {
    entry = {observer: null, records: [], probedNodes: new WeakSet()};
    watchers.set(node, entry);
  }
  return entry;
}

function constructObserverConfig(records) {
  const config = {};
  for (const {types} of records) {
    if (types.added || types.removed) config.childList = true;
    if (types.attributes) config.attributes = true;
    if (types.subtree) config.subtree = true;
    if (types.attributeOldValue) {
      config.attributeOldValue = true;
      config.attributes = true;
    }
    if (types.characterData) config.characterData = true;
    if (types.characterDataOldValue) {
      config.characterDataOldValue = true;
      config.characterData = true;
    }
    if (types.attributeFilter) {
      config.attributeFilter = [
        ...new Set([...(config.attributeFilter || []), ...types.attributeFilter])
      ];
    }
  }
  return config;
}

function reobserve(node, entry) {
  if (!entry.records.length) {
    if (entry.observer) {
      filterOutInternalMutations(entry.observer.takeRecords(), entry.probedNodes);
      entry.observer.disconnect();
    }
    watchers.delete(node);
    return;
  }
  if (!entry.observer) {
    entry.observer = new MutationObserver(function (mutationList) {
      dispatch(node, entry, mutationList);
    });
  }
  entry.observer.observe(node, constructObserverConfig(entry.records));
}

//---[ ▲ Helper Functions ▲ ]-----------------------------------------------------------------------------</editor-fold>

$.fn.watch = function (options, selector, callback) {
  if (typeof selector === 'function') {
    callback = selector;
    selector = undefined;
  }
  selector = normalizeSelector(selector);
  const newTypes = parseTypes(options);

  if (!Object.keys(newTypes).length) {
    console.error('$.fn.watch: no valid types given, nothing to watch');
    return this;
  }

  return this.each(function () {
    const storedEntry = watchers.get(this);

    let entry = storedEntry;
    let records;

    if (callback) {
      const found = storedEntry?.records.find(
        (r) => isSameSelector(r.selector, selector) && r.callback === callback
      );
      if (found) {
        records = [found];
      } else {
        const hasRealType = newTypes.added || newTypes.removed || newTypes.attributes || newTypes.characterData;
        if (!hasRealType) {
          console.error(
            '$.fn.watch: subtree needs an accompanying real type (added/removed/attributes/characterData), ignoring'
          );
          return;
        }
        entry = getEntry(this);
        const newRecord = {selector, callback, types: {}};
        entry.records.push(newRecord);
        records = [newRecord];
      }
    } else {
      records = entry
        ? (selector === undefined
          ? entry.records
          : entry.records.filter((r) => isSameSelector(r.selector, selector)))
        : [];
      if (!records.length) {
        console.error('$.fn.watch: no existing watcher matches that selector to update');
        return;
      }
    }

    for (const record of records) {
      for (const [name, value] of Object.entries(newTypes)) {
        if (name === 'attributeFilter') {
          record.types.attributeFilter = [...new Set([...(record.types.attributeFilter || []), ...value])];
          continue;
        }
        if (record.types[name] !== value) record.types[name] = value;
      }
    }

    reobserve(this, entry);
  });
};

$.fn.watchEach = function (options, selector, callback) {
  if (typeof selector === 'function') {
    callback = selector;
    selector = undefined;
  }

  let wrapper = watchEachWrappers.get(callback);
  if (!wrapper) {
    wrapper = function ($targets, mutationList) {
      $targets.each(function (index, element) {
        callback.call(element, $(element), mutationList, index);
      });
    };
    watchEachWrappers.set(callback, wrapper);
  }

  return this.watch(options, selector, wrapper);
};

$.fn.unwatch = function (options, selector) {
  selector = normalizeSelector(selector);
  const removeTypes = parseTypes(options, false);

  if (!Object.keys(removeTypes).length) {
    console.error('$.fn.unwatch: no valid types given, nothing to remove');
    return this; // bail before .each() - nothing would change anyway, skip the wasted pass
  }

  return this.each(function () {
    const entry = watchers.get(this);
    if (!entry) return;

    entry.records = entry.records.filter((record) => {
      if (selector !== undefined && !isSameSelector(record.selector, selector)) return true;

      for (const [name, value] of Object.entries(removeTypes)) {
        if (name === 'attributeFilter') {
          if (record.types.attributeFilter) {
            record.types.attributeFilter = record.types.attributeFilter.filter((attr) => !value.includes(attr));
            if (!record.types.attributeFilter.length) delete record.types.attributeFilter;
          }
          continue;
        }
        const existing = record.types[name];
        if (existing !== undefined && (value === true || existing === value)) {
          delete record.types[name];
        }
      }
      if (!record.types.attributes) {
        delete record.types.attributeFilter;
        delete record.types.attributeOldValue;
      }
      if (!record.types.characterData) delete record.types.characterDataOldValue;

      const hasRealType =
        record.types.added ||
        record.types.removed ||
        record.types.attributes ||
        record.types.characterData;

      if (!hasRealType) delete record.types.subtree;

      return Boolean(hasRealType);
    });

    reobserve(this, entry);
  });
};

//===[ ▲ watch / unwatch ▲ ]==============================================================================</editor-fold>
