<?php
/** Required classic-theme fallback. */
if (!defined('ABSPATH')) exit;
get_header();
?>
<main class="iw-editorial-page"><header class="iw-editorial-archive-header"><div class="iw-editorial-eyebrow">ГЕОГРАФИЯ МЕЖМИРЬЯ</div><h1><?php echo is_home() ? 'Статьи' : 'Материалы'; ?></h1></header><section class="iw-editorial-catalog">
<?php if (have_posts()) : ?><div class="iw-editorial-grid"><?php while (have_posts()) : the_post(); ?><article class="iw-editorial-card"><div class="iw-editorial-card-copy"><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><p><?php echo esc_html(wp_trim_words(get_the_excerpt() ?: wp_strip_all_tags(get_the_content()), 28)); ?></p><a class="iw-editorial-more" href="<?php the_permalink(); ?>">Читать →</a></div></article><?php endwhile; ?></div><nav class="iw-editorial-pagination" aria-label="Страницы списка"><?php echo wp_kses_post(paginate_links(array('type'=>'list'))); ?></nav><?php else : ?><div class="iw-editorial-empty"><h2>Материалы пока не опубликованы</h2><p>Проверенные статьи появятся здесь.</p></div><?php endif; ?>
</section></main>
<?php get_footer(); ?>
