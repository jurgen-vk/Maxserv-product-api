import $ from 'jquery';
import { RangeSlider } from '#app/components/ui/input/range-slider/RangeSlider';


//===[ ▼ Main ▼ ]==========================================================================================<editor-fold>

// Most inputs will work dynamically because they have an event listener on the document.
// This needs an instantiated class, so that isn't really neatly possible. same for the select input.
// If you are dynamically going to swap this input without refreshing the page,
// you'll need to add a jquery watch statement I made, see the select input.
$('.c_ui_input_range-slider').foundEach(function () {
  new RangeSlider(this);
});

//===[ ▲ Main ▲ ]=========================================================================================</editor-fold>