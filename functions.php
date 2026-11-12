<?php
if (!defined('ABSPATH')) exit;
require_once get_template_directory() . '/tpl03-functions.php';
function interworld_setup(){
 add_theme_support('title-tag');
 add_theme_support('post-thumbnails');
 add_theme_support('html5', array('search-form','comment-form','comment-list','gallery','caption','style','script'));
 register_nav_menus(array('primary'=>__('Верхнее меню','interworld')));
}
add_action('after_setup_theme','interworld_setup');
function interworld_assets(){wp_enqueue_style('interworld-style',get_stylesheet_uri(),array(),'0.2.0');}
add_action('wp_enqueue_scripts','interworld_assets');
/* Подключаем стили универсального шаблона категорий */
function interworld_category_assets() {
    if (is_tax('object_type')) {
        wp_enqueue_style(
            'interworld-category',
            get_stylesheet_directory_uri() . '/taxonomy-object_type.css',
            array('interworld-style'),
            filemtime(get_stylesheet_directory() . '/taxonomy-object_type.css')
        );
    }
}

add_action('wp_enqueue_scripts', 'interworld_category_assets', 20);
function interworld_fallback_menu(){
 echo '<ul><li><a href="#explore">Эоны и Эфиры</a></li><li><a href="#creatures">Существа</a></li><li><a href="#spaces">Пространства</a></li><li><a href="#afterlife">Посмертие</a></li><li><a href="#library">Библиотека</a></li></ul>';
}
function interworld_widgets(){register_sidebar(array('name'=>__('Сайтбар','interworld'),'id'=>'sidebar-1','before_widget'=>'<section>','after_widget'=>'</section>','before_title'=>'<h3>','after_title'=>'</h3>'));}
add_action('widgets_init','interworld_widgets');
/* Объекты Межмирья */
function interworld_register_objects() {

    register_post_type('interworld_object', array(
        'labels' => array(
            'name'               => 'Объекты Межмирья',
            'singular_name'      => 'Объект Межмирья',
            'add_new'            => 'Добавить объект',
            'add_new_item'       => 'Добавить объект Межмирья',
            'edit_item'          => 'Редактировать объект',
            'new_item'           => 'Новый объект',
            'view_item'          => 'Просмотреть объект',
            'search_items'       => 'Искать объекты',
            'not_found'          => 'Объекты не найдены',
            'menu_name'          => 'Объекты Межмирья',
        ),

        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_rest'        => true,
        'has_archive'         => true,
        'publicly_queryable'  => true,

    'rewrite' => false,

        'supports' => array(
            'title',
            'editor',
            'thumbnail',
            'excerpt',
        ),

        'menu_icon' => 'dashicons-admin-site-alt3',
    ));
}
add_action('init', 'interworld_register_objects');
/* Красивые URL объектов Межмирья без /obekty/ */
function interworld_object_rewrite_rules() {

    $objects = get_posts(array(
        'post_type'      => 'interworld_object',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ));

    if (empty($objects)) {
        return;
    }

    foreach ($objects as $object_id) {

        $slug = get_post_field('post_name', $object_id);

        if (empty($slug)) {
            continue;
        }

        add_rewrite_rule(
            '^' . preg_quote($slug, '/') . '/?$',
            'index.php?interworld_object=' . $slug,
            'top'
        );
    }
}

add_action('init', 'interworld_object_rewrite_rules', 25);
/* Типы объектов Межмирья */
function interworld_register_object_type_taxonomy() {

    register_taxonomy('object_type', array('interworld_object'), array(

        'labels' => array(
            'name'              => 'Типы объектов',
            'singular_name'     => 'Тип объекта',
            'search_items'      => 'Искать типы объектов',
            'all_items'         => 'Все типы объектов',
            'edit_item'         => 'Редактировать тип объекта',
            'update_item'       => 'Обновить тип объекта',
            'add_new_item'      => 'Добавить тип объекта',
            'new_item_name'     => 'Название нового типа объекта',
            'menu_name'         => 'Типы объектов',
        ),

        'public'             => true,
        'show_ui'            => true,
        'show_admin_column'  => true,
        'show_in_rest'       => true,

        'hierarchical'       => true,

  'rewrite' => false,

    ));
}

add_action('init', 'interworld_register_object_type_taxonomy');

/* Группы объектов Межмирья */
function interworld_register_object_group_taxonomy() {
    register_taxonomy('object_group', array('interworld_object'), array(
        'labels' => array(
            'name'              => 'Группы объектов',
            'singular_name'     => 'Группа объекта',
            'search_items'      => 'Искать группы',
            'all_items'         => 'Все группы',
            'edit_item'         => 'Редактировать группу',
            'update_item'       => 'Обновить группу',
            'add_new_item'      => 'Добавить группу',
            'new_item_name'     => 'Название новой группы',
            'menu_name'         => 'Группы объектов',
        ),
        'public'             => true,
        'show_ui'            => true,
        'show_admin_column'  => true,
        'show_in_rest'       => true,
        'hierarchical'       => true,
        'rewrite'            => false,
    ));
}
add_action('init', 'interworld_register_object_group_taxonomy');
/* Красивые URL и стили страниц групп */
function interworld_object_group_rewrite_rules() {
    $terms = get_terms(array(
        'taxonomy'   => 'object_group',
        'hide_empty' => false,
    ));

    if (is_wp_error($terms) || empty($terms)) {
        return;
    }

    foreach ($terms as $term) {
        $slug = preg_quote($term->slug, '/');

        add_rewrite_rule(
            '^' . $slug . '/?$',
            'index.php?object_group=' . $term->slug,
            'top'
        );

        add_rewrite_rule(
            '^' . $slug . '/page/([0-9]+)/?$',
            'index.php?object_group=' . $term->slug . '&paged=$matches[1]',
            'top'
        );
    }
}
add_action('init', 'interworld_object_group_rewrite_rules', 20);


/* Красивые ссылки групп */
function interworld_object_group_term_link($url, $term, $taxonomy) {
    if ($taxonomy !== 'object_group') {
        return $url;
    }

    return trailingslashit(home_url('/' . $term->slug));
}
add_filter('term_link', 'interworld_object_group_term_link', 10, 3);


/* Подключаем CSS страниц групп */
function interworld_object_group_assets() {
    if (is_tax('object_group')) {
        wp_enqueue_style(
            'interworld-object-group',
            get_stylesheet_directory_uri() . '/taxonomy-object_group.css',
            array('interworld-style'),
            filemtime(get_stylesheet_directory() . '/taxonomy-object_group.css')
        );
    }
}
add_action('wp_enqueue_scripts', 'interworld_object_group_assets', 20);
/* Красивые URL для категорий Межмирья */
function interworld_object_type_rewrite_rules() {

    $terms = get_terms(array(
        'taxonomy'   => 'object_type',
        'hide_empty' => false,
    ));

    if (is_wp_error($terms) || empty($terms)) {
        return;
    }

    foreach ($terms as $term) {

        $slug = preg_quote($term->slug, '/');

        // Страница категории
        add_rewrite_rule(
            '^' . $slug . '/?$',
            'index.php?object_type=' . $term->slug,
            'top'
        );

        // Страницы пагинации категории
        add_rewrite_rule(
            '^' . $slug . '/page/([0-9]+)/?$',
            'index.php?object_type=' . $term->slug . '&paged=$matches[1]',
            'top'
        );
    }
}

add_action('init', 'interworld_object_type_rewrite_rules', 20);


/* Красивые ссылки терминов */
function interworld_object_type_term_link($url, $term, $taxonomy) {

    if ($taxonomy !== 'object_type') {
        return $url;
    }

    return trailingslashit(home_url('/' . $term->slug));
}

add_filter('term_link', 'interworld_object_type_term_link', 10, 3);
/* =========================================
   Автоматический латинский URL объектов Межмирья
   ========================================= */

/* Русская транслитерация */
function interworld_transliterate_slug($text) {

    $text = mb_strtolower($text, 'UTF-8');

    $map = array(
        'а'=>'a',  'б'=>'b',  'в'=>'v',  'г'=>'g',  'д'=>'d',
        'е'=>'e',  'ё'=>'yo', 'ж'=>'zh', 'з'=>'z',  'и'=>'i',
        'й'=>'y',  'к'=>'k',  'л'=>'l',  'м'=>'m',  'н'=>'n',
        'о'=>'o',  'п'=>'p',  'р'=>'r',  'с'=>'s',  'т'=>'t',
        'у'=>'u',  'ф'=>'f',  'х'=>'h',  'ц'=>'ts', 'ч'=>'ch',
        'ш'=>'sh', 'щ'=>'sch','ъ'=>'',   'ы'=>'y',  'ь'=>'',
        'э'=>'e',  'ю'=>'yu', 'я'=>'ya'
    );

    $text = strtr($text, $map);

    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');

    return $text;
}





/* Красивые ссылки объектов без /obekty/ */
function interworld_object_permalink($url, $post) {

    if ($post->post_type !== 'interworld_object') {
        return $url;
    }

    return trailingslashit(
        home_url('/' . $post->post_name)
    );
}

add_filter(
    'post_type_link',
    'interworld_object_permalink',
    10,
    2
);

