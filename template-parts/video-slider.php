<!-- Swiper container -->
<div class="swiper video-slider h-[calc(var(--svh)*84)]">
  <div class="swiper-wrapper h-[calc(var(--svh)*84)]">
    <div class="swiper-slide h-full relative">
      <video autoplay muted playsinline loop
        class="absolute top-0 left-0 w-full h-full object-cover">
        <source src="https://aesir-studios.com/wp-content/uploads/2025/12/1208_MASTER-AESIR-STUDIO-2025-FASHION-v6.mp4" type="video/mp4">
      </video>
      <!-- <div class="w-full absolute bottom-[106px] md:bottom-[113px] left-0 flex items-center justify-center">
        <h1 class="text-white">Aesir Studio</h1>
      </div> -->
    </div>
    <!-- <div class="swiper-slide h-full relative">
      <video autoplay muted playsinline
        poster="<?php echo bloginfo('template_directory'); ?>/assets/images/poster-slide2.jpg"
        class="absolute top-0 left-0 w-full h-full object-cover">
        <source src="<?php echo bloginfo('template_directory'); ?>/assets/images/video-slide2.mp4" type="video/mp4">
      </video>
      <div class="w-full absolute bottom-[106px] md:bottom-[113px] left-0 flex items-center justify-center">
        <h1 class="text-white">Pocket Collection</h1>
      </div>
    </div>

    <div class="swiper-slide h-full relative">
      <video autoplay muted playsinline
        poster="<?php echo bloginfo('template_directory'); ?>/assets/images/poster-slide1.jpg"
        class="absolute top-0 left-0 w-full h-full object-cover">
        <source src="<?php echo bloginfo('template_directory'); ?>/assets/images/video-slide1.mp4" type="video/mp4">
      </video>
      <div class="w-full absolute bottom-[106px] md:bottom-[113px] left-0 flex items-center justify-center">
        <h1 class="text-white">2025 Bold Collection</h1>
      </div>
    </div>
    <div class="swiper-slide h-full relative">
      <img src="<?php echo bloginfo('template_directory'); ?>/assets/images/poster-slide2.jpg"
        class="absolute top-0 left-0 w-full h-full object-cover" />
      <div class="w-full absolute bottom-[106px] md:bottom-[113px] left-0 flex items-center justify-center">
        <h1 class="text-white">Image Collection</h1>
      </div>
    </div> -->
  </div>

  <!-- Pagination as progress bar dots -->
  <div class="swiper-pagination progress-pagination"></div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const sliderSelector = '.video-slider';

    // ---- State ----
    let progressBars = [];
    let completed = [];
    let realCount = 0;
    let lastRealIndex = null;
    const listenersMap = new WeakMap();
    let wrapTriggered = false;
    let imgTimer = null; // for image slides

    // ---- Helpers ----
    function removeListenersFromVideo(v) {
      if (!v) return;
      const info = listenersMap.get(v);
      if (info) {
        try {
          v.removeEventListener('timeupdate', info.timeupdate);
          v.removeEventListener('ended', info.ended);
        } catch (e) { }
        listenersMap.delete(v);
      }
    }

    function removeAllVideoListenersAndPause() {
      document.querySelectorAll(`${sliderSelector} video`).forEach(v => {
        removeListenersFromVideo(v);
        try { v.pause(); } catch (e) { }
      });
    }

    function resetAll() {
      clearTimeout(imgTimer);
      removeAllVideoListenersAndPause();
      completed = new Array(realCount).fill(false);
      progressBars.forEach(bar => {
        if (!bar) return;
        bar.style.transition = 'none';
        bar.style.width = '0%';
      });
    }

    function markCompleted(idx) {
      if (idx == null || !progressBars[idx]) return;
      completed[idx] = true;
      const bar = progressBars[idx];
      bar.style.transition = 'none';
      bar.style.width = '100%';
    }

    // ---- Play active slide ----
    function enterSlide(sw) {
      clearTimeout(imgTimer);
      removeAllVideoListenersAndPause();

      const activeSlide = sw.slides[sw.activeIndex];
      if (!activeSlide) return;
      const activeVideo = activeSlide.querySelector('video');
      const activeImg = activeSlide.querySelector('img');
      const realIdx = sw.realIndex;

      // update bars
      progressBars.forEach((bar, i) => {
        if (!bar) return;
        if (i < realIdx) {
          bar.style.transition = 'none';
          bar.style.width = '100%';
        } else if (i > realIdx) {
          bar.style.transition = 'none';
          bar.style.width = '0%';
        } else {
          completed[i] = false;
          bar.style.transition = 'none';
          bar.style.width = '0%';
        }
      });

      // ---- Case 1: Video ----
      if (activeVideo) {
        activeVideo.currentTime = 0;

        const onTimeUpdate = () => {
          if (!activeVideo.duration || isNaN(activeVideo.duration) || completed[realIdx]) return;
          const pct = Math.min(100, (activeVideo.currentTime / activeVideo.duration) * 100);
          const bar = progressBars[realIdx];
          if (bar) {
            bar.style.transition = 'width 0.12s linear';
            bar.style.width = pct + '%';
          }
        };

        const onEnded = () => {
          markCompleted(realIdx);
          removeListenersFromVideo(activeVideo);
          if (realIdx === (realCount - 1)) {
            wrapTriggered = true;
          }
          setTimeout(() => {
            try { sw.slideNext(); } catch (e) { }
          }, 120);
        };

        listenersMap.set(activeVideo, { timeupdate: onTimeUpdate, ended: onEnded });
        activeVideo.addEventListener('timeupdate', onTimeUpdate);
        activeVideo.addEventListener('ended', onEnded);

        // ---- Safe play() wrapper ----
        try {
          const playPromise = activeVideo.play();
          if (playPromise !== undefined) {
            playPromise.catch(err => {
              console.warn("Video play blocked or interrupted:", err);
            });
          }
        } catch (err) {
          console.warn("Video play error:", err);
        }

        onTimeUpdate();
        return;
      }

      // ---- Case 2: Image ----
      if (activeImg) {
        const duration = 15000; // 15s
        const bar = progressBars[realIdx];
        if (bar) {
          bar.style.transition = `width ${duration}ms linear`;
          bar.style.width = '100%';
        }

        imgTimer = setTimeout(() => {
          markCompleted(realIdx);
          if (realIdx === (realCount - 1)) {
            wrapTriggered = true;
          }
          try { sw.slideNext(); } catch (e) { }
        }, duration);
      }
    }

    // ---- Slide change ----
    function handleSlideChange(sw) {
      if (wrapTriggered && lastRealIndex === (realCount - 1) && sw.realIndex === 0) {
        resetAll();
        wrapTriggered = false;
      }
      lastRealIndex = sw.realIndex;
      enterSlide(sw);
    }

    // ---- Pagination click ----
    function attachPaginationClickHandler(sw) {
      const paginationEl = sw.pagination && sw.pagination.el ? sw.pagination.el : document.querySelector('.swiper-pagination');
      if (!paginationEl) return;

      paginationEl.addEventListener('click', (e) => {
        const bullet = e.target.closest('.swiper-pagination-bullet');
        if (!bullet) return;
        const bullets = Array.from(paginationEl.querySelectorAll('.swiper-pagination-bullet'));
        const clickedIndex = bullets.indexOf(bullet);
        if (clickedIndex === -1) return;

        for (let i = 0; i < realCount; i++) {
          if (i < clickedIndex) {
            completed[i] = true;
            if (progressBars[i]) { progressBars[i].style.transition = 'none'; progressBars[i].style.width = '100%'; }
          } else if (i > clickedIndex) {
            completed[i] = false;
            if (progressBars[i]) { progressBars[i].style.transition = 'none'; progressBars[i].style.width = '0%'; }
          } else {
            completed[i] = false;
            if (progressBars[i]) { progressBars[i].style.transition = 'none'; progressBars[i].style.width = '0%'; }
          }
        }
      });
    }

    // ---- Swiper ----
    const swiper = new Swiper(sliderSelector, {
      loop: true,
      speed: 600,
      allowTouchMove: true,
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
        renderBullet: (index, className) => `<span class="${className}"><span class="progress-bar"></span></span>`
      },
      on: {
        init: function () {
          const bullets = Array.from(document.querySelectorAll('.swiper-pagination .swiper-pagination-bullet'));
          progressBars = bullets.map(b => b.querySelector('.progress-bar'));
          realCount = progressBars.length;
          completed = new Array(realCount).fill(false);
          lastRealIndex = this.realIndex;
          attachPaginationClickHandler(this);
          enterSlide(this);
        },
        slideChangeTransitionStart: function () {
          handleSlideChange(this);
        }
      }
    });

    // ---- Click left/right navigation ----
    const sliderEl = document.querySelector(sliderSelector);
    if (sliderEl) {
      sliderEl.style.cursor = 'default';

      sliderEl.addEventListener('mousemove', (e) => {
        const rect = sliderEl.getBoundingClientRect();
        const midX = rect.left + rect.width / 2;
        if (e.clientX > midX) {
          sliderEl.style.cursor = 'e-resize'; // right side
        } else {
          sliderEl.style.cursor = 'w-resize'; // left side
        }
      });

      sliderEl.addEventListener('click', (e) => {
        const rect = sliderEl.getBoundingClientRect();
        const midX = rect.left + rect.width / 2;
        if (e.clientX > midX) {
          swiper.slideNext();
        } else {
          swiper.slidePrev();
        }
      });
    }

    // ---- Cleanup ----
    window.addEventListener('beforeunload', () => {
      clearTimeout(imgTimer);
      removeAllVideoListenersAndPause();
    });
  });
</script>