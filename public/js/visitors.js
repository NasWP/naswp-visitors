(function() {
  const autoLoadDuration = 6;
  const eventList = ["keydown", "mousemove", "wheel", "touchmove", "touchstart", "touchend"];
  const autoLoadTimeout = setTimeout(triggerScripts, autoLoadDuration * 1000);
  eventList.forEach(event => {
    window.addEventListener(event, triggerScripts, {
      passive: true
    });
  });

  function triggerScripts() {
    countVisits();
    clearTimeout(autoLoadTimeout);
    eventList.forEach(function(event) {
      window.removeEventListener(event, triggerScripts, {
        passive: true
      });
    });
  }

  function countVisits() {
    if (!NASWP_VISITORS.trackUrl) {
      countVisitsLegacy();
      return;
    }
    // Lightweight endpoint (SHORTINIT boot); on any failure (blocked direct
    // access to plugin PHP files, exotic WP layout...) fall back to admin-ajax
    const url = `${NASWP_VISITORS.trackUrl}?ID=${NASWP_VISITORS.id}&type=${NASWP_VISITORS.type}&d=${NASWP_VISITORS.d}&token=${NASWP_VISITORS.token}`;
    fetch(url)
      .then(response => {
        if (!response.ok) countVisitsLegacy();
      })
      .catch(() => countVisitsLegacy());
  }

  function countVisitsLegacy() {
    const url = `${NASWP_VISITORS.ajaxurl}?ID=${NASWP_VISITORS.id}&type=${NASWP_VISITORS.type}&nonce=${NASWP_VISITORS.nonce}&action=naswp_track_user`;
    fetch(url)
      // .then(response => response.text())
      // .then(data => console.log(data))
      .catch(error => console.error(error));
  }

}());