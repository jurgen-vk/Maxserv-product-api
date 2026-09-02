import $ from 'jquery';

// $.fn.fire dispatches a genuine native DOM event, unlike $.fn.trigger, which
// only reaches jQuery-bound handlers and old-style onX properties, never
// addEventListener-registered ones (confirmed directly in jQuery's own
// trigger() source — it never calls dispatchEvent except via calling a
// same-named native method like .click()/.focus(), which 'change' has none of).
//
// Usage:
//   $('.el').fire('change', { bubbles: true });
//   $('.el').fire(new ImportEvent('progress', { detail: {...} }));
const EVENT_CONSTRUCTORS = {
  // MouseEvent
  click: MouseEvent,
  dblclick: MouseEvent,
  mousedown: MouseEvent,
  mouseup: MouseEvent,
  mousemove: MouseEvent,
  mouseover: MouseEvent,
  mouseout: MouseEvent,
  mouseenter: MouseEvent,
  mouseleave: MouseEvent,
  contextmenu: MouseEvent,
  auxclick: MouseEvent,
  // KeyboardEvent
  keydown: KeyboardEvent,
  keyup: KeyboardEvent,
  keypress: KeyboardEvent,
  // FocusEvent
  focus: FocusEvent,
  blur: FocusEvent,
  focusin: FocusEvent,
  focusout: FocusEvent,
  // InputEvent
  input: InputEvent,
  beforeinput: InputEvent,
  // AnimationEvent / TransitionEvent
  animationstart: AnimationEvent,
  animationend: AnimationEvent,
  animationcancel: AnimationEvent,
  animationiteration: AnimationEvent,
  transitionstart: TransitionEvent,
  transitionend: TransitionEvent,
  transitioncancel: TransitionEvent,
  transitionrun: TransitionEvent,
  // PointerEvent
  pointerdown: PointerEvent,
  pointerup: PointerEvent,
  pointermove: PointerEvent,
  pointerover: PointerEvent,
  pointerout: PointerEvent,
  pointerenter: PointerEvent,
  pointerleave: PointerEvent,
  pointercancel: PointerEvent,
  gotpointercapture: PointerEvent,
  lostpointercapture: PointerEvent,
  // TouchEvent / WheelEvent / DragEvent
  touchstart: TouchEvent,
  touchend: TouchEvent,
  touchmove: TouchEvent,
  touchcancel: TouchEvent,
  wheel: WheelEvent,
  drag: DragEvent,
  dragstart: DragEvent,
  dragend: DragEvent,
  dragenter: DragEvent,
  dragleave: DragEvent,
  dragover: DragEvent,
  drop: DragEvent,
  // form
  submit: SubmitEvent,
  formdata: FormDataEvent,
  // window / history / storage
  popstate: PopStateEvent,
  hashchange: HashChangeEvent,
  storage: StorageEvent,
  pageshow: PageTransitionEvent,
  pagehide: PageTransitionEvent,
  beforeunload: BeforeUnloadEvent,
  pagereveal: PageRevealEvent,
  pageswap: PageSwapEvent,
  // progress-style
  progress: ProgressEvent,
  loadstart: ProgressEvent,
  load: ProgressEvent,
  loadend: ProgressEvent,
  abort: ProgressEvent,
  // misc
  error: ErrorEvent,
  message: MessageEvent,
  messageerror: MessageEvent,
  close: CloseEvent,
  compositionstart: CompositionEvent,
  compositionupdate: CompositionEvent,
  compositionend: CompositionEvent,
  clipboardchange: ClipboardChangeEvent,
  copy: ClipboardEvent,
  cut: ClipboardEvent,
  paste: ClipboardEvent,
  devicemotion: DeviceMotionEvent,
  deviceorientation: DeviceOrientationEvent,
  deviceorientationabsolute: DeviceOrientationEvent,
  gamepadconnected: GamepadEvent,
  gamepaddisconnected: GamepadEvent,
  dataavailable: BlobEvent,
  loading: FontFaceSetLoadEvent,
  loadingdone: FontFaceSetLoadEvent,
  loadingerror: FontFaceSetLoadEvent,
  inputreport: HIDInputReportEvent,
  upgradeneeded: IDBVersionChangeEvent,
  blocked: IDBVersionChangeEvent,
  complete: OfflineAudioCompletionEvent,
  shippingaddresschange: PaymentRequestUpdateEvent,
  shippingoptionchange: PaymentRequestUpdateEvent,
  datachannel: RTCDataChannelEvent,
  icecandidate: RTCPeerConnectionIceEvent,
  addtrack: TrackEvent,
  removetrack: TrackEvent,
  webglcontextlost: WebGLContextEvent,
  webglcontextrestored: WebGLContextEvent,
  webglcontextcreationerror: WebGLContextEvent,
  toggle: ToggleEvent,
  beforetoggle: ToggleEvent,
  beforeinstallprompt: BeforeInstallPromptEvent,
  rejectionhandled: PromiseRejectionEvent,
  unhandledrejection: PromiseRejectionEvent,
  scrollsnapchange: SnapEvent,
  scrollsnapchanging: SnapEvent
};

$.fn.fire = function (eventOrName, options = {}) {
  const event = eventOrName instanceof Event
    ? eventOrName
    : new (EVENT_CONSTRUCTORS[eventOrName] ?? ('detail' in options ? CustomEvent : Event))(
      eventOrName,
      {bubbles: true, cancelable: true, ...options}
    );

  return this.each(function () {
    this.dispatchEvent(event);
  });
};
