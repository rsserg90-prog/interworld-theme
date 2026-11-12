<?php
/**
 * INTERWORLD — универсальная страница группы объектов.
 *
 * Работает для любой группы taxonomy: object_group.
 * Данные берутся из WordPress: название, описание, объекты и связанные группы.
 */
if (!defined('ABSPATH')) exit;

$term = get_queried_object();

if (!$term || empty($term->term_id)) {
    get_header();
    echo '<main class="interworld-group-page"><div class="group-inner"><p>Группа не найдена.</p></div></main>';
    get_footer();
    return;
}

$term_name = $term->name;
$term_description = interworld_term_field('iw_tpl03_hero_description', $term) ?: term_description($term->term_id, 'object_group');
$term_link = get_term_link($term);

if (is_wp_error($term_link)) {
    $term_link = home_url('/');
}

/*
 * Получаем объекты текущей группы.
 * Сначала выводим все доступные объекты. Когда объектов станет много,
 * здесь можно добавить пагинацию/AJAX без изменения архитектуры страницы.
 */
$page = max(1, (int) get_query_var('paged'));
$objects_query = new WP_Query(array(
    'post_type'      => 'interworld_object',
    'post_status'    => 'publish',
    'posts_per_page' => 12,
    'paged'          => $page,
    'orderby'        => 'title',
    'order'          => 'ASC',
    'tax_query'      => array(
        array(
            'taxonomy' => 'object_group',
            'field'    => 'term_id',
            'terms'    => $term->term_id,
        ),
    ),
));

$objects = $objects_query->posts;
$object_count = (int) $objects_query->found_posts;

/*
 * Определяем типы объектов, которые реально представлены в группе.
 * Ничего не выводим, если объектов пока нет.
 */
$related_types = array();

foreach ($objects as $object) {
    $types = get_the_terms($object->ID, 'object_type');

    if (is_wp_error($types) || empty($types)) {
        continue;
    }

    foreach ($types as $type) {
        $related_types[$type->term_id] = $type;
    }
}

/*
 * Другие группы — контекстная навигация.
 * Текущую группу исключаем.
 */
$parent_type = interworld_parent_type($term);
if ($parent_type) $related_types = array($parent_type->term_id => $parent_type);
$other_groups = $parent_type ? array_values(array_filter(interworld_child_groups($parent_type), function($group) use ($term) {
    return $group->term_id !== $term->term_id;
})) : array();
$intro = interworld_term_field('iw_tpl03_intro', $term);
$faq = interworld_tpl03_faq($term);
$sources = interworld_tpl03_links($term, 'source');
$books = interworld_tpl03_links($term, 'book');

$hero_image = interworld_term_image_url($term);

get_header();
?>

<main class="interworld-group-page">

    <!-- HERO -->
    <section class="group-hero" id="definition">
        <?php if ($hero_image) : ?><img
            class="group-hero-image"
            src="<?php echo esc_url($hero_image); ?>"
            alt="Карта Межмирья — <?php echo esc_attr($term_name); ?>"
            loading="eager"
        ><?php endif; ?>

        <div class="group-hero-content">
<div class="group-breadcrumbs">
    <a href="<?php echo esc_url(home_url('/')); ?>">География Межмирья</a>
    <span> · </span>

    <?php
    /*
     * Родительский тип группы хранится в ACF:
     * Данные группы Межмирья → Тип объектов.
     *
     * Поэтому хлебные крошки не зависят от количества
     * объектов внутри группы.
     */
    if ($parent_type && !is_wp_error($parent_type)) :

        $parent_type_link = get_term_link($parent_type);

        if (!is_wp_error($parent_type_link)) :
    ?>
        <a href="<?php echo esc_url($parent_type_link); ?>">
            <?php echo esc_html($parent_type->name); ?>
        </a>
        <span> · </span>
    <?php
        endif;

    endif;
    ?>

    <span><?php echo esc_html($term_name); ?></span>
</div>

            <div class="group-eyebrow">ГРУППА ОБЪЕКТОВ МЕЖМИРЬЯ</div>

            <h1><?php echo esc_html(interworld_term_field('iw_tpl03_title', $term) ?: $term_name); ?></h1>

            <?php if (interworld_term_field('iw_tpl03_subtitle', $term)) : ?><p class="group-hero-description"><?php echo esc_html(interworld_term_field('iw_tpl03_subtitle', $term)); ?></p><?php endif; ?>

            <?php if (!empty($term_description)) : ?>
                <div class="group-hero-description">
                    <?php echo wp_kses_post($term_description); ?>
                </div>
            <?php endif; ?>

            <div class="group-stats">
                <div class="group-stat">
                    <strong><?php echo esc_html($object_count); ?></strong>
                    <span>объектов</span>
                </div>

                <?php if (!empty($related_types)) : ?>
                    <div class="group-stat">
                        <strong><?php echo esc_html(count($related_types)); ?></strong>
                        <span>типа</span>
                    </div>
                <?php endif; ?>

                <div class="group-stat">
                    <strong>1</strong>
                    <span>группа</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ANCHOR NAV -->
    <nav class="group-anchor-nav" aria-label="Навигация по странице">
        <div class="group-inner group-anchor-inner">
            <a href="#definition">Определение</a>
            <a href="#objects">Объекты</a>
            <?php if (!empty($related_types)) : ?>
                <a href="#structure">Структура</a>
            <?php endif; ?>
            <?php if ($faq) : ?><a href="#faq">Вопросы и ответы</a><?php endif; ?>
            <?php if ($sources) : ?><a href="#sources">Источники</a><?php endif; ?>
            <?php if ($books) : ?><a href="#books">Книги</a><?php endif; ?>
            <?php if ($other_groups || $parent_type) : ?><a href="#related">Связи</a><?php endif; ?>
        </div>
    </nav>

    <!-- INTRO -->
    <?php if ($intro) : ?><section class="group-section" id="about-group">
        <div class="group-inner">
            <div class="group-intro-grid">
                <div>
                    <div class="group-section-eyebrow">О ГРУППЕ</div>
                    <h2><?php echo esc_html(interworld_term_field('iw_tpl03_intro_title', $term) ?: 'О группе'); ?></h2>
                </div>

                <div class="group-intro-copy">
                    <?php echo wp_kses_post($intro); ?>

                    <a class="group-text-link" href="#objects">
                        Перейти к объектам
                    </a>
                </div>
            </div>
        </div>
    </section><?php endif; ?>

    <!-- STRUCTURE -->
    <?php if (!empty($related_types)) : ?>
        <section class="group-section group-structure-section" id="structure">
            <div class="group-inner">
                <div class="group-section-heading">
                    <div class="group-section-eyebrow">СТРУКТУРА</div>
                    <h2>Типы объектов в группе</h2>
                    <p>
                        Типы определяются автоматически по объектам,
                        связанным с этой группой.
                    </p>
                </div>

                <div class="group-type-list">
                    <?php foreach ($related_types as $type) :
                        $type_link = get_term_link($type);
                        if (is_wp_error($type_link)) {
                            continue;
                        }
                    ?>
                        <a class="group-type-item" href="<?php echo esc_url($type_link); ?>">
                            <span><?php echo esc_html($type->name); ?></span>
                            <b aria-hidden="true"></b>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- OBJECTS -->
    <section class="group-section group-objects-section" id="objects">
        <div class="group-inner">
            <div class="group-section-heading">
                <div class="group-section-eyebrow">КАТАЛОГ ГРУППЫ</div>
                <h2>Объекты</h2>
                <p>
                    <?php if ($object_count > 0) : ?>
                        В группе сейчас <?php echo esc_html($object_count); ?> <?php echo esc_html(
                            _n('объект', 'объектов', $object_count, 'interworld')
                        ); ?>.
                    <?php else : ?>
                        Объекты этой группы пока не добавлены в энциклопедию.
                    <?php endif; ?>
                </p>
            </div>

            <?php if (!empty($objects)) : ?>
                <div class="group-card-grid">
                    <?php foreach ($objects as $object) :
                        $object_id = $object->ID;
                        $object_url = get_permalink($object_id);
                        $object_title = get_the_title($object_id);

                        $types = get_the_terms($object_id, 'object_type');
                        $type_name = '';
                        if (!is_wp_error($types) && !empty($types)) {
                            $first_type = reset($types);
                            $type_name = $first_type->name;
                        }

                        $image = get_the_post_thumbnail_url($object_id, 'large');
                        $excerpt = get_the_excerpt($object_id);
                    ?>
                        <article class="group-card">
                            <a class="group-card-image" href="<?php echo esc_url($object_url); ?>">
                                <?php if ($image) : ?>
                                    <img
                                        src="<?php echo esc_url($image); ?>"
                                        alt="<?php echo esc_attr($object_title); ?>"
                                        loading="lazy"
                                    >
                                <?php else : ?>
                                    <span class="group-card-image-placeholder">INTERWORLD</span>
                                <?php endif; ?>
                            </a>

                            <div class="group-card-body">
                                <?php if ($type_name) : ?>
                                    <div class="group-card-type">
                                        <?php echo esc_html($type_name); ?>
                                    </div>
                                <?php endif; ?>

                                <h3>
                                    <a href="<?php echo esc_url($object_url); ?>">
                                        <?php echo esc_html($object_title); ?>
                                    </a>
                                </h3>

                                <?php if ($excerpt) : ?>
                                    <p><?php echo esc_html($excerpt); ?></p>
                                <?php endif; ?>

                                <a class="group-card-link" href="<?php echo esc_url($object_url); ?>" aria-label="Открыть <?php echo esc_attr($object_title); ?>">
                                    <span aria-hidden="true"></span>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="group-empty-state">
                    <div class="group-empty-mark" aria-hidden="true">—</div>
                    <h3>Объекты пока не добавлены</h3>
                    <p>
                        После создания объектов они автоматически появятся
                        здесь и будут связаны с этой группой.
                    </p>
                </div>
            <?php endif; ?>
            <?php if ($objects_query->max_num_pages > 1) : ?>
                <nav class="group-pagination" aria-label="Страницы каталога">
                    <?php echo wp_kses_post(paginate_links(array('current' => $page, 'total' => $objects_query->max_num_pages, 'type' => 'list', 'prev_text' => '←', 'next_text' => '→'))); ?>
                </nav>
            <?php endif; ?>
        </div>
    </section>

    <!-- SOURCES -->
    <?php if ($faq) : ?><section class="group-section" id="faq"><div class="group-inner"><div class="group-section-heading"><div class="group-section-eyebrow">ЧАСТЫЕ ВОПРОСЫ</div><h2>Вопросы и ответы</h2></div><?php foreach ($faq as $item) : ?><details><summary><?php echo esc_html($item['q']); ?></summary><?php echo wp_kses_post(wpautop($item['a'])); ?></details><?php endforeach; ?></div></section><?php endif; ?>
    <?php if ($sources) : ?><section class="group-section group-sources-section" id="sources">
        <div class="group-inner">
            <div class="group-section-heading">
                <div class="group-section-eyebrow">ИСТОЧНИКИ</div>
                <h2>Материалы для исследования</h2>
            </div>

            <div class="group-source-grid">
                <?php foreach ($sources as $source) : ?><article class="group-source-card">
                    <div>
                        <h3><a href="<?php echo esc_url($source['url']); ?>"><?php echo esc_html($source['title']); ?></a></h3>
                        <?php if ($source['description']) : ?><p><?php echo esc_html($source['description']); ?></p><?php endif; ?>
                    </div>
                </article><?php endforeach; ?>
            </div>
        </div>
    </section><?php endif; ?>
    <?php if ($books) : ?><section class="group-section group-sources-section" id="books"><div class="group-inner"><div class="group-section-heading"><h2>Книги</h2></div><div class="group-source-grid"><?php foreach ($books as $book) : ?><article class="group-source-card"><div><h3><a href="<?php echo esc_url($book['url']); ?>"><?php echo esc_html($book['title']); ?></a></h3><?php if ($book['description']) : ?><p><?php echo esc_html($book['description']); ?></p><?php endif; ?></div></article><?php endforeach; ?></div></div></section><?php endif; ?>

    <!-- RELATED -->
    <?php if ($other_groups || $parent_type) : ?><section class="group-section group-related-section" id="related">
        <div class="group-inner">
            <div class="group-section-heading">
                <div class="group-section-eyebrow">НАВИГАЦИЯ</div>
                <h2>Другие группы</h2>
                <p>
                    Переходите между группами и исследуйте Межмирье как связанную систему.
                </p>
            </div>

            <?php if (!empty($other_groups) && !is_wp_error($other_groups)) : ?>
                <div class="group-related-list">
                    <?php foreach ($other_groups as $other_group) :
                        $other_link = get_term_link($other_group);
                        if (is_wp_error($other_link)) {
                            continue;
                        }
                    ?>
                        <a class="group-related-item" href="<?php echo esc_url($other_link); ?>">
                            <span>
                                <small>ГРУППА</small>
                                <?php echo esc_html($other_group->name); ?>
                            </span>
                            <b aria-hidden="true"></b>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($related_types)) : ?>
                <div class="group-related-types">
                    <?php foreach ($related_types as $type) :
                        $type_link = get_term_link($type);
                        if (is_wp_error($type_link)) {
                            continue;
                        }
                    ?>
                        <a href="<?php echo esc_url($type_link); ?>">
                            Все объекты типа «<?php echo esc_html($type->name); ?>»
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section><?php endif; ?>

    <!-- FINAL CTA -->
    <section class="group-final-cta">
        <div class="group-inner group-final-inner">
            <div class="group-section-eyebrow">INTERWORLD</div>
            <h2>Продолжить исследование Межмирья</h2>
            <p>
                Переходите к другим категориям, группам и отдельным объектам,
                чтобы собирать общую карту знаний.
            </p>
            <a href="<?php echo esc_url(home_url('/')); ?>" class="group-final-link">
                Вернуться к Географии Межмирья
            </a>
        </div>
    </section>

</main>

<?php
wp_reset_postdata();
get_footer();
?>
