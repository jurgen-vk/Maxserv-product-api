import $ from "jquery";
import debounce from "@app/utils/debounce";

$(".c_ui_table__search").each(function () {
  const $root = $(this);
  const $input = $root.find(".search-input");
  const $button = $root.find(".search-button");
  const $clear = $root.find(".clear");

  const emit = (value) => $root.trigger("table:search", [value]);
  const emitDebounced = debounce(emit, 500);

  function syncClearButton() {
    $clear.toggle($input.val() !== "");
  }

  $input.on("input", function () {
    syncClearButton();
    emitDebounced($(this).val());
  });

  $input.on("keydown", function (event) {
    if (event.key === "Enter") {
      event.preventDefault();
      emit($(this).val());
    }
  });

  $button.on("click", () => emit($input.val()));

  $clear.on("click", () => {
    $input.val("").trigger("focus");
    syncClearButton();
    emit("");
  });

  syncClearButton();
});
