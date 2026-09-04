import $ from 'jquery';

//===[ ▼ Classes ▼ ]=======================================================================================<editor-fold>

class MediaCarousel {
  constructor(el) {
    this.#$root = $(el);

    this.#$imageDisplay = this.#$root.find('.main .image-display');
    this.#$images = this.#$imageDisplay.find('.slide');
    this.#$thumbs = this.#$root.find('.thumb');
    this.#$thumbsRow = this.#$root.find('.thumbs');
    this.#$mainNav = this.#$root.find('.main .arrow');
    this.#$thumbsScroll = this.#$root.find('.thumbs-scroll');

    this.#index = 0;

    this.#bindEvents();
    this.#observeActiveSlide();
    this.#updateScrollArrows();
    this.#updateMainArrows();
  }

  #$root;
  #$imageDisplay;
  #$images;
  #$thumbs;
  #$thumbsRow;
  #$mainNav;
  #$thumbsScroll;
  #index;
  #isNavigating = false;

  #bindEvents() {
    this.#$thumbs.on('click', (e) => {
      this.#slideTo($(e.currentTarget).data('index'));
    });

    this.#$mainNav.on('click', (e) => {
      const isNext = $(e.currentTarget).hasClass('next');
      this.#slideTo(isNext ? this.#index + 1 : this.#index - 1);
    });

    this.#$thumbsScroll.on('click', (e) => {
      const isNext = $(e.currentTarget).hasClass('next');
      this.#$thumbsRow[0].scrollLeft += isNext ? 160 : -160;
    });

    this.#$imageDisplay.on('scroll', () => this.#updateMainArrows());
    this.#$imageDisplay.on('scrollend', () => { this.#isNavigating = false; });
    this.#$thumbsRow.on('scroll', () => this.#updateScrollArrows());

    $(window).on('resize', () => {
      this.#updateScrollArrows();
      this.#updateMainArrows();
    });
  }

  #observeActiveSlide() {
    if (this.#$images.length <= 1) return;

    const observer = new IntersectionObserver((
      entries
    ) => {
      if (this.#isNavigating) return;

      const match = entries.find((
        candidate
      ) => {
        return candidate.intersectionRatio >= 0.5;
      });
      if (!match) return;

      const index = this.#$images.index(match.target);
      if (index !== this.#index) this.#setActive(index);
    }, {root: this.#$imageDisplay[0], threshold: 0.5});

    this.#$images.each((_, el) => observer.observe(el));
  }

  #setActive(newIndex) {
    this.#index = newIndex;
    this.#$thumbs.removeAttr('data-active');

    const $thumb = this.#$thumbs.eq(newIndex);
    $thumb.attr('data-active', '');
    $thumb[0].scrollIntoView({behavior: 'smooth', inline: 'nearest', block: 'nearest'});
  }

  #slideTo(newIndex) {
    if (this.#$images.length === 0) return;

    newIndex = Math.max(0, Math.min(this.#$images.length - 1, newIndex));
    if (newIndex === this.#index) return;

    this.#isNavigating = true;
    this.#setActive(newIndex);
    this.#$images.eq(newIndex)[0].scrollIntoView({behavior: 'smooth', inline: 'start', block: 'nearest'});
  }

  #updateMainArrows() {
    if (this.#$imageDisplay.length === 0) return;

    const el = this.#$imageDisplay[0];
    const maxScroll = el.scrollWidth - el.clientWidth;

    this.#$mainNav.filter('.prev').toggle(el.scrollLeft > 1);
    this.#$mainNav.filter('.next').toggle(el.scrollLeft < maxScroll - 1);
  }

  #updateScrollArrows() {
    if (this.#$thumbsRow.length === 0) return;

    const el = this.#$thumbsRow[0];
    const maxScroll = el.scrollWidth - el.clientWidth;

    console.log(el.scrollLeft > 1);
    console.log(el.scrollLeft < maxScroll - 1);

    this.#$thumbsScroll.filter('.prev').toggle(el.scrollLeft > 1);
    this.#$thumbsScroll.filter('.next').toggle(el.scrollLeft < maxScroll - 1);
  }
}

//===[ ▲ Classes ▲ ]======================================================================================</editor-fold>

export {
  MediaCarousel
};
