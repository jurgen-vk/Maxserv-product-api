import $ from "jquery";
import setProgress from "@app/progress";
import mercure from "@core/sse/mercure";

const $root = $(".p_imports");

const statusVariant = {
  pending: "neutral",
  running: "info",
  completed: "success",
  failed: "danger"
};

function renderBadge(status) {
  const label = status.charAt(0).toUpperCase() + status.slice(1);
  return `<span class="c_ui_badge" data-variant="${statusVariant[status]}">${label}</span>`;
}

// Only rows that started as pending/running get a live subscription — a
// row that's already completed/failed on load has nothing left to update.
$root.find(".import-row").each(function () {
  const $row = $(this);
  const initialStatus = $row.data("status");

  if (initialStatus !== "pending" && initialStatus !== "running") {
    return;
  }

  const importId = $row.data("importId");

  mercure.subscribe(`imports/${importId}`, (update, unsubscribe) => {
    if (update.status === "running") {
      const percent = setProgress($row.find(".progress-bar")[0], update.processed, update.total);
      $row.find(".progress-text").text(`${percent}%`);
      $row.find(".processed-cell").text(update.processed);
      return;
    }

    if (update.status === "completed" || update.status === "failed") {
      unsubscribe();
      $row.find(".status-cell").html(renderBadge(update.status));
      $row.find(".progress-cell").html("<span class=\"percent\">—</span>");

      if (update.status === "completed") {
        $row.find(".processed-cell").text(update.processed);
        $row.find(".completed-cell").text(new Date().toLocaleString());
      }
    }
  });

  // Deliberately no onerror handling — same rationale as the products page's
  // import watcher: transient drops auto-reconnect, a real terminal
  // failure always arrives as an explicit 'failed' status instead.
});
