<?php
/** Dynamic category archive for the Library taxonomy. */
if (!defined('ABSPATH')) exit;
require_once get_stylesheet_directory() . '/library-functions.php';
wp_enqueue_style('interworld-library', get_stylesheet_directory_uri() . '/library-templates.css', array('interworld-style'), '1.0.0');
get_header();
$term = get_queried_object();
$paged = max(1, (int)get_query_var('paged'));
$books_query = new WP_Query(array('post_type'=>'library_book','post_status'=>'publish','posts_per_page'=>12,'orderby'=>'title','order'=>'ASC','paged'=>$paged,'tax_query'=>array(array('taxonomy'=>'library_category','field'=>'term_id','terms'=>isset($term->term_id) ? (int)$term->term_id : 0))));
?>
<main class="iw-library-page iw-library-category-page">
  <section class="iw-library-hero"><div class="iw-library-wrap"><div class="iw-library-breadcrumbs"><a href="<?php echo esc_url(home_url('/')); ?>">География Межмирья</a><span> / </span><a href="<?php echo esc_url(home_url('/biblioteka/')); ?>">Библиотека</a><span> / </span><span><?php echo esc_html($term->name); ?></span></div><div class="iw-library-eyebrow">КАТЕГОРИЯ БИБЛИОТЕКИ</div><h1><?php echo esc_html($term->name); ?></h1><?php if (!empty($term->description)) : ?><div class="iw-library-intro"><?php echo wp_kses_post(wpautop($term->description)); ?></div><?php else : ?><p class="iw-library-intro iw-library-muted">Книги, отнесённые к категории «<?php echo esc_html($term->name); ?>».</p><?php endif; ?><div class="iw-library-summary"><span><strong><?php echo esc_html((int)$term->count); ?></strong> книг в категории</span></div></div></section>
  <section class="iw-library-section iw-library-wrap"><div class="iw-library-section-head"><div><div class="iw-library-eyebrow">КНИЖНОЕ СОБРАНИЕ</div><h2>Книги категории</h2></div><a class="iw-library-back" href="<?php echo esc_url(home_url('/biblioteka/')); ?>">← Вся библиотека</a></div>
  <?php if ($books_query->have_posts()) : ?><div class="iw-book-grid"><?php while ($books_query->have_posts()) : $books_query->the_post(); echo interworld_library_book_card(get_the_ID()); endwhile; ?></div><nav class="iw-library-pagination" aria-label="Страницы категории"><?php echo wp_kses_post(paginate_links(array('total'=>$books_query->max_num_pages,'current'=>$paged,'type'=>'plain'))); ?></nav><?php else : ?><p class="iw-library-empty">В этой категории пока нет опубликованных книг.</p><?php endif; wp_reset_postdata(); ?></section>
</main>
<?php get_footer(); ?>
