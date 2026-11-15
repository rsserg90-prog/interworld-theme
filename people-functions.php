<?php
if (!defined('ABSPATH')) exit;
require_once get_template_directory() . '/library-functions.php';

add_action('init', function() {
    if (!post_type_exists('person')) register_post_type('person', array(
        'labels' => array('name' => 'Личности', 'singular_name' => 'Личность', 'add_new_item' => 'Добавить личность', 'edit_item' => 'Редактировать личность'),
        'public' => true, 'show_in_rest' => true, 'has_archive' => 'lichnosti',
        'rewrite' => array('slug' => 'person'), 'menu_icon' => 'dashicons-id',
        'supports' => array('title','editor','thumbnail','excerpt','revisions'),
    ));
    if (!taxonomy_exists('person_category')) register_taxonomy('person_category', array('person'), array(
        'labels' => array('name' => 'Категории личностей', 'singular_name' => 'Категория личности'),
        'public' => true, 'show_in_rest' => true, 'hierarchical' => true,
        'rewrite' => array('slug' => 'lichnosti', 'hierarchical' => true),
        'show_admin_column' => true,
    ));
    else register_taxonomy_for_object_type('person_category', 'person');
}, 15);

add_action('admin_init', function() {
    if (get_option('iw_person_rewrite_version') === '1') return;
    flush_rewrite_rules(false);
    update_option('iw_person_rewrite_version', '1', false);
});

add_action('pre_get_posts', function($query) {
    if (is_admin() || !$query->is_main_query()) return;
    if ($query->is_post_type_archive('person') || $query->is_tax('person_category')) $query->set('posts_per_page', 12);
});

add_action('admin_menu', function() {
    add_submenu_page('edit.php?post_type=person', 'Хаб «Личности»', 'Хаб «Личности»', 'manage_options', 'iw-people-hub', function() {
        if (!current_user_can('manage_options')) return;
        $settings = get_option('iw_people_hub', array());
        echo '<div class="wrap"><h1>Хаб «Личности»</h1><form method="post" action="options.php">';
        settings_fields('iw_people_hub');
        echo '<table class="form-table"><tr><th><label for="iw-people-lead">Краткое вступление</label></th><td><textarea id="iw-people-lead" name="iw_people_hub[lead]" rows="4" class="large-text">' . esc_textarea($settings['lead'] ?? '') . '</textarea></td></tr>';
        echo '<tr><th><label for="iw-people-about">О разделе</label></th><td><textarea id="iw-people-about" name="iw_people_hub[about]" rows="10" class="large-text">' . esc_textarea($settings['about'] ?? '') . '</textarea></td></tr></table>';
        submit_button('Сохранить'); echo '</form></div>';
    });
});
add_action('admin_init', function() {
    register_setting('iw_people_hub', 'iw_people_hub', array('type'=>'array', 'sanitize_callback'=>function($value) {
        return array('lead'=>sanitize_textarea_field($value['lead'] ?? ''), 'about'=>wp_kses_post($value['about'] ?? ''));
    }));
});

add_action('acf/init', function() {
    if (!function_exists('acf_add_local_field_group')) return;
    acf_add_local_field_group(array('key' => 'group_iw_person_profile', 'title' => 'INTERWORLD: карточка личности',
        'fields' => array(
            array('key'=>'field_iw_person_lead','name'=>'iw_person_lead','label'=>'Кто это — краткий ответ','type'=>'textarea'),
            array('key'=>'field_iw_person_period','name'=>'iw_person_period','label'=>'Период','type'=>'text'),
            array('key'=>'field_iw_person_aliases','name'=>'iw_person_aliases','label'=>'Другие имена','type'=>'textarea','instructions'=>'По одному имени в строке'),
            array('key'=>'field_iw_person_facts','name'=>'iw_person_facts','label'=>'Подтверждённые факты','type'=>'textarea','instructions'=>'По одному факту в строке; не указывайте неподтверждённые сведения'),
            array('key'=>'field_iw_person_primary_category','name'=>'iw_person_primary_category','label'=>'Основная категория для навигации','type'=>'taxonomy','taxonomy'=>'person_category','field_type'=>'select','return_format'=>'id','save_terms'=>0,'load_terms'=>0),
            array('key'=>'field_iw_person_authored_books','name'=>'iw_person_authored_books','label'=>'Книги автора','type'=>'post_object','post_type'=>array('library_book'),'multiple'=>1,'ui'=>1,'return_format'=>'id'),
            array('key'=>'field_iw_person_about_books','name'=>'iw_person_about_books','label'=>'Книги о личности','type'=>'post_object','post_type'=>array('library_book'),'multiple'=>1,'ui'=>1,'return_format'=>'id'),
        ), 'location'=>array(array(array('param'=>'post_type','operator'=>'==','value'=>'person'))), 'active'=>true));
    acf_add_local_field_group(array('key'=>'group_iw_person_category', 'title'=>'INTERWORLD: категория личностей',
        'fields'=>array(
            array('key'=>'field_iw_person_cat_lead','name'=>'iw_person_cat_lead','label'=>'Краткое вступление','type'=>'textarea'),
            array('key'=>'field_iw_person_cat_body','name'=>'iw_person_cat_body','label'=>'Описание категории','type'=>'wysiwyg','toolbar'=>'basic','media_upload'=>0),
            array('key'=>'field_iw_person_cat_art','name'=>'iw_person_cat_art','label'=>'Изображение','type'=>'image','return_format'=>'id'),
            array('key'=>'field_iw_person_cat_featured','name'=>'iw_person_cat_featured','label'=>'Избранные личности','type'=>'post_object','post_type'=>array('person'),'multiple'=>1,'ui'=>1,'return_format'=>'id'),
        ), 'location'=>array(array(array('param'=>'taxonomy','operator'=>'==','value'=>'person_category'))), 'active'=>true));
});

function interworld_person_card($id) {
    if (get_post_status($id) !== 'publish') return '';
    $lead = interworld_knowledge_value('iw_person_lead', $id);
    $image = get_the_post_thumbnail($id, 'medium', array('loading'=>'lazy','alt'=>''));
    if (!$image) $image = '<span aria-hidden="true">✦</span>';
    return '<article class="iw-person-card"><a class="iw-person-card-image" href="' . esc_url(get_permalink($id)) . '">' . $image . '</a><div><h3><a href="' . esc_url(get_permalink($id)) . '">' . esc_html(get_the_title($id)) . '</a></h3>' . ($lead ? '<p>' . esc_html(wp_trim_words(wp_strip_all_tags($lead), 24)) . '</p>' : '') . '<a class="iw-person-more" href="' . esc_url(get_permalink($id)) . '">Подробнее →</a></div></article>';
}

add_action('wp_enqueue_scripts', function() {
    if (is_singular('person') || is_tax('person_category') || is_post_type_archive('person')) {
        wp_enqueue_style('interworld-library', get_template_directory_uri() . '/library-templates.css', array('interworld-style'), filemtime(get_template_directory() . '/library-templates.css'));
        wp_enqueue_style('interworld-people', get_template_directory_uri() . '/people.css', array('interworld-style'), filemtime(get_template_directory() . '/people.css'));
    }
}, 25);
