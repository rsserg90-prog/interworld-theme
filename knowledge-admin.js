document.addEventListener('click', event => {
  const pick = event.target.closest('.iw-knowledge-pick-image');
  if (pick && window.wp?.media) {
    const input = pick.closest('p').querySelector('input');
    const frame = wp.media({title:'Изображение источника', button:{text:'Выбрать'}, multiple:false});
    frame.on('select', () => { input.value = frame.state().get('selection').first().get('url'); });
    frame.open();
  }
  const add = event.target.closest('.iw-knowledge-source-add');
  if (add) {
    const editor = add.closest('.iw-knowledge-source-editor');
    const row = editor.querySelector('.iw-knowledge-source-row').cloneNode(true);
    row.querySelectorAll('input').forEach(input => { input.value = ''; });
    row.querySelector('select').value = 'article';
    editor.querySelector('.iw-knowledge-source-rows').appendChild(row);
  }
  const remove = event.target.closest('.iw-knowledge-source-remove');
  if (remove) remove.closest('.iw-knowledge-source-row').remove();
});
