<?php
/** Editorial controls shared by book and person records and category terms. */
if (!defined('ABSPATH')) exit;

add_action('acf/init', function() {
    if (!function_exists('acf_add_local_field_group')) return;
    $fields = array();
    foreach (array(
        'iw_knowledge_objects' => array('Объекты Межмирья', 'interworld_object'),
        'iw_knowledge_books' => array('Книги', 'library_book'),
        'iw_knowledge_people' => array('Личности', 'person'),
    ) as $name => $config) {
        $fields[] = array('key' => 'field_' . $name, 'name' => $name,
            'label' => $config[0] . ' — ручной выбор', 'type' => 'post_object',
            'post_type' => array($config[1]), 'return_format' => 'id',
            'multiple' => 1, 'ui' => 1, 'allow_null' => 1);
    }
    acf_add_local_field_group(array('key' => 'group_iw_knowledge_links',
        'title' => 'INTERWORLD: взаимосвязи', 'fields' => $fields,
        'location' => array(
            array(array('param' => 'post_type', 'operator' => '==', 'value' => 'library_book')),
            array(array('param' => 'post_type', 'operator' => '==', 'value' => 'person')),
            array(array('param' => 'taxonomy', 'operator' => '==', 'value' => 'library_category')),
            array(array('param' => 'taxonomy', 'operator' => '==', 'value' => 'person_category')),
            array(array('param' => 'page_template', 'operator' => '==', 'value' => 'page-biblioteka.php')),
        ), 'active' => true));

    acf_add_local_field_group(array('key'=>'group_iw_library_hub', 'title'=>'INTERWORLD: хаб библиотеки',
        'fields'=>array(
            array('key'=>'field_iw_lib_hub_lead','name'=>'iw_lib_hub_lead','label'=>'Краткое вступление','type'=>'textarea'),
            array('key'=>'field_iw_lib_hub_about','name'=>'iw_lib_hub_about','label'=>'О собрании','type'=>'wysiwyg','toolbar'=>'basic','media_upload'=>0),
            array('key'=>'field_iw_lib_hub_featured','name'=>'iw_lib_hub_featured','label'=>'Избранные книги','type'=>'post_object','post_type'=>array('library_book'),'multiple'=>1,'ui'=>1,'return_format'=>'id'),
        ), 'location'=>array(array(array('param'=>'page_template','operator'=>'==','value'=>'page-biblioteka.php'))), 'active'=>true));
    acf_add_local_field_group(array('key'=>'group_iw_library_category', 'title'=>'INTERWORLD: категория библиотеки',
        'fields'=>array(
            array('key'=>'field_iw_lib_cat_lead','name'=>'iw_lib_cat_lead','label'=>'Краткое вступление','type'=>'textarea'),
            array('key'=>'field_iw_lib_cat_body','name'=>'iw_lib_cat_body','label'=>'Описание категории','type'=>'wysiwyg','toolbar'=>'basic','media_upload'=>0),
            array('key'=>'field_iw_lib_cat_art','name'=>'iw_lib_cat_art','label'=>'Изображение','type'=>'image','return_format'=>'id'),
            array('key'=>'field_iw_lib_cat_featured','name'=>'iw_lib_cat_featured','label'=>'Избранные книги','type'=>'post_object','post_type'=>array('library_book'),'multiple'=>1,'ui'=>1,'return_format'=>'id'),
        ), 'location'=>array(array(array('param'=>'taxonomy','operator'=>'==','value'=>'library_category'))), 'active'=>true));
    acf_add_local_field_group(array('key'=>'group_iw_book_people', 'title'=>'INTERWORLD: авторы книги',
        'fields'=>array(array('key'=>'field_iw_book_authors','name'=>'iw_book_authors','label'=>'Авторы — карточки личностей','type'=>'post_object','post_type'=>array('person'),'multiple'=>1,'ui'=>1,'return_format'=>'id')),
        'location'=>array(array(array('param'=>'post_type','operator'=>'==','value'=>'library_book'))), 'active'=>true));
});

add_action('acf/save_post', function($post_id) {
    if (!is_numeric($post_id) || get_post_type((int)$post_id) !== 'library_book') return;
    $book_id = (int)$post_id;
    $previous = array_map('absint', (array)get_post_meta($book_id, '_iw_book_author_index', true));
    $current = array_filter(interworld_knowledge_ids('iw_book_authors', $book_id), function($id) { return get_post_type($id)==='person'; });
    foreach (array_unique(array_merge($previous, $current)) as $person_id) {
        if (get_post_type($person_id) !== 'person') continue;
        $books = array_map('absint', (array)get_post_meta($person_id, '_iw_person_authored_book_ids', true));
        $books = array_values(array_diff($books, array($book_id)));
        if (in_array($person_id, $current, true)) $books[] = $book_id;
        update_post_meta($person_id, '_iw_person_authored_book_ids', array_values(array_unique($books)));
    }
    update_post_meta($book_id, '_iw_book_author_index', array_values($current));
}, 20);

function interworld_knowledge_source_rows($context) {
    $rows = interworld_knowledge_value('iw_knowledge_sources', $context);
    return is_array($rows) ? $rows : array();
}

function interworld_knowledge_source_fields($rows) {
    echo '<div class="iw-knowledge-source-editor"><p>Добавляйте источники без ограничения. Для «Других источников» достаточно названия.</p><div class="iw-knowledge-source-rows">';
    foreach (array_merge($rows, array(array())) as $row) interworld_knowledge_source_row($row);
    echo '</div><button type="button" class="button iw-knowledge-source-add">Добавить источник</button></div>';
}

function interworld_knowledge_source_row($row = array()) {
    $kind = $row['kind'] ?? 'article';
    echo '<div class="iw-knowledge-source-row" style="border:1px solid #ccd0d4;padding:12px;margin:10px 0">';
    echo '<p><label>Тип <select name="iw_knowledge_source_kind[]">';
    foreach (array('article' => 'Статья', 'video' => 'Видео', 'other' => 'Другой источник') as $value => $label) echo '<option value="' . esc_attr($value) . '" ' . selected($kind, $value, false) . '>' . esc_html($label) . '</option>';
    echo '</select></label></p>';
    foreach (array('title' => 'Название', 'url' => 'Ссылка (необязательно)', 'description' => 'Краткое пояснение', 'date' => 'Дата публикации (необязательно)', 'image' => 'Ссылка на изображение (необязательно)') as $key => $label) {
        echo '<p><label>' . esc_html($label) . '<br><input type="text" class="widefat" name="iw_knowledge_source_' . esc_attr($key) . '[]" value="' . esc_attr($row[$key] ?? '') . '"></label>';
        if ($key === 'image') echo ' <button type="button" class="button iw-knowledge-pick-image">Выбрать из медиафайлов</button>';
        echo '</p>';
    }
    echo '<button type="button" class="button-link-delete iw-knowledge-source-remove">Удалить строку</button></div>';
}

add_action('add_meta_boxes', function() {
    foreach (array('library_book', 'person', 'page') as $type) {
        add_meta_box('iw_knowledge_sources', 'INTERWORLD: источники', function($post) {
            wp_nonce_field('iw_knowledge_sources_save', 'iw_knowledge_sources_nonce');
            interworld_knowledge_source_fields(interworld_knowledge_source_rows($post->ID));
        }, $type, 'normal', 'default');
    }
});

function interworld_knowledge_sanitize_sources() {
    $titles = isset($_POST['iw_knowledge_source_title']) ? (array) wp_unslash($_POST['iw_knowledge_source_title']) : array();
    $kinds = isset($_POST['iw_knowledge_source_kind']) ? (array) wp_unslash($_POST['iw_knowledge_source_kind']) : array();
    $urls = isset($_POST['iw_knowledge_source_url']) ? (array) wp_unslash($_POST['iw_knowledge_source_url']) : array();
    $descriptions = isset($_POST['iw_knowledge_source_description']) ? (array) wp_unslash($_POST['iw_knowledge_source_description']) : array();
    $images = isset($_POST['iw_knowledge_source_image']) ? (array) wp_unslash($_POST['iw_knowledge_source_image']) : array();
    $dates = isset($_POST['iw_knowledge_source_date']) ? (array) wp_unslash($_POST['iw_knowledge_source_date']) : array();
    $rows = array();
    foreach ($titles as $index => $title) {
        $title = sanitize_text_field($title);
        if ($title === '') continue;
        $kind = sanitize_key($kinds[$index] ?? 'other');
        $rows[] = array('kind' => in_array($kind, array('article','video','other'), true) ? $kind : 'other',
            'title' => $title, 'url' => esc_url_raw($urls[$index] ?? ''),
            'description' => sanitize_textarea_field($descriptions[$index] ?? ''),
            'image' => esc_url_raw($images[$index] ?? ''), 'date' => sanitize_text_field($dates[$index] ?? ''));
    }
    return $rows;
}

add_action('save_post', function($post_id) {
    if (!in_array(get_post_type($post_id), array('library_book','person','page'), true)) return;
    if (!isset($_POST['iw_knowledge_sources_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['iw_knowledge_sources_nonce'])), 'iw_knowledge_sources_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    update_post_meta($post_id, 'iw_knowledge_sources', interworld_knowledge_sanitize_sources());
});

foreach (array('library_category','person_category') as $taxonomy) {
    add_action($taxonomy . '_add_form_fields', function() {
        wp_nonce_field('iw_knowledge_sources_save', 'iw_knowledge_sources_nonce');
        echo '<div class="form-field"><label>Источники</label>';
        interworld_knowledge_source_fields(array());
        echo '</div>';
    });
    add_action($taxonomy . '_edit_form_fields', function($term) {
        wp_nonce_field('iw_knowledge_sources_save', 'iw_knowledge_sources_nonce');
        echo '<tr class="form-field"><th scope="row"><label>Источники</label></th><td>';
        interworld_knowledge_source_fields(interworld_knowledge_source_rows($term->taxonomy . '_' . $term->term_id));
        echo '</td></tr>';
    });
    add_action('created_' . $taxonomy, 'interworld_knowledge_save_term_sources');
    add_action('edited_' . $taxonomy, 'interworld_knowledge_save_term_sources');
}
function interworld_knowledge_save_term_sources($term_id) {
    if (!isset($_POST['iw_knowledge_sources_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['iw_knowledge_sources_nonce'])), 'iw_knowledge_sources_save')) return;
    $term = get_term($term_id);
    if (!$term || is_wp_error($term) || !current_user_can('edit_term', $term_id)) return;
    update_term_meta($term_id, 'iw_knowledge_sources', interworld_knowledge_sanitize_sources());
}

add_action('admin_enqueue_scripts', function($hook) {
    if (!in_array($hook, array('post.php','post-new.php','term.php','edit-tags.php'), true)) return;
    wp_enqueue_media();
    wp_enqueue_script('interworld-knowledge-admin', get_template_directory_uri() . '/knowledge-admin.js', array(), filemtime(get_template_directory() . '/knowledge-admin.js'), true);
});
