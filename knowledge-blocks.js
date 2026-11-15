document.querySelectorAll('.iw-knowledge-section').forEach(section => {
  const tabs = [...section.querySelectorAll('[role="tab"]')];
  const activate = (tab, focus = false) => {
    tabs.forEach(item => {
      const selected = item === tab;
      item.setAttribute('aria-selected', selected ? 'true' : 'false');
      item.tabIndex = selected ? 0 : -1;
      const panel = document.getElementById(item.getAttribute('aria-controls'));
      if (panel) panel.hidden = !selected;
    });
    if (focus) tab.focus();
  };
  tabs.forEach((tab, index) => {
    tab.addEventListener('click', () => activate(tab));
    tab.addEventListener('keydown', event => {
      let next = index;
      if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
      else if (event.key === 'ArrowLeft') next = (index - 1 + tabs.length) % tabs.length;
      else if (event.key === 'Home') next = 0;
      else if (event.key === 'End') next = tabs.length - 1;
      else return;
      event.preventDefault(); activate(tabs[next], true);
    });
  });
  section.querySelectorAll('.iw-knowledge-panel').forEach(panel => {
    const track = panel.querySelector('.is-scrollable');
    if (!track) return;
    const previous = panel.querySelector('[data-iw-knowledge-prev]');
    const next = panel.querySelector('[data-iw-knowledge-next]');
    const step = () => track.querySelector('.iw-knowledge-card')?.getBoundingClientRect().width + 14 || track.clientWidth;
    const update = () => {
      previous.disabled = track.scrollLeft <= 2;
      next.disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 2;
    };
    previous.addEventListener('click', () => track.scrollBy({left: -step(), behavior:'smooth'}));
    next.addEventListener('click', () => track.scrollBy({left: step(), behavior:'smooth'}));
    track.addEventListener('scroll', update, {passive:true});
    window.addEventListener('resize', update); update();
  });
});
