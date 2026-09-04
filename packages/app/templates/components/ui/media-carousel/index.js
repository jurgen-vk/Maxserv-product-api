import $ from 'jquery';
import { MediaCarousel } from '#app/components/ui/media-carousel/MediaCarousel';

//===[ ▼ Main ▼ ]==========================================================================================<editor-fold>

$('.c_ui_media-carousel').foundEach(function () {
  new MediaCarousel(this);
});

//===[ ▲ Main ▲ ]=========================================================================================</editor-fold>
