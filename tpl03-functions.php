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

function interworld_tpl03_faq($term) {
    $items = array();
    for ($i = 1; $i <= 4; $i++) {
        $q = trim((string) interworld_term_field('iw_tpl03_faq_q_' . $i, $term));
        $a = trim((string) interworld_term_field('iw_tpl03_faq_a_' . $i, $term));
        if ($q !== '' && $a !== '') $items[] = array('q' => $q, 'a' => $a);
    }
    return $items;
}

function interworld_tpl03_links($term, $kind) {
    $items = array();
    for ($i = 1; $i <= 3; $i++) {
        $title = trim((string) interworld_term_field('iw_tpl03_' . $kind . '_title_' . $i, $term));
        $url = trim((string) interworld_term_field('iw_tpl03_' . $kind . '_url_' . $i, $term));
        if ($title !== '' && wp_http_validate_url($url)) {
            $items[] = array('title' => $title, 'url' => $url,
                'description' => (string) interworld_term_field('iw_tpl03_' . $kind . '_description_' . $i, $term));
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
    for ($i = 1; $i <= 4; $i++) {
        $add('iw_tpl03_faq_q_' . $i, 'FAQ ' . $i . ': вопрос');
        $add('iw_tpl03_faq_a_' . $i, 'FAQ ' . $i . ': ответ', 'textarea');
    }
    foreach (array('source' => 'Источник', 'book' => 'Книга') as $kind => $label) {
        for ($i = 1; $i <= 3; $i++) {
            $add('iw_tpl03_' . $kind . '_title_' . $i, $label . ' ' . $i . ': название');
            $add('iw_tpl03_' . $kind . '_url_' . $i, $label . ' ' . $i . ': URL', 'url');
            $add('iw_tpl03_' . $kind . '_description_' . $i, $label . ' ' . $i . ': пояснение', 'textarea');
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
