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
$term_description = term_description($term->term_id, 'object_group');
$term_link = get_term_link($term);

if (is_wp_error($term_link)) {
    $term_link = home_url('/');
}

/*
 * Получаем объекты текущей группы.
 * Сначала выводим все доступные объекты. Когда объектов станет много,
 * здесь можно добавить пагинацию/AJAX без изменения архитектуры страницы.
 */
$objects_query = new WP_Query(array(
    'post_type'      => 'interworld_object',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
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
$other_groups = get_terms(array(
    'taxonomy'   => 'object_group',
    'hide_empty' => false,
    'exclude'    => array($term->term_id),
    'orderby'    => 'name',
    'order'      => 'ASC',
));

$hero_image = 'https://geografiya-mezhmirya.ru/wp-content/uploads/2026/09/karta-meszmiria.webp';

get_header();
?>

<main class="interworld-group-page">

    <!-- HERO -->
    <section class="group-hero" id="definition">
        <img
            class="group-hero-image"
            src="<?php echo esc_url($hero_image); ?>"
            alt="Карта Межмирья — <?php echo esc_attr($term_name); ?>"
            loading="eager"
        >

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
    $parent_type = get_field(
        'object_parent_type',
        'object_group_' . $term->term_id
    );

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

            <h1><?php echo esc_html($term_name); ?></h1>

            <?php if (!empty($term_description)) : ?>
                <div class="group-hero-description">
                    <?php echo wp_kses_post($term_description); ?>
                </div>
            <?php else : ?>
                <p class="group-hero-description group-description-empty">
                    Энциклопедическая страница группы. Описание будет дополнено
                    после подтверждения материала по источникам проекта.
                </p>
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
            <a href="#sources">Источники</a>
            <a href="#related">Связи</a>
        </div>
    </nav>

    <!-- INTRO -->
    <section class="group-section" id="about-group">
        <div class="group-inner">
            <div class="group-intro-grid">
                <div>
                    <div class="group-section-eyebrow">О ГРУППЕ</div>
                    <h2>Место группы<br>в Межмирье</h2>
                </div>

                <div class="group-intro-copy">
                    <p>
                        Эта страница объединяет объекты, отнесённые к группе
                        «<?php echo esc_html($term_name); ?>».
                    </p>

                    <p>
                        Здесь постепенно будут собираться определения,
                        описания объектов, взаимосвязи, источники и материалы
                        для дальнейшего исследования.
                    </p>

                    <a class="group-text-link" href="#objects">
                        Перейти к объектам
                    </a>
                </div>
            </div>
        </div>
    </section>

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
        </div>
    </section>

    <!-- SOURCES -->
    <section class="group-section group-sources-section" id="sources">
        <div class="group-inner">
            <div class="group-section-heading">
                <div class="group-section-eyebrow">ИСТОЧНИКИ</div>
                <h2>Материалы для исследования</h2>
                <p>
                    Источники будут расширяться по мере наполнения базы знаний.
                </p>
            </div>

            <div class="group-source-grid">
                <article class="group-source-card">
                    <div class="group-source-thumb">
                        <img
                            src="https://geografiya-mezhmirya.ru/wp-content/uploads/2026/09/geografia-mezhmiria-1.webp"
                            alt="География Межмирья. Том 1. Эоны и Эфиры"
                            loading="lazy"
                        >
                    </div>
                    <div>
                        <div class="group-source-type">ОСНОВНОЙ ИСТОЧНИК</div>
                        <h3>География Межмирья. Том 1. Эоны и Эфиры</h3>
                        <p>
                            Материал, в котором описывается система Эонов и Эфиров Межмирья.
                        </p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <!-- RELATED -->
    <section class="group-section group-related-section" id="related">
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
    </section>

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
