<?php
/** Shared data contract for object type and group term pages. */
if (!defined('ABSPATH')) exit;

function interworld_term_field($name, $term) {
    if (!function_exists('get_field') || !($term instanceof WP_Term)) return null;
    return get_field($name, $term->taxonomy . '_' . $term->term_id);
}

function interworld_parent_type($group) {
    $value = interworld_term_field('object_parent_type', $group);
    if ($value instanceof WP_Term) return $value->taxonomy === 'object_type' ? $value : null;
    if (is_array($value)) $value = reset($value);
    if (is_numeric($value)) {
        $term = get_term((int) $value, 'object_type');
        return $term instanceof WP_Term ? $term : null;
    }
    return null;
}

function interworld_child_groups($type) {
    $terms = get_terms(array('taxonomy' => 'object_group', 'hide_empty' => false, 'orderby' => 'name'));
    if (is_wp_error($terms)) return array();
    return array_values(array_filter($terms, function($group) use ($type) {
        $parent = interworld_parent_type($group);
        return $parent && (int) $parent->term_id === (int) $type->term_id;
    }));
}

function interworld_term_image_url($term) {
    $image = interworld_term_field('iw_tpl03_hero_image', $term);
    if (is_numeric($image)) return wp_get_attachment_image_url((int) $image, 'full') ?: '';
    if (is_array($image) && !empty($image['url'])) return $image['url'];
    return '';
}

function interworld_object_count_word($count) {
    $last_two = $count % 100;
    if ($last_two >= 11 && $last_two <= 14) return 'объектов';
    $last = $count % 10;
    if ($last === 1) return 'объект';
    if ($last >= 2 && $last <= 4) return 'объекта';
    return 'объектов';
}

function interworld_tpl03_count_label($count, $term) {
    $type = $term->taxonomy === 'object_group' ? interworld_parent_type($term) : $term;
    if ($type && $type->slug === 'efiry') {
        $n = $count % 100;
        if ($n >= 11 && $n <= 14) return 'эфиров';
        $n = $count % 10;
        return $n === 1 ? 'эфир' : ($n >= 2 && $n <= 4 ? 'эфира' : 'эфиров');
    }
    return interworld_term_field('iw_tpl03_count_label', $type) ?: interworld_object_count_word($count);
}

function interworld_tpl03_related($term, $field) {
    $items = array();
    foreach ((array) interworld_term_field($field, $term) as $id) {
        $id = $id instanceof WP_Post ? $id->ID : (int) $id;
        if ($id && get_post_status($id) === 'publish') $items[$id] = $id;
    }
    return array_values($items);
}

function interworld_tpl03_related_terms($term) {
    $terms = array();
    foreach (array('object_type' => 'iw_tpl03_topic_types', 'object_group' => 'iw_tpl03_topic_groups') as $taxonomy => $field) {
        foreach ((array) interworld_term_field($field, $term) as $value) {
            $id = $value instanceof WP_Term ? $value->term_id : (int) $value;
            $related = $id ? get_term($id, $taxonomy) : null;
            if ($related instanceof WP_Term && $related->term_id !== $term->term_id) $terms[$taxonomy . $id] = $related;
        }
    }
    return array_values($terms);
}

function interworld_tpl03_relation_labels() {
    return array('aeon' => 'Эоны', 'ether' => 'Эфиры', 'archon' => 'Архонты',
        'deity' => 'Боги | Божества', 'space' => 'Пространства Межмирья',
        'symbol' => 'Символы и атрибуты', 'creature' => 'Существа Промежутка',
        'fairy' => 'Фейри', 'city' => 'Города Межмирья', 'book' => 'Книги',
        'afterlife' => 'Посмертие (Дуат)', 'other' => 'Другие связи');
}

function interworld_tpl03_relations($term) {
    $groups = array();
    $labels = interworld_tpl03_relation_labels();
    $rows = get_term_meta($term->term_id, '_iw_tpl03_relations', true);
    foreach (is_array($rows) ? $rows : array() as $row) {
        $id = absint($row['id'] ?? 0);
        $type = sanitize_key($row['type'] ?? 'other');
        if ($id && get_post_type($id) === 'interworld_object' && get_post_status($id) === 'publish' && isset($labels[$type])) $groups[$type][$id] = $id;
    }
    // Earlier fields remain readable until their contents are moved into the new editor.
    foreach (array('iw_tpl03_creatures' => 'creature', 'iw_tpl03_spaces' => 'space') as $field => $fallback) {
        foreach (interworld_tpl03_related($term, $field) as $id) {
            $types = get_the_terms($id, 'object_type');
            $type = $fallback;
            if (is_array($types) && $types) {
                $name = reset($types)->name;
                foreach ($labels as $key => $label) if ($name === $label) { $type = $key; break; }
            }
            $groups[$type][$id] = $id;
        }
    }
    $ordered = array();
    foreach ($labels as $key => $label) if (!empty($groups[$key])) $ordered[$key] = array($label, array_values($groups[$key]));
    return $ordered;
}

function interworld_tpl03_faq($term) {
    $items = array();
    for ($i = 1; $i <= 4; $i++) {
        $q = trim((string) interworld_term_field('iw_tpl03_faq_q_' . $i, $term));
        $a = trim((string) interworld_term_field('iw_tpl03_faq_a_' . $i, $term));
        if ($q !== '' && $a !== '') $items[] = array('q' => $q, 'a' => $a);
    }
    $extra = get_term_meta($term->term_id, '_iw_tpl03_faq_extra', true);
    if (is_array($extra)) foreach ($extra as $item) {
        if (!empty($item['q']) && !empty($item['a'])) $items[] = $item;
    }
    return $items;
}

function interworld_tpl03_links($term, $kind) {
    $items = array();
    if ($kind === 'book') {
        $ids = (array) interworld_term_field('iw_tpl03_library_books', $term);
        foreach ($ids as $id) {
            $id = $id instanceof WP_Post ? $id->ID : (int) $id;
            if (!$id || get_post_type($id) !== 'library_book' || get_post_status($id) !== 'publish') continue;
            $cover = get_the_post_thumbnail_url($id, 'medium');
            if (!$cover && function_exists('get_field')) {
                $field = get_field('oblozhka_knigi', $id);
                if (is_array($field)) $cover = $field['url'] ?? '';
                elseif (is_numeric($field)) $cover = wp_get_attachment_image_url((int) $field, 'medium');
                elseif (is_string($field)) $cover = $field;
            }
            $items[] = array('title' => get_the_title($id), 'url' => get_permalink($id),
                'description' => get_the_excerpt($id), 'image' => $cover);
        }
    }
    for ($i = 1; $i <= 3; $i++) {
        $title = trim((string) interworld_term_field('iw_tpl03_' . $kind . '_title_' . $i, $term));
        $url = trim((string) interworld_term_field('iw_tpl03_' . $kind . '_url_' . $i, $term));
        if ($title !== '' && wp_http_validate_url($url)) {
            $image = interworld_term_field('iw_tpl03_' . $kind . '_image_' . $i, $term);
            $items[] = array('title' => $title, 'url' => $url,
                'description' => (string) interworld_term_field('iw_tpl03_' . $kind . '_description_' . $i, $term),
                'image' => is_numeric($image) ? (wp_get_attachment_image_url((int) $image, 'medium') ?: '') : '');
        }
    }
    return $items;
}

add_action('acf/init', function() {
    if (!function_exists('acf_add_local_field_group')) return;
    $fields = array();
    $add = function($name, $label, $type = 'text', $extra = array()) use (&$fields) {
        $fields[] = array_merge(array('key' => 'field_' . $name, 'name' => $name,
            'label' => $label, 'type' => $type), $extra);
    };
    $add('iw_tpl03_title', 'Заголовок H1 (пусто = имя категории)');
    $add('iw_tpl03_subtitle', 'Подзаголовок');
    $add('iw_tpl03_hero_image', 'Изображение первого экрана', 'image', array('return_format' => 'id'));
    $add('iw_tpl03_hero_description', 'Краткое описание на первом экране', 'textarea');
    $add('iw_tpl03_intro_title', 'Заголовок вводного блока');
    $add('iw_tpl03_intro', 'Вводный текст', 'wysiwyg', array('toolbar' => 'basic', 'media_upload' => 0));
    $add('iw_tpl03_system_title', 'Заголовок блока структуры');
    $add('iw_tpl03_system_intro', 'Вводный текст структуры', 'textarea');
    $add('iw_tpl03_group_preview', 'Краткое описание для карточки группы', 'textarea');
    $add('iw_tpl03_group_image', 'Изображение карточки группы', 'image', array('return_format' => 'id'));
    $add('iw_tpl03_count_label', 'Название карточек в счётчике (для других типов)');
    $add('iw_tpl03_catalog_title', 'Заголовок списка карточек');
    $add('iw_tpl03_seo_title', 'Заголовок развёрнутого описания');
    $add('iw_tpl03_seo_text', 'Развёрнутое описание', 'wysiwyg', array('toolbar' => 'full', 'media_upload' => 0));
    $add('iw_tpl03_faq_title', 'Заголовок вопросов и ответов');
    foreach (array('creatures' => 'Связанные существа', 'spaces' => 'Связанные пространства', 'people' => 'Личности и контекст', 'topics' => 'Темы для продолжения') as $key => $label) {
        $add('iw_tpl03_' . $key, $label, 'post_object', array('post_type' => array('interworld_object', 'page', 'post'), 'return_format' => 'id', 'multiple' => 1, 'ui' => 1));
    }
    $add('iw_tpl03_topic_types', 'Связанные категории', 'taxonomy', array('taxonomy' => 'object_type', 'field_type' => 'multi_select', 'return_format' => 'id', 'save_terms' => 0, 'load_terms' => 0));
    $add('iw_tpl03_topic_groups', 'Связанные группы', 'taxonomy', array('taxonomy' => 'object_group', 'field_type' => 'multi_select', 'return_format' => 'id', 'save_terms' => 0, 'load_terms' => 0));
    $add('iw_tpl03_library_books', 'Связанные книги библиотеки', 'post_object',
        array('post_type' => array('library_book'), 'return_format' => 'id', 'multiple' => 1, 'ui' => 1));
    for ($i = 1; $i <= 4; $i++) {
        $add('iw_tpl03_faq_q_' . $i, 'FAQ ' . $i . ': вопрос');
        $add('iw_tpl03_faq_a_' . $i, 'FAQ ' . $i . ': ответ', 'textarea');
    }
    foreach (array('source' => 'Источник', 'book' => 'Книга') as $kind => $label) {
        for ($i = 1; $i <= 3; $i++) {
            $add('iw_tpl03_' . $kind . '_title_' . $i, $label . ' ' . $i . ': название');
            $add('iw_tpl03_' . $kind . '_url_' . $i, $label . ' ' . $i . ': URL', 'url');
            $add('iw_tpl03_' . $kind . '_description_' . $i, $label . ' ' . $i . ': пояснение', 'textarea');
            if ($kind === 'source') $add('iw_tpl03_source_image_' . $i, $label . ' ' . $i . ': изображение', 'image', array('return_format' => 'id'));
        }
    }
    acf_add_local_field_group(array(
        'key' => 'group_iw_tpl03', 'title' => 'INTERWORLD: содержание хаба и группы',
        'fields' => $fields,
        'location' => array(
            array(array('param' => 'taxonomy', 'operator' => '==', 'value' => 'object_type')),
            array(array('param' => 'taxonomy', 'operator' => '==', 'value' => 'object_group')),
        ),
        'position' => 'normal', 'style' => 'default', 'active' => true,
    ));
});
