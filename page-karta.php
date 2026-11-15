<?php
/**
 * Template Name: Карта Межмирья
 * Template Post Type: page
 * Accessible map image with a searchable text index of the encyclopedia.
 */
if (!defined('ABSPATH')) exit;
get_header();
$page_id = get_queried_object_id();
$page_title = get_the_title($page_id);
$lead = interworld_content_value('iw_page_lead', $page_id);
$body = interworld_content_value('iw_page_body', $page_id);
$image = interworld_content_image_url(interworld_content_value('iw_page_hero_image', $page_id), 'full');
if (!$image) $image = get_the_post_thumbnail_url($page_id, 'full') ?: '';
$search = isset($_GET['iw_q']) ? sanitize_text_field(wp_unslash($_GET['iw_q'])) : '';
$sort = isset($_GET['iw_sort']) ? sanitize_key(wp_unslash($_GET['iw_sort'])) : 'alphabetical';
$map_items = new WP_Query(interworld_content_catalog_args(array('post_type'=>'interworld_object','post_status'=>'publish','posts_per_page'=>24,'paged'=>max(1,(int)get_query_var('paged')),'no_found_rows'=>false,'orderby'=>$sort==='newest'?'date':'title','order'=>$sort==='newest'?'DESC':'ASC')));
if (have_posts()) the_post();
?>
<main class="iw-editorial-page iw-map-page">
  <header class="iw-editorial-archive-header"><nav class="iw-editorial-breadcrumbs" aria-label="Хлебные крошки"><a href="<?php echo esc_url(home_url('/')); ?>">Главная</a><span aria-hidden="true">›</span><span><?php echo esc_html($page_title); ?></span></nav><div class="iw-editorial-eyebrow">ВИЗУАЛЬНЫЙ ПУТЕВОДИТЕЛЬ</div><h1><?php echo esc_html($page_title); ?></h1><?php if ($lead) : ?><p class="iw-editorial-lead"><?php echo esc_html($lead); ?></p><?php endif; ?></header>
  <?php if ($image) : ?><figure class="iw-map-figure"><a href="<?php echo esc_url($image); ?>" target="_blank" rel="noopener"><img src="<?php echo esc_url($image); ?>" alt="Карта Межмирья" loading="eager"></a><figcaption>Откройте изображение в полном размере. Элементы карты перечислены ниже текстом.</figcaption></figure><?php else : ?><div class="iw-editorial-empty"><h2>Карта готовится</h2><p>Добавьте изображение карты в поле «Изображение первого экрана или карта» и краткое описание.</p></div><?php endif; ?>
  <?php if ($body || trim(wp_strip_all_tags(get_post_field('post_content',$page_id)))) : ?><section class="iw-editorial-body"><?php if ($body) echo wp_kses_post(wpautop($body)); echo apply_filters('the_content', get_post_field('post_content',$page_id)); ?></section><?php endif; ?>
  <section class="iw-editorial-catalog" aria-labelledby="iw-map-index-heading"><div class="iw-editorial-section-heading"><div class="iw-editorial-eyebrow">ТЕКСТОВЫЙ УКАЗАТЕЛЬ</div><h2 id="iw-map-index-heading">Объекты на карте</h2><p>Список помогает найти объекты без визуальной карты и перейти к их отдельным описаниям.</p></div>
    <form class="iw-catalog-tools" method="get"><label><span class="screen-reader-text">Поиск по объектам</span><input type="search" name="iw_q" value="<?php echo esc_attr($search); ?>" placeholder="Найти объект"></label><label><span class="screen-reader-text">Сортировка</span><select name="iw_sort"><option value="alphabetical" <?php selected($sort,'alphabetical'); ?>>По алфавиту</option><option value="newest" <?php selected($sort,'newest'); ?>>Сначала новые</option></select></label><button type="submit">Найти</button></form>
    <?php if ($map_items->have_posts()) : ?><ul class="iw-map-index"><?php while ($map_items->have_posts()) : $map_items->the_post(); $terms=get_the_terms(get_the_ID(),'object_type'); ?><li><a href="<?php the_permalink(); ?>"><strong><?php the_title(); ?></strong><?php if ($terms && !is_wp_error($terms)) : ?><span><?php echo esc_html(implode(', ', wp_list_pluck($terms,'name'))); ?></span><?php endif; ?><?php if (has_excerpt()) : ?><small><?php echo esc_html(get_the_excerpt()); ?></small><?php endif; ?></a></li><?php endwhile; ?></ul><nav class="iw-editorial-pagination" aria-label="Страницы списка"><?php echo interworld_content_pagination(max(1,(int)get_query_var('paged')),$map_items->max_num_pages,'list'); ?></nav><?php else : ?><div class="iw-editorial-empty"><h2>Объекты пока не добавлены</h2><p>Когда редактор опубликует первые карточки, они появятся в этом указателе.</p></div><?php endif; wp_reset_postdata(); ?>
  </section>
</main>
<?php get_footer(); ?>
