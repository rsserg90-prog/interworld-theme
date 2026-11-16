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

// The existing Enmerkar record keeps its public URL after its post type changes.
add_action('init', function() {
    if (get_post_type(117) === 'person' && get_post_field('post_name', 117) === 'enmerkar') {
        add_rewrite_rule('^enmerkar/?$', 'index.php?person=enmerkar', 'top');
    }
}, 30);
add_filter('post_type_link', function($url, $post) {
    if ($post->post_type === 'person' && $post->post_name === 'enmerkar') return home_url('/enmerkar/');
    return $url;
}, 20, 2);

add_action('admin_menu', function() {
    add_submenu_page('edit.php?post_type=person', 'Перенос Энмеркара', 'Перенос Энмеркара', 'manage_options', 'iw-migrate-enmerkar', function() {
        if (!current_user_can('manage_options')) return;
        $post = get_post(117);
        echo '<div class="wrap"><h1>Перенос Энмеркара</h1>';
        if (!$post || $post->post_name !== 'enmerkar') { echo '<p>Исходная запись не найдена. Перенос не выполнялся.</p></div>'; return; }
        if ($post->post_type === 'person') { echo '<p>Запись уже перенесена. Адрес: <a href="' . esc_url(get_permalink($post)) . '">/enmerkar/</a>.</p></div>'; return; }
        echo '<p>Запись №117 будет переведена из «Объектов Межмирья» в «Личности». ID, содержимое, изображение и метаполя сохранятся. Перед действием нужна свежая резервная копия WordPress.</p>';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="iw_migrate_enmerkar">';
        wp_nonce_field('iw_migrate_enmerkar'); submit_button('Перенести запись'); echo '</form></div>';
    });
});
add_action('admin_post_iw_migrate_enmerkar', function() {
    if (!current_user_can('manage_options')) wp_die('Недостаточно прав.');
    check_admin_referer('iw_migrate_enmerkar');
    $post = get_post(117);
    if (!$post || $post->post_type !== 'interworld_object' || $post->post_name !== 'enmerkar') wp_die('Исходная запись изменилась. Перенос отменён.');
    $duplicate = get_page_by_path('enmerkar', OBJECT, 'person');
    if ($duplicate) wp_die('Карточка личности с таким адресом уже существует. Перенос отменён.');
    $result = wp_update_post(array('ID'=>117,'post_type'=>'person'), true);
    if (is_wp_error($result)) wp_die(esc_html($result->get_error_message()));
    $role_ids = array();
    foreach (array('Авторы'=>'avtory','Маги'=>'magi','Исследователи'=>'issledovateli') as $name=>$slug) {
        $existing = term_exists($slug, 'person_category');
        if (!$existing) $existing = wp_insert_term($name, 'person_category', array('slug'=>$slug));
        if (!is_wp_error($existing)) $role_ids[$slug] = (int)(is_array($existing) ? $existing['term_id'] : $existing);
    }
    if ($role_ids) wp_set_object_terms(117, array_values($role_ids), 'person_category', false);
    if (!empty($role_ids['avtory'])) {
        if (function_exists('update_field')) update_field('field_iw_person_primary_category', $role_ids['avtory'], 117);
        else update_post_meta(117, 'iw_person_primary_category', $role_ids['avtory']);
    }
    if (get_post_type(138) === 'library_book') {
        $authors = interworld_knowledge_ids('iw_book_authors', 138);
        $authors[] = 117;
        $authors = array_values(array_unique($authors));
        if (function_exists('update_field')) update_field('field_iw_book_authors', $authors, 138);
        else update_post_meta(138, 'iw_book_authors', $authors);
        update_post_meta(138, '_iw_book_author_index', $authors);
        $books = array_map('absint', (array)get_post_meta(117, '_iw_person_authored_book_ids', true));
        $books[] = 138;
        update_post_meta(117, '_iw_person_authored_book_ids', array_values(array_unique($books)));
    }
    add_rewrite_rule('^enmerkar/?$', 'index.php?person=enmerkar', 'top');
    flush_rewrite_rules(false);
    wp_safe_redirect(admin_url('edit.php?post_type=person&page=iw-migrate-enmerkar'));
    exit;
});

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
