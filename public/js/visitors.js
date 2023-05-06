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
    const url = `${NASWP_VISITORS.ajaxurl}?ID=${NASWP_VISITORS.id}&type=${NASWP_VISITORS.type}&nonce=${NASWP_VISITORS.nonce}&action=naswp_track_user`;
    fetch(url)
      // .then(response => response.text())
      // .then(data => console.log(data))
      .catch(error => console.error(error));
  }

}());