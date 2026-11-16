<?php
/** Content types, editorial fields, placeholder indexing and shared helpers. */
if (!defined('ABSPATH')) exit;

function interworld_content_register_types() {
    $types = array(
        'iw_article' => array('Статьи', 'Статья', 'stati', 'dashicons-media-text'),
        'iw_tradition' => array('Традиции', 'Традиция', 'traditsii', 'dashicons-book-alt'),
        'iw_glossary' => array('Глоссарий', 'Термин', 'glossariy', 'dashicons-editor-spellcheck'),
        'iw_source' => array('Источники', 'Источник', 'istochniki', 'dashicons-book'),
    );
    foreach ($types as $type => $data) {
        if (post_type_exists($type)) continue;
        register_post_type($type, array(
            'labels' => array('name' => $data[0], 'singular_name' => $data[1], 'add_new_item' => 'Добавить: ' . $data[1], 'edit_item' => 'Редактировать: ' . $data[1], 'menu_name' => $data[0]),
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            'has_archive' => $data[2],
            'rewrite' => array('slug' => $data[2], 'with_front' => false),
            'query_var' => true,
            'exclude_from_search' => false,
            'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'author', 'revisions', 'custom-fields'),
            'menu_icon' => $data[3],
        ));
    }
}
add_action('init', 'interworld_content_register_types', 8);

/** Expose only the editorial fields needed by the local import/review tool. */
function interworld_content_register_import_meta() {
    $field_types = array(
        'iw_source_id' => array('context' => array('edit'), 'sanitize' => 'sanitize_key'),
        'iw_lead' => array('context' => array('view', 'edit'), 'sanitize' => 'sanitize_textarea_field'),
        'iw_source_reference_name' => array('context' => array('view', 'edit'), 'sanitize' => 'sanitize_text_field'),
        'iw_source_reference_url' => array('context' => array('view', 'edit'), 'sanitize' => 'esc_url_raw'),
        'iw_source_reference_location' => array('context' => array('view', 'edit'), 'sanitize' => 'sanitize_text_field'),
        'iw_editorial_notes' => array('context' => array('edit'), 'sanitize' => 'sanitize_textarea_field'),
    );
    foreach (array('iw_article') as $post_type) {
        if (!post_type_exists($post_type)) continue;
        foreach ($field_types as $meta_key => $field) {
            register_post_meta($post_type, $meta_key, array(
                'type' => 'string',
                'single' => true,
                'show_in_rest' => array('schema' => array('type' => 'string', 'context' => $field['context'])),
                'sanitize_callback' => $field['sanitize'],
                'auth_callback' => function($allowed, $key, $post_id) {
                    return current_user_can('edit_post', (int)$post_id);
                },
            ));
        }
    }
}
add_action('init', 'interworld_content_register_import_meta', 99);

add_action('init', function() {
    $types = array('interworld_object', 'person', 'iw_article', 'iw_tradition', 'iw_glossary', 'iw_source');
    if (!taxonomy_exists('iw_topic')) {
        register_taxonomy('iw_topic', $types, array(
            'labels' => array('name' => 'Темы Межмирья', 'singular_name' => 'Тема', 'menu_name' => 'Темы Межмирья'),
            'public' => true, 'show_ui' => true, 'show_admin_column' => true, 'show_in_rest' => true,
            'hierarchical' => true, 'rewrite' => array('slug' => 'tema', 'with_front' => false),
        ));
    } else foreach ($types as $type) if (post_type_exists($type)) register_taxonomy_for_object_type('iw_topic', $type);
}, 25);

function interworld_content_rewrite_setup() {
    if (get_option('iw_editorial_rewrite_version') === '1') return;
    flush_rewrite_rules(false);
    update_option('iw_editorial_rewrite_version', '1', false);
}
add_action('admin_init', 'interworld_content_rewrite_setup');

function interworld_content_value($key, $post_id = 0) {
    $post_id = $post_id ?: get_the_ID();
    if (function_exists('get_field')) {
        $value = get_field($key, $post_id);
        if ($value !== null && $value !== false && $value !== '') return $value;
    }
    if (is_string($post_id) && strpos($post_id, '_') !== false) {
        $term_id = absint(substr(strrchr($post_id, '_'), 1));
        if ($term_id) return get_term_meta($term_id, $key, true);
    }
    return get_post_meta($post_id, $key, true);
}

function interworld_content_image_url($image, $size = 'large') {
    if (is_array($image)) {
        if (!empty($image['url'])) return $image['url'];
        $image = $image['ID'] ?? 0;
    }
    return is_numeric($image) ? (wp_get_attachment_image_url((int)$image, $size) ?: '') : (is_string($image) && wp_http_validate_url($image) ? $image : '');
}

function interworld_content_provenance($post_id) {
    $name = trim((string)interworld_content_value('iw_source_reference_name', $post_id));
    $url = esc_url(interworld_content_value('iw_source_reference_url', $post_id));
    $location = trim((string)interworld_content_value('iw_source_reference_location', $post_id));
    if ($name === '' && !$url && $location === '') return;
    ?><section class="iw-editorial-source iw-library-wrap"><h2>Основание и источник</h2><p><?php if ($name !== '') : ?><?php if ($url) : ?><a href="<?php echo $url; ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($name); ?></a><?php else : echo esc_html($name); endif; ?><?php elseif ($url) : ?><a href="<?php echo $url; ?>" target="_blank" rel="noopener noreferrer">Открыть исходный материал</a><?php endif; ?><?php if ($location !== '') : ?><?php echo ($name !== '' || $url) ? ' · ' : ''; ?><?php echo esc_html($location); ?><?php endif; ?></p></section><?php
}

function interworld_content_page_url($slug, $fallback = '') {
    $page = get_page_by_path($slug);
    if ($page instanceof WP_Post) return get_permalink($page);
    return $fallback ? home_url('/' . trim($fallback, '/') . '/') : '';
}

function interworld_content_section_url($section) {
    $term_map = array(
        'zones' => array('eony', 'zony', 'zones', 'eony-i-efiry', 'efiry'),
        'creatures' => array('sushchestva-promezhutka', 'sushchestva', 'creatures'),
        'spaces' => array('prostranstva-mezhmirya', 'prostranstva', 'spaces'),
        'afterlife' => array('posmertie', 'duat', 'afterlife'),
        'fairies' => array('feyri', 'fey', 'fairies'),
        'archons' => array('arkhonty', 'archons'),
    );
    if (isset($term_map[$section])) {
        foreach ($term_map[$section] as $slug) {
            $term = get_term_by('slug', $slug, 'object_type');
            if (!$term) $term = get_term_by('slug', $slug, 'object_group');
            if ($term) {
                $url = get_term_link($term);
                if (!is_wp_error($url)) return $url;
            }
        }
    }
    $pages = array('map' => array('karta-mezhmiria', 'karta'), 'about' => array('o-proekte', 'about'));
    if (isset($pages[$section])) foreach ($pages[$section] as $slug) {
        $url = interworld_content_page_url($slug);
        if ($url) return $url;
    }
    if ($section === 'library') return home_url('/biblioteka/');
    if ($section === 'persons') return get_post_type_archive_link('person') ?: home_url('/lichnosti/');
    if ($section === 'articles') return get_post_type_archive_link('iw_article') ?: home_url('/stati/');
    if ($section === 'traditions') return get_post_type_archive_link('iw_tradition') ?: home_url('/traditsii/');
    if ($section === 'glossary') return get_post_type_archive_link('iw_glossary') ?: home_url('/glossariy/');
    if ($section === 'sources') return get_post_type_archive_link('iw_source') ?: home_url('/istochniki/');
    if (in_array($section, array('zones','creatures','spaces','afterlife','fairies','archons'), true)) {
        return get_post_type_archive_link('interworld_object') ?: home_url('/?post_type=interworld_object');
    }
    if ($section === 'map' || $section === 'about') return home_url('/#' . $section);
    return home_url('/');
}

function interworld_fallback_menu() {
    $items = array(
        'zones' => 'Зоны и Эфиры', 'creatures' => 'Существа', 'spaces' => 'Пространства',
        'afterlife' => 'Посмертие', 'fairies' => 'Фейри', 'archons' => 'Архонты', 'library' => 'Библиотека',
    );
    echo '<ul class="menu">';
    foreach ($items as $key => $label) echo '<li><a href="' . esc_url(interworld_content_section_url($key)) . '">' . esc_html($label) . '</a></li>';
    echo '</ul>';
}
function interworld_fallback_footer_menu() {
    $items = array('map'=>'Карта','library'=>'Библиотека','articles'=>'Статьи','persons'=>'Личности','traditions'=>'Традиции','glossary'=>'Глоссарий','sources'=>'Источники','about'=>'О проекте');
    echo '<ul class="menu">';
    foreach ($items as $key => $label) echo '<li><a href="' . esc_url(interworld_content_section_url($key)) . '">' . esc_html($label) . '</a></li>';
    echo '</ul>';
}

function interworld_content_acf_groups() {
    if (!function_exists('acf_add_local_field_group')) return;

    $make_fields = function($fields, $prefix = '') {
        $result = array();
        foreach ($fields as $name => $field) {
            $result[] = array_merge(array('key' => 'field_' . ($prefix ? sanitize_key($prefix) . '_' : '') . $name, 'name' => $name), $field);
        }
        return $result;
    };
    $base_fields = array(
        'iw_lead' => array('label' => 'Краткий ответ: что это', 'type' => 'textarea', 'instructions' => 'Одно-два ясных предложения для карточки и первого экрана.'),
        'iw_subtitle' => array('label' => 'Подзаголовок', 'type' => 'text'),
        'iw_aliases' => array('label' => 'Другие названия', 'type' => 'textarea', 'instructions' => 'По одному названию в строке.'),
        'iw_verified_facts' => array('label' => 'Факты и характеристики', 'type' => 'textarea', 'instructions' => 'Пишите короткими строками и указывайте проверенные сведения.'),
        'iw_period' => array('label' => 'Период или дата', 'type' => 'text'),
    );
    foreach (array('iw_article','iw_tradition','iw_glossary','iw_source') as $type) {
        acf_add_local_field_group(array(
            'key' => 'group_iw_editorial_' . $type,
            'title' => 'INTERWORLD: краткое описание и данные',
            'fields' => $make_fields($base_fields, $type),
            'location' => array(array(array('param' => 'post_type', 'operator' => '==', 'value' => $type))),
            'position' => 'normal', 'style' => 'default', 'active' => true,
        ));
    }
    $provenance = $make_fields(array(
        'iw_source_reference_name'=>array('label'=>'Источник, книга или публикация','type'=>'text'),
        'iw_source_reference_url'=>array('label'=>'Ссылка на исходный материал','type'=>'url'),
        'iw_source_reference_location'=>array('label'=>'Том, глава или страницы','type'=>'text','instructions'=>'Например: том 2, глава 4, страницы 35–38. Оставьте пустым, если точное место неизвестно.'),
        'iw_editorial_notes'=>array('label'=>'Заметки редактора (не показываются посетителям)','type'=>'textarea'),
    ), 'source_provenance');
    acf_add_local_field_group(array('key'=>'group_iw_source_provenance','title'=>'INTERWORLD: происхождение и проверка материала','fields'=>$provenance,
        'location'=>array(
            array(array('param'=>'post_type','operator'=>'==','value'=>'interworld_object')),
            array(array('param'=>'post_type','operator'=>'==','value'=>'person')),
            array(array('param'=>'post_type','operator'=>'==','value'=>'library_book')),
            array(array('param'=>'post_type','operator'=>'==','value'=>'iw_article')),
            array(array('param'=>'post_type','operator'=>'==','value'=>'iw_tradition')),
            array(array('param'=>'post_type','operator'=>'==','value'=>'iw_glossary')),
            array(array('param'=>'post_type','operator'=>'==','value'=>'iw_source')),
        ),'position'=>'normal','style'=>'default','active'=>true));

    $object_fields = array_merge(array(
        'object_code' => array('label' => 'Код', 'type' => 'text'),
        'object_number' => array('label' => 'Номер', 'type' => 'text'),
        'object_short_definition' => array('label' => 'Краткое определение', 'type' => 'textarea'),
        'object_banner_description' => array('label' => 'Текст первого экрана', 'type' => 'textarea'),
        'object_function' => array('label' => 'Функция', 'type' => 'textarea'),
        'object_center' => array('label' => 'Центральный элемент', 'type' => 'text'),
        'object_principle' => array('label' => 'Принцип', 'type' => 'text'),
        'object_epiklety' => array('label' => 'Эпиклеты', 'type' => 'textarea'),
    ), array());
    foreach (array('youtube'=>'YouTube','enmerkar'=>'Блог Энмеркара','masters_heart'=>'Сердце Мастера','telegram'=>'Telegram','litres'=>'ЛитРес','geography'=>'География Межмирья') as $key => $label) {
        $object_fields['object_link_' . $key] = array('label' => $label . ': ссылка', 'type' => 'url');
        $object_fields['object_link_' . $key . '_label'] = array('label' => $label . ': текст ссылки', 'type' => 'text');
        $object_fields['object_link_' . $key . '_logo'] = array('label' => $label . ': значок', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'thumbnail');
    }
    for ($i = 1; $i <= 6; $i++) $object_fields['iw_object_gallery_' . $i] = array('label' => 'Изображение галереи ' . $i, 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium');
    for ($i = 1; $i <= 4; $i++) {
        $object_fields['iw_object_faq_q_' . $i] = array('label' => 'Вопрос ' . $i, 'type' => 'text');
        $object_fields['iw_object_faq_a_' . $i] = array('label' => 'Ответ ' . $i, 'type' => 'textarea');
    }
    for ($i = 1; $i <= 3; $i++) {
        $object_fields['iw_object_quote_' . $i] = array('label' => 'Цитата ' . $i, 'type' => 'textarea');
        $object_fields['iw_object_quote_source_' . $i] = array('label' => 'Источник цитаты ' . $i, 'type' => 'text');
        $object_fields['iw_object_quote_url_' . $i] = array('label' => 'Ссылка на источник цитаты ' . $i, 'type' => 'url');
    }
    acf_add_local_field_group(array('key'=>'group_iw_object_card','title'=>'INTERWORLD: карточка объекта','fields'=>$make_fields($object_fields,'object_card'),
        'location'=>array(array(array('param'=>'post_type','operator'=>'==','value'=>'interworld_object'))),'position'=>'normal','style'=>'default','active'=>true));

    $home_fields = array(
        'iw_home_eyebrow' => array('label'=>'Надзаголовок', 'type'=>'text'),
        'iw_home_title' => array('label'=>'Заголовок главной страницы', 'type'=>'text'),
        'iw_home_lead' => array('label'=>'Краткое описание проекта', 'type'=>'textarea'),
        'iw_home_hero_image' => array('label'=>'Изображение первого экрана', 'type'=>'image', 'return_format'=>'id'),
        'iw_home_map_text' => array('label'=>'Краткое описание карты', 'type'=>'textarea'),
        'iw_home_map_image' => array('label'=>'Изображение карты', 'type'=>'image', 'return_format'=>'id'),
        'iw_home_about' => array('label'=>'О проекте', 'type'=>'textarea'),
        'iw_home_featured_objects' => array('label'=>'Избранные объекты', 'type'=>'post_object', 'post_type'=>array('interworld_object'), 'multiple'=>1, 'ui'=>1, 'return_format'=>'id'),
        'iw_home_featured_person' => array('label'=>'Автор для блока на главной', 'type'=>'post_object', 'post_type'=>array('person'), 'multiple'=>0, 'ui'=>1, 'return_format'=>'id'),
    );
    for ($i = 1; $i <= 4; $i++) {
        $home_fields['iw_home_faq_q_' . $i] = array('label'=>'Вопрос ' . $i, 'type'=>'text');
        $home_fields['iw_home_faq_a_' . $i] = array('label'=>'Ответ ' . $i, 'type'=>'textarea');
    }
    acf_add_local_field_group(array('key'=>'group_iw_home','title'=>'INTERWORLD: главная страница','fields'=>$make_fields($home_fields,'home'),
        'location'=>array(array(array('param'=>'page_type','operator'=>'==','value'=>'front_page'))),'position'=>'normal','style'=>'default','active'=>true));

    $page_fields = array(
        'iw_page_lead' => array('label'=>'Краткое вступление', 'type'=>'textarea'),
        'iw_page_hero_image' => array('label'=>'Изображение первого экрана или карта', 'type'=>'image', 'return_format'=>'id'),
        'iw_page_body' => array('label'=>'Краткое описание раздела', 'type'=>'wysiwyg', 'toolbar'=>'basic', 'media_upload'=>0),
    );
    acf_add_local_field_group(array('key'=>'group_iw_hub_page','title'=>'INTERWORLD: содержимое раздела','fields'=>$make_fields($page_fields,'hub'),
        'location'=>array(array(array('param'=>'page_template','operator'=>'==','value'=>'page-hub.php'))),'position'=>'normal','style'=>'default','active'=>true));
    acf_add_local_field_group(array('key'=>'group_iw_map_page','title'=>'INTERWORLD: карта','fields'=>$make_fields($page_fields,'map'),
        'location'=>array(array(array('param'=>'page_template','operator'=>'==','value'=>'page-karta.php'))),'position'=>'normal','style'=>'default','active'=>true));
    acf_add_local_field_group(array('key'=>'group_iw_book_catalog_link','title'=>'INTERWORLD: внешняя страница книги','fields'=>$make_fields(array(
        'iw_book_external_catalog_url'=>array('label'=>'Страница книги на сайте автора или продавца','type'=>'url'),
        'iw_book_external_catalog_label'=>array('label'=>'Текст кнопки','type'=>'text','default_value'=>'Открыть страницу книги'),
    ),'book_catalog'), 'location'=>array(array(array('param'=>'post_type','operator'=>'==','value'=>'library_book'))),'position'=>'normal','style'=>'default','active'=>true));
}
add_action('acf/init', 'interworld_content_acf_groups');

function interworld_content_noindex_metabox() {
    $types = get_post_types(array('public'=>true, 'show_ui'=>true), 'names');
    foreach ($types as $type) add_meta_box('iw_noindex', 'Индексация поиском', 'interworld_content_noindex_box', $type, 'side', 'default');
}
add_action('add_meta_boxes', 'interworld_content_noindex_metabox');
function interworld_content_noindex_box($post) {
    wp_nonce_field('iw_noindex_save', 'iw_noindex_nonce');
    $checked = get_post_meta($post->ID, '_iw_noindex', true) === '1';
    echo '<label><input type="checkbox" name="_iw_noindex" value="1" ' . checked($checked, true, false) . '> Не показывать в поиске и карте сайта</label><p class="description">Отмечайте для временной пустой страницы. Снимите отметку, когда добавите полезное содержание.</p>';
}
add_action('save_post', function($post_id) {
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id) || !isset($_POST['iw_noindex_nonce'])) return;
    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['iw_noindex_nonce'])), 'iw_noindex_save') || !current_user_can('edit_post', $post_id)) return;
    if (!empty($_POST['_iw_noindex'])) update_post_meta($post_id, '_iw_noindex', '1');
    else delete_post_meta($post_id, '_iw_noindex');
}, 30);

add_action('save_post_page', function($post_id) {
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id) || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) return;
    $template = get_page_template_slug($post_id);
    $empty = false;
    if ($template === 'page-karta.php') {
        $image = interworld_content_value('iw_page_hero_image', $post_id);
        $has_image = (bool)(interworld_content_image_url($image) ?: get_the_post_thumbnail_url($post_id, 'large'));
        $has_text = trim(wp_strip_all_tags(get_post_field('post_content', $post_id))) !== '' || trim((string)interworld_content_value('iw_page_body', $post_id)) !== '';
        $empty = !$has_image || !$has_text;
    } elseif ($template === 'page-hub.php') {
        $empty = trim(wp_strip_all_tags(get_post_field('post_content', $post_id))) === '' && trim((string)interworld_content_value('iw_page_lead', $post_id)) === '' && trim((string)interworld_content_value('iw_page_body', $post_id)) === '';
    } elseif ($template === 'page-biblioteka.php') {
        $empty = (int)wp_count_posts('library_book')->publish === 0;
    }
    if ($empty) update_post_meta($post_id, '_iw_placeholder_noindex', '1');
    else delete_post_meta($post_id, '_iw_placeholder_noindex');
}, 99);
add_action('save_post', function($post_id) {
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id) || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) return;
    $type = get_post_type($post_id);
    if (!in_array($type, array('interworld_object','person','library_book','iw_article','iw_tradition','iw_glossary','iw_source'), true)) return;
    if (get_post_status($post_id) !== 'publish') { delete_post_meta($post_id, '_iw_placeholder_noindex'); return; }
    $body = trim(wp_strip_all_tags(get_post_field('post_content', $post_id)));
    $excerpt = trim(wp_strip_all_tags(get_post_field('post_excerpt', $post_id)));
    $lead_fields = array(
        'interworld_object' => array('object_short_definition','object_banner_description'),
        'person' => array('iw_person_lead','iw_person_facts'),
        'library_book' => array('kratkoe_opisanie','polnoe_opisanie'),
        'iw_article' => array('iw_lead'), 'iw_tradition' => array('iw_lead'),
        'iw_glossary' => array('iw_lead'), 'iw_source' => array('iw_lead'),
    );
    $lead = '';
    foreach ($lead_fields[$type] as $field_name) {
        $lead = trim(wp_strip_all_tags((string)interworld_content_value($field_name, $post_id)));
        if ($lead !== '') break;
    }
    if ($body === '' && $excerpt === '' && $lead === '') update_post_meta($post_id, '_iw_placeholder_noindex', '1');
    else delete_post_meta($post_id, '_iw_placeholder_noindex');
}, 101);
function interworld_content_sync_library_placeholder() {
    $pages = get_posts(array('post_type'=>'page','post_status'=>'publish','posts_per_page'=>-1,'fields'=>'ids','meta_key'=>'_wp_page_template','meta_value'=>'page-biblioteka.php'));
    $empty = (int)wp_count_posts('library_book')->publish === 0;
    foreach ($pages as $page_id) {
        if ($empty) update_post_meta($page_id, '_iw_placeholder_noindex', '1');
        else delete_post_meta($page_id, '_iw_placeholder_noindex');
    }
}
add_action('save_post_library_book', 'interworld_content_sync_library_placeholder', 99);
foreach (array('trashed_post','untrashed_post','deleted_post') as $book_status_action) {
    add_action($book_status_action, function($post_id, $post = null) {
        if ((($post instanceof WP_Post) && $post->post_type === 'library_book') || get_post_type($post_id) === 'library_book') interworld_content_sync_library_placeholder();
    }, 10, 2);
}

function interworld_content_register_term_noindex_fields() {
    foreach (get_taxonomies(array('public'=>true, 'show_ui'=>true), 'names') as $taxonomy) {
        add_action($taxonomy . '_add_form_fields', function() {
            wp_nonce_field('iw_term_noindex_save', 'iw_term_noindex_nonce');
            echo '<div class="form-field"><label><input type="checkbox" name="_iw_noindex" value="1"> Не показывать пустую категорию в поиске и карте сайта</label></div>';
        });
        add_action($taxonomy . '_edit_form_fields', function($term) {
            wp_nonce_field('iw_term_noindex_save', 'iw_term_noindex_nonce');
            $checked = get_term_meta($term->term_id, '_iw_noindex', true) === '1';
            echo '<tr class="form-field"><th scope="row">Индексация поиском</th><td><label><input type="checkbox" name="_iw_noindex" value="1" ' . checked($checked, true, false) . '> Не показывать пустую категорию в поиске и карте сайта</label></td></tr>';
        });
        add_action('created_' . $taxonomy, 'interworld_content_save_term_noindex');
        add_action('edited_' . $taxonomy, 'interworld_content_save_term_noindex');
    }
}
add_action('init', 'interworld_content_register_term_noindex_fields', 30);
function interworld_content_save_term_noindex($term_id) {
    if (empty($_POST['iw_term_noindex_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['iw_term_noindex_nonce'])), 'iw_term_noindex_save')) return;
    if (!current_user_can('edit_term', $term_id)) return;
    if (!empty($_POST['_iw_noindex'])) update_term_meta($term_id, '_iw_noindex', '1');
    else delete_term_meta($term_id, '_iw_noindex');
}

add_filter('wp_robots', function($robots) {
    $noindex = false;
    if (is_singular()) {
        $id = get_queried_object_id();
        $noindex = get_post_meta($id, '_iw_noindex', true) === '1' || get_post_meta($id, '_iw_placeholder_noindex', true) === '1';
        if (is_page_template('page-biblioteka.php') && (int)wp_count_posts('library_book')->publish === 0) $noindex = true;
        if (is_page_template('page-karta.php')) {
            $map_image = interworld_content_image_url(interworld_content_value('iw_page_hero_image', $id)) ?: get_the_post_thumbnail_url($id, 'large');
            $map_text = trim(wp_strip_all_tags(get_post_field('post_content', $id))) !== '' || trim(wp_strip_all_tags((string)interworld_content_value('iw_page_body', $id))) !== '';
            if (!$map_image || !$map_text) $noindex = true;
        }
    } elseif (is_tax() || is_category() || is_tag()) {
        $term = get_queried_object();
        $noindex = $term instanceof WP_Term && (get_term_meta($term->term_id, '_iw_noindex', true) === '1' || (int)$term->count === 0);
    } elseif (is_post_type_archive()) {
        $type = get_query_var('post_type');
        if (is_array($type)) $type = reset($type);
        $counts = wp_count_posts($type ?: get_queried_object()->name);
        $noindex = $counts && (int)$counts->publish === 0;
    }
    if ($noindex) { $robots['noindex'] = true; $robots['follow'] = true; }
    return $robots;
});

add_action('wp_head', function() {
    if (defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION') || defined('SEOPRESS_VERSION') || defined('AIOSEO_VERSION')) return;
    $description = '';
    if (is_front_page()) $description = get_bloginfo('description') ?: interworld_content_value('iw_home_lead', get_queried_object_id());
    elseif (is_singular()) {
        $id = get_queried_object_id();
        $description = get_the_excerpt($id);
        if (!$description) $description = interworld_content_value('iw_lead', $id) ?: interworld_content_value('object_short_definition', $id) ?: interworld_content_value('iw_page_lead', $id);
        if (!$description) $description = wp_trim_words(wp_strip_all_tags(get_post_field('post_content', $id)), 32, '');
    } elseif (is_tax() || is_category() || is_tag()) $description = term_description();
    if (trim((string)$description) === '') return;
    echo '<meta name="description" content="' . esc_attr(wp_trim_words(wp_strip_all_tags($description), 30, '')) . '">' . "\n";
}, 1);

add_filter('wp_sitemaps_posts_query_args', function($args, $post_type) {
    $args['meta_query'] = isset($args['meta_query']) ? $args['meta_query'] : array();
    $args['meta_query'][] = array('relation'=>'AND',
        array('relation'=>'OR', array('key'=>'_iw_noindex','compare'=>'NOT EXISTS'), array('key'=>'_iw_noindex','value'=>'1','compare'=>'!=')),
        array('relation'=>'OR', array('key'=>'_iw_placeholder_noindex','compare'=>'NOT EXISTS'), array('key'=>'_iw_placeholder_noindex','value'=>'1','compare'=>'!=')));
    return $args;
}, 10, 2);
add_filter('wp_sitemaps_taxonomies_query_args', function($args, $taxonomy) {
    $args['hide_empty'] = true;
    $args['meta_query'] = isset($args['meta_query']) ? $args['meta_query'] : array();
    $args['meta_query'][] = array('relation'=>'OR', array('key'=>'_iw_noindex','compare'=>'NOT EXISTS'), array('key'=>'_iw_noindex','value'=>'1','compare'=>'!='));
    return $args;
}, 10, 2);

add_filter('query_vars', function($vars) { $vars[] = 'iw_q'; $vars[] = 'iw_sort'; return $vars; });
function interworld_content_catalog_args($args) {
    $search = isset($_GET['iw_q']) ? sanitize_text_field(wp_unslash($_GET['iw_q'])) : '';
    $sort = isset($_GET['iw_sort']) ? sanitize_key(wp_unslash($_GET['iw_sort'])) : '';
    if ($search !== '') $args['s'] = $search;
    if ($sort === 'newest') { $args['orderby'] = 'date'; $args['order'] = 'DESC'; }
    elseif ($sort === 'alphabetical') { $args['orderby'] = 'title'; $args['order'] = 'ASC'; }
    return $args;
}
function interworld_content_catalog_form($placeholder = 'Найти материал') {
    $term = get_queried_object();
    $action = $term instanceof WP_Term ? get_term_link($term) : '';
    if (is_wp_error($action) || !$action) $action = home_url('/');
    $search = isset($_GET['iw_q']) ? sanitize_text_field(wp_unslash($_GET['iw_q'])) : '';
    $sort = isset($_GET['iw_sort']) ? sanitize_key(wp_unslash($_GET['iw_sort'])) : 'alphabetical';
    echo '<form class="iw-catalog-tools" method="get" action="' . esc_url($action) . '"><label><span class="screen-reader-text">Поиск в разделе</span><input type="search" name="iw_q" value="' . esc_attr($search) . '" placeholder="' . esc_attr($placeholder) . '"></label><label><span class="screen-reader-text">Сортировка</span><select name="iw_sort"><option value="alphabetical" ' . selected($sort, 'alphabetical', false) . '>По алфавиту</option><option value="newest" ' . selected($sort, 'newest', false) . '>Сначала новые</option></select></label><button type="submit">Найти</button></form>';
}
function interworld_content_pagination($current, $total, $type = 'plain') {
    $args = array('current'=>max(1,(int)$current), 'total'=>max(1,(int)$total), 'type'=>$type);
    $query_args = array();
    foreach (array('iw_q','iw_sort') as $key) if (isset($_GET[$key]) && !is_array($_GET[$key])) $query_args[$key] = sanitize_text_field(wp_unslash($_GET[$key]));
    if ($query_args) $args['add_args'] = $query_args;
    return wp_kses_post(paginate_links($args));
}
add_action('pre_get_posts', function($query) {
    if (is_admin() || !$query->is_main_query()) return;
    if ($query->is_post_type_archive()) {
        $query->set('posts_per_page', 12);
        $search = isset($_GET['iw_q']) ? sanitize_text_field(wp_unslash($_GET['iw_q'])) : '';
        $sort = isset($_GET['iw_sort']) ? sanitize_key(wp_unslash($_GET['iw_sort'])) : '';
        if ($search !== '') $query->set('s', $search);
        if ($sort === 'newest') { $query->set('orderby', 'date'); $query->set('order', 'DESC'); }
        else { $query->set('orderby', 'title'); $query->set('order', 'ASC'); }
    }
    if ($query->is_post_type_archive('person')) {
        $search = isset($_GET['iw_q']) ? sanitize_text_field(wp_unslash($_GET['iw_q'])) : '';
        $sort = isset($_GET['iw_sort']) ? sanitize_key(wp_unslash($_GET['iw_sort'])) : '';
        if ($search !== '') $query->set('s', $search);
        if ($sort === 'newest') { $query->set('orderby', 'date'); $query->set('order', 'DESC'); }
        else { $query->set('orderby', 'title'); $query->set('order', 'ASC'); }
    }
    if ($query->is_tax('iw_topic')) {
        $search = isset($_GET['iw_q']) ? sanitize_text_field(wp_unslash($_GET['iw_q'])) : '';
        $sort = isset($_GET['iw_sort']) ? sanitize_key(wp_unslash($_GET['iw_sort'])) : '';
        if ($search !== '') $query->set('s', $search);
        if ($sort === 'newest') { $query->set('orderby', 'date'); $query->set('order', 'DESC'); }
        else { $query->set('orderby', 'title'); $query->set('order', 'ASC'); }
    }
    if ($query->is_search()) {
        $query->set('post_type', array('post','page','interworld_object','person','library_book','iw_article','iw_tradition','iw_glossary','iw_source'));
    }
});
add_filter('posts_search', function($search_sql, $query) {
    if (is_admin() || !$query->get('s') || $search_sql === '') return $search_sql;
    global $wpdb;
    $like = '%' . $wpdb->esc_like($query->get('s')) . '%';
    $meta_sql = $wpdb->prepare(
        " OR EXISTS (SELECT 1 FROM {$wpdb->postmeta} AS iw_search_meta WHERE iw_search_meta.post_id = {$wpdb->posts}.ID AND iw_search_meta.meta_key IN ('iw_lead','iw_verified_facts','iw_source_reference_name','iw_source_reference_location','object_short_definition','object_banner_description','kratkoe_opisanie','iw_person_lead') AND iw_search_meta.meta_value LIKE %s)",
        $like
    );
    $closing = strrpos($search_sql, ')');
    if ($closing === false) return $search_sql;
    return substr($search_sql, 0, $closing) . $meta_sql . substr($search_sql, $closing);
}, 10, 2);

add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('interworld-site-templates', get_template_directory_uri() . '/site-templates.css', array('interworld-style'), filemtime(get_template_directory() . '/site-templates.css'));
    if (is_singular(array('interworld_object','library_book','person','iw_article','iw_tradition','iw_glossary','iw_source')) || is_post_type_archive() || is_tax() || is_page_template('page-biblioteka.php') || is_page_template('page-hub.php')) {
        wp_enqueue_style('interworld-knowledge', get_template_directory_uri() . '/knowledge-blocks.css', array('interworld-style'), filemtime(get_template_directory() . '/knowledge-blocks.css'));
        wp_enqueue_script('interworld-knowledge', get_template_directory_uri() . '/knowledge-blocks.js', array(), filemtime(get_template_directory() . '/knowledge-blocks.js'), true);
    }
    if (is_front_page()) wp_enqueue_style('interworld-library', get_template_directory_uri() . '/library-templates.css', array('interworld-site-templates'), filemtime(get_template_directory() . '/library-templates.css'));
}, 40);
