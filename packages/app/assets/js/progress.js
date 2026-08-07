export default function setProgress(root, processed, total) {
  const el = typeof root === "string" ? document.querySelector(root) : root;
  if (!el) return;

  const percent = total > 0 ? Math.round((processed / total) * 100) : 0;
  const fill = el.querySelector(".fill");
  if (fill) fill.style.setProperty("--progress", `${percent}%`);
  el.setAttribute("aria-valuenow", String(percent));

  return percent;
}
