<?php
/** Generic archive for the theme's editorial sections. */
if (!defined('ABSPATH')) exit;
get_header();
$object = get_queried_object();
$title = post_type_archive_title('', false);
if (!$title && $object instanceof WP_Post_Type) $title = $object->labels->name;
if (!$title) $title = 'Материалы Межмирья';
$lead = $object instanceof WP_Post_Type ? $object->description : '';
?>
<main class="iw-editorial-page iw-editorial-archive">
  <header class="iw-editorial-archive-header"><nav class="iw-editorial-breadcrumbs" aria-label="Хлебные крошки"><a href="<?php echo esc_url(home_url('/')); ?>">Главная</a><span aria-hidden="true">›</span><span><?php echo esc_html($title); ?></span></nav><div class="iw-editorial-eyebrow">ГЕОГРАФИЯ МЕЖМИРЬЯ</div><h1><?php echo esc_html($title); ?></h1><?php if ($lead) : ?><p class="iw-editorial-lead"><?php echo esc_html($lead); ?></p><?php else : ?><p class="iw-editorial-lead">Материалы этого раздела добавляются редактором и проходят проверку перед публикацией.</p><?php endif; ?></header>
  <section class="iw-editorial-catalog" aria-label="Список материалов">
    <form class="iw-catalog-tools" method="get" action="<?php echo esc_url(get_post_type_archive_link(get_query_var('post_type')) ?: home_url('/')); ?>"><label><span class="screen-reader-text">Поиск в разделе</span><input type="search" name="iw_q" value="<?php echo esc_attr(isset($_GET['iw_q']) ? sanitize_text_field(wp_unslash($_GET['iw_q'])) : ''); ?>" placeholder="Найти материал"></label><label><span class="screen-reader-text">Сортировка</span><select name="iw_sort"><option value="alphabetical" <?php selected(isset($_GET['iw_sort']) ? sanitize_key(wp_unslash($_GET['iw_sort'])) : 'alphabetical', 'alphabetical'); ?>>По алфавиту</option><option value="newest" <?php selected(isset($_GET['iw_sort']) ? sanitize_key(wp_unslash($_GET['iw_sort'])) : '', 'newest'); ?>>Сначала новые</option></select></label><button type="submit">Найти</button></form>
    <?php if (have_posts()) : ?><div class="iw-editorial-grid">
      <?php while (have_posts()) : the_post(); $id=get_the_ID(); $item_lead=interworld_content_value('iw_lead',$id); $excerpt=get_the_excerpt(); if (!$excerpt) $excerpt=$item_lead ?: wp_trim_words(wp_strip_all_tags(get_the_content()), 28); ?>
        <article class="iw-editorial-card"><a class="iw-editorial-card-image" href="<?php the_permalink(); ?>"><?php if (has_post_thumbnail()) the_post_thumbnail('medium_large', array('loading'=>'lazy','alt'=>'')); else : ?><span aria-hidden="true">INTERWORLD</span><?php endif; ?></a><div class="iw-editorial-card-copy"><div class="iw-editorial-eyebrow"><?php echo esc_html(get_post_type_object(get_post_type())->labels->singular_name ?? 'Материал'); ?></div><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php if ($excerpt) : ?><p><?php echo esc_html(wp_trim_words(wp_strip_all_tags($excerpt), 28)); ?></p><?php endif; ?><a class="iw-editorial-more" href="<?php the_permalink(); ?>">Открыть материал <span aria-hidden="true">→</span></a></div></article>
      <?php endwhile; ?>
    </div><nav class="iw-editorial-pagination" aria-label="Страницы списка"><?php echo interworld_content_pagination(max(1,(int)get_query_var('paged')),$wp_query->max_num_pages,'list'); ?></nav>
    <?php else : ?><div class="iw-editorial-empty"><h2>Материалы готовятся</h2><p>Здесь появятся проверенные публикации. Пока их нет, в разделе доступен поиск по всему сайту.</p><a href="<?php echo esc_url(home_url('/')); ?>">Вернуться на главную</a></div><?php endif; ?>
  </section>
</main>
<?php get_footer(); ?>
