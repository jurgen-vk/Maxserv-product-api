type HtmlContent = string | Node | JQuery | Array<Node | string>;
type HtmlContentFactory = (this: HTMLElement, index: number, oldHtml: string) => HtmlContent;

declare global {
  interface JQuery {
    /** Returns the first matched element's `outerHTML`, or `undefined` if the collection is empty. */
    outerHtml(): string | undefined;
    /**
     * Replaces every matched element with `value`. Plain strings take a fast path (a direct
     * `outerHTML` assignment); anything else — an element, a `JQuery` object, an array of
     * nodes/strings, or a function computing any of those per element — falls back to
     * `.replaceWith()`, since the native `outerHTML` property only ever accepts a string.
     */
    outerHtml(value: HtmlContent | HtmlContentFactory): JQuery;

    /** Returns the first matched element's inner HTML — a thin alias for `.html()` with no arguments. */
    innerHtml(): string;
    /**
     * Sets inner content on every matched element — a thin alias for `.html(value)`. Despite
     * the name, this isn't string-only: jQuery's own `.html()` falls back to `.append()` for
     * anything that isn't a plain HTML string, so this also accepts an element, a `JQuery`
     * object, an array of nodes/strings, or a function computing any of those per element.
     */
    innerHtml(value: HtmlContent | HtmlContentFactory): JQuery;
  }
}

export {};