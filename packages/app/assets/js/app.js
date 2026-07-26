import Alpine from "alpinejs";

window.Alpine = Alpine;

Alpine.store("toast", {
  visible: false,
  message: "",
  type: "success",
  timer: null,
  show(message, type = "success") {
    this.message = message;
    this.type = type;
    this.visible = true;
    clearTimeout(this.timer);
    this.timer = setTimeout(() => this.hide(), 4000);
  },
  hide() {
    this.visible = false;
  }
});

Alpine.store("import", {
  running: false,
  processed: 0,
  total: 0,
  get percent() {
    return this.total > 0 ? Math.round((this.processed / this.total) * 100) : 0;
  },
  async start() {
    this.running = true;

    try {
      const res = await fetch("/imports", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({imports: ["products"]})
      });

      if (!res.ok) {
        const {error} = await res.json();
        throw new Error(error ?? "Unknown error");
      }

      const {imports} = await res.json();
      const [{id: importId}] = imports;

      const source = new EventSource(
        document.body.dataset.mercureUrl + "?topic=" + encodeURIComponent("imports/" + importId)
      );

      source.onmessage = async (event) => {
        const update = JSON.parse(event.data);

        if (update.status === "running") {
          this.processed = update.processed;
          this.total = update.total;
          return;
        }

        if (update.status === "completed") {
          source.close();
          Alpine.store("toast").show(update.count + " products imported successfully");
          await this.refreshTable();
          this.reset();
          return;
        }

        if (update.status === "failed") {
          source.close();
          Alpine.store("toast").show("Import failed", "error");
          this.reset();
        }
      };

      // Deliberately no onerror handler: a dropped connection is usually transient
      // (network blip, tab backgrounded) — EventSource reconnects on its own and
      // catches up via Last-Event-ID, so tearing down the UI here would turn a
      // recoverable hiccup into a false failure. A real, terminal failure arrives as
      // the explicit 'failed' status above instead.
    } catch (err) {
      Alpine.store("toast").show("Import failed: " + err.message, "error");
      this.reset();
    }
  },
  reset() {
    this.running = false;
    this.processed = 0;
    this.total = 0;
  },
  async refreshTable() {
    const tableUrl = new URL("/products", window.location.origin);
    new URLSearchParams(window.location.search).forEach((v, k) => {
      if (k !== "fragments") tableUrl.searchParams.set(k, v);
    });
    tableUrl.searchParams.append("fragments[]", "_table");

    const tableRes = await fetch(tableUrl);
    const fragments = await tableRes.json();
    if (fragments._table != null) {
      document.getElementById("products-table-container").outerHTML = fragments.table;
    }
  }
});

Alpine.data("importsList", (initial, mercureUrl) => ({
  imports: initial,
  init() {
    const source = new EventSource(mercureUrl);

    source.onmessage = (event) => {
      const update = JSON.parse(event.data);

      if (this.imports[update.id]) {
        Object.assign(this.imports[update.id], update);
      }
    };
  }
}));

import.meta.glob("../../templates/**/*.js", {eager: true});

Alpine.start();
