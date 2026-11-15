<?php
/** Unlimited editorial materials for TPL-03 taxonomy pages. */
if (!defined('ABSPATH')) exit;

function interworld_tpl03_materials($term) {
    $rows = get_term_meta($term->term_id, '_iw_tpl03_materials', true);
    return is_array($rows) ? $rows : array();
}

function interworld_tpl03_material_row($row = array()) {
    $row = wp_parse_args($row, array('kind' => 'article', 'title' => '', 'url' => '', 'description' => '', 'image' => '', 'date' => '', 'credit' => ''));
    ?>
    <div class="iw-material-row" style="padding:14px;margin:10px 0;border:1px solid #ccd0d4;background:#fff">
        <p><label>Тип <select name="iw_materials[kind][]"><option value="article" <?php selected($row['kind'], 'article'); ?>>Статья</option><option value="video" <?php selected($row['kind'], 'video'); ?>>Видео</option><option value="text" <?php selected($row['kind'], 'text'); ?>>Текстовый источник</option></select></label></p>
        <p><label>Название <input class="regular-text" name="iw_materials[title][]" value="<?php echo esc_attr($row['title']); ?>"></label></p>
        <p><label>Ссылка <input class="regular-text" type="url" name="iw_materials[url][]" value="<?php echo esc_attr($row['url']); ?>"></label></p>
        <p><label>Краткое описание <textarea class="large-text" rows="2" name="iw_materials[description][]"><?php echo esc_textarea($row['description']); ?></textarea></label></p>
        <p><label>Изображение <input class="regular-text iw-material-image" type="url" name="iw_materials[image][]" value="<?php echo esc_attr($row['image']); ?>"></label> <button type="button" class="button iw-material-pick">Выбрать из медиатеки</button></p>
        <p><label>Дата публикации <input type="date" name="iw_materials[date][]" value="<?php echo esc_attr($row['date']); ?>"></label> <label>Автор или площадка <input name="iw_materials[credit][]" value="<?php echo esc_attr($row['credit']); ?>"></label></p>
        <button type="button" class="button iw-material-remove">Убрать строку</button>
    </div>
    <?php
}

function interworld_tpl03_materials_editor($term) {
    echo '<tr class="form-field"><th><label>Материалы для исследования</label></th><td>';
    wp_nonce_field('iw_tpl03_save_materials', 'iw_tpl03_materials_nonce');
    echo '<p>Добавьте любое число статей, видео и текстовых источников. Строки без названия не сохраняются.</p><div id="iw-material-rows">';
    foreach (interworld_tpl03_materials($term) as $row) interworld_tpl03_material_row($row);
    echo '</div><button type="button" class="button" id="iw-material-add">Добавить материал</button>';
    echo '<template id="iw-material-template">'; interworld_tpl03_material_row(); echo '</template></td></tr>';
    $faq = get_term_meta($term->term_id, '_iw_tpl03_faq_extra', true);
    echo '<tr class="form-field"><th>Дополнительные вопросы</th><td><p>Добавляйте вопросы сверх четырёх полей выше.</p><div id="iw-faq-rows">';
    foreach ((array) $faq as $item) interworld_tpl03_faq_row($item);
    echo '</div><button type="button" class="button" id="iw-faq-add">Добавить вопрос</button><template id="iw-faq-template">'; interworld_tpl03_faq_row(); echo '</template></td></tr>';
}
function interworld_tpl03_faq_row($item = array()) {
    echo '<div class="iw-faq-row" style="padding:12px;margin:8px 0;border:1px solid #ccd0d4"><p><label>Вопрос <input class="large-text" name="iw_faq_q[]" value="' . esc_attr($item['q'] ?? '') . '"></label></p><p><label>Ответ <textarea class="large-text" rows="3" name="iw_faq_a[]">' . esc_textarea($item['a'] ?? '') . '</textarea></label></p><button type="button" class="button iw-faq-remove">Убрать вопрос</button></div>';
}
add_action('object_type_edit_form_fields', 'interworld_tpl03_materials_editor');
add_action('object_group_edit_form_fields', 'interworld_tpl03_materials_editor');

function interworld_tpl03_save_materials($term_id) {
    if (!current_user_can('manage_categories') || !isset($_POST['iw_tpl03_materials_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['iw_tpl03_materials_nonce'])), 'iw_tpl03_save_materials')) return;
    $raw = isset($_POST['iw_materials']) && is_array($_POST['iw_materials']) ? wp_unslash($_POST['iw_materials']) : array();
    $rows = array();
    foreach ((array) ($raw['title'] ?? array()) as $i => $title) {
        $title = sanitize_text_field($title);
        if ($title === '') continue;
        $kind = sanitize_key($raw['kind'][$i] ?? 'text');
        if (!in_array($kind, array('article', 'video', 'text'), true)) $kind = 'text';
        $rows[] = array('kind' => $kind, 'title' => $title,
            'url' => esc_url_raw($raw['url'][$i] ?? ''), 'description' => sanitize_textarea_field($raw['description'][$i] ?? ''),
            'image' => esc_url_raw($raw['image'][$i] ?? ''), 'date' => sanitize_text_field($raw['date'][$i] ?? ''),
            'credit' => sanitize_text_field($raw['credit'][$i] ?? ''));
    }
    update_term_meta($term_id, '_iw_tpl03_materials', $rows);
    $faq = array();
    $questions = isset($_POST['iw_faq_q']) ? (array) wp_unslash($_POST['iw_faq_q']) : array();
    $answers = isset($_POST['iw_faq_a']) ? (array) wp_unslash($_POST['iw_faq_a']) : array();
    foreach ($questions as $i => $q) {
        $q = sanitize_text_field($q); $a = sanitize_textarea_field($answers[$i] ?? '');
        if ($q !== '' && $a !== '') $faq[] = array('q' => $q, 'a' => $a);
    }
    update_term_meta($term_id, '_iw_tpl03_faq_extra', $faq);
}
add_action('edited_object_type', 'interworld_tpl03_save_materials');
add_action('edited_object_group', 'interworld_tpl03_save_materials');

add_action('admin_enqueue_scripts', function($hook) {
    if ($hook !== 'term.php' || !in_array($_GET['taxonomy'] ?? '', array('object_type', 'object_group'), true)) return;
    wp_enqueue_media();
    wp_add_inline_script('jquery-core', <<<'JS'
document.addEventListener('DOMContentLoaded', function(){
  const rows=document.getElementById('iw-material-rows'), template=document.getElementById('iw-material-template');
  if(!rows||!template)return;
  document.getElementById('iw-material-add').addEventListener('click', function(){rows.append(template.content.cloneNode(true));});
  const faqRows=document.getElementById('iw-faq-rows'),faqTemplate=document.getElementById('iw-faq-template');
  document.getElementById('iw-faq-add').addEventListener('click',function(){faqRows.append(faqTemplate.content.cloneNode(true));});
  faqRows.addEventListener('click',function(event){if(event.target.classList.contains('iw-faq-remove'))event.target.closest('.iw-faq-row').remove();});
  rows.addEventListener('click', function(event){
    const row=event.target.closest('.iw-material-row'); if(!row)return;
    if(event.target.classList.contains('iw-material-remove')) row.remove();
    if(event.target.classList.contains('iw-material-pick')){
      const picker=wp.media({title:'Изображение материала',multiple:false,library:{type:'image'}});
      picker.on('select',function(){row.querySelector('.iw-material-image').value=picker.state().get('selection').first().get('url');});
      picker.open();
    }
  });
});
JS
    );
});
