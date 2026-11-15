<?php
/** TPL-03 group page. */
if (!defined('ABSPATH')) exit;
$term = get_queried_object();
if (!($term instanceof WP_Term)) { get_header(); get_footer(); return; }
$parent = interworld_parent_type($term);
$title = interworld_term_field('iw_tpl03_title', $term) ?: $term->name;
$subtitle = interworld_term_field('iw_tpl03_subtitle', $term);
$description = interworld_term_field('iw_tpl03_hero_description', $term) ?: term_description($term->term_id, 'object_group');
$image = interworld_term_image_url($term);
$intro = interworld_term_field('iw_tpl03_intro', $term);
$page = max(1, (int) get_query_var('paged'));
$object_args = array('post_type' => 'interworld_object', 'post_status' => 'publish', 'posts_per_page' => 12, 'paged' => $page, 'orderby' => 'title', 'order' => 'ASC', 'tax_query' => array(array('taxonomy' => 'object_group', 'field' => 'term_id', 'terms' => $term->term_id)));
$object_args = interworld_content_catalog_args($object_args);
$objects = new WP_Query($object_args);
$count = (int) $objects->found_posts;
$catalog_title = interworld_term_field('iw_tpl03_catalog_title', $term) ?: ($parent ? $parent->name : 'Объекты');
get_header(); ?>
<main class="interworld-group-page">
<section class="group-hero" id="definition">
<?php if ($image) : ?><img class="group-hero-image" src="<?php echo esc_url($image); ?>" alt="" loading="eager"><?php endif; ?>
<div class="group-hero-content"><div class="group-breadcrumbs"><a href="<?php echo esc_url(home_url('/')); ?>">География Межмирья</a> · <?php if ($parent && !is_wp_error(get_term_link($parent))) : ?><a href="<?php echo esc_url(get_term_link($parent)); ?>"><?php echo esc_html($parent->name); ?></a> · <?php endif; ?><span><?php echo esc_html($term->name); ?></span></div>
<h1><?php echo esc_html($title); ?></h1>
<?php if ($subtitle) : ?><p class="group-hero-subtitle"><?php echo esc_html($subtitle); ?></p><?php endif; ?>
<?php if ($description) : ?><div class="group-hero-description"><?php echo wp_kses_post($description); ?></div><?php endif; ?>
<div class="group-stats"><div class="group-stat"><strong><?php echo esc_html($count); ?></strong><span><?php echo esc_html(interworld_tpl03_count_label($count, $term)); ?></span></div></div></div></section>
<nav class="group-anchor-nav" aria-label="Навигация по странице"><div class="group-inner group-anchor-inner"><a href="#definition">Начало</a><?php if ($intro) : ?><a href="#about-group">Определение</a><?php endif; ?><a href="#objects"><?php echo esc_html($catalog_title); ?></a><?php interworld_tpl03_anchor_links($term); ?></div></nav>
<div class="group-inner"><?php interworld_content_catalog_form('Найти объект'); ?></div>
<?php if ($intro) : ?><section class="group-section" id="about-group"><div class="group-inner"><div class="group-intro-grid"><h2><?php echo esc_html(interworld_term_field('iw_tpl03_intro_title', $term) ?: 'Что такое ' . $title); ?></h2><div class="group-intro-copy"><?php echo wp_kses_post($intro); ?></div></div></div></section><?php endif; ?>
<section class="group-section group-objects-section" id="objects"><div class="group-inner"><div class="group-section-heading"><h2><?php echo esc_html($catalog_title); ?></h2></div>
<?php if ($count) : ?><div class="group-card-grid"><?php foreach ($objects->posts as $object) : $id=$object->ID; $thumb=get_the_post_thumbnail_url($id,'large'); $excerpt=get_the_excerpt($id); if (!$excerpt) $excerpt=interworld_content_value('object_short_definition',$id); ?><article class="group-card"><a class="group-card-image" href="<?php echo esc_url(get_permalink($id)); ?>"><?php if ($thumb) : ?><img src="<?php echo esc_url($thumb); ?>" alt="" loading="lazy"><?php else : ?><span class="group-card-image-placeholder">INTERWORLD</span><?php endif; ?></a><div class="group-card-body"><h3><a href="<?php echo esc_url(get_permalink($id)); ?>"><?php echo esc_html(get_the_title($id)); ?></a></h3><?php if ($excerpt) : ?><p><?php echo esc_html($excerpt); ?></p><?php endif; ?><a class="group-card-link" href="<?php echo esc_url(get_permalink($id)); ?>">Подробнее →</a></div></article><?php endforeach; ?></div><?php if ($objects->max_num_pages > 1) : ?><nav class="group-pagination" aria-label="Страницы списка"><?php echo interworld_content_pagination($page,$objects->max_num_pages,'list'); ?></nav><?php endif; ?><?php else : ?><p class="group-empty-state"><?php echo esc_html($catalog_title); ?> этого раздела пока не добавлены.</p><?php endif; ?>
</div></section>
<?php interworld_tpl03_render_sections($term); ?>
</main>
<?php wp_reset_postdata(); get_footer(); ?>
