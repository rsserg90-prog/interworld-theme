<?php
/** Dynamic category archive for the Library taxonomy. */
if (!defined('ABSPATH')) exit;
require_once get_stylesheet_directory() . '/library-functions.php';
wp_enqueue_style('interworld-library', get_stylesheet_directory_uri() . '/library-templates.css', array('interworld-style'), filemtime(get_stylesheet_directory() . '/library-templates.css'));
get_header();
$term = get_queried_object();
$context = 'library_category_' . (int) $term->term_id;
$lead = interworld_knowledge_value('iw_lib_cat_lead', $context);
$body = interworld_knowledge_value('iw_lib_cat_body', $context);
$featured = array_slice(interworld_knowledge_ids('iw_lib_cat_featured', $context), 0, 3);
$children = get_terms(array('taxonomy'=>'library_category','parent'=>(int)$term->term_id,'hide_empty'=>false,'orderby'=>'name'));
$paged = max(1, (int)get_query_var('paged'));
$books_query = new WP_Query(array('post_type'=>'library_book','post_status'=>'publish','posts_per_page'=>12,'orderby'=>'title','order'=>'ASC','post__not_in'=>$featured,'paged'=>$paged,'tax_query'=>array(array('taxonomy'=>'library_category','field'=>'term_id','terms'=>isset($term->term_id) ? (int)$term->term_id : 0))));
$total_books = (int)$books_query->found_posts + count(array_filter($featured, function($id) use ($term) { return has_term((int)$term->term_id, 'library_category', $id); }));
?>
<main class="iw-library-page iw-library-category-page">
  <section class="iw-library-hero"><div class="iw-library-wrap"><div class="iw-library-breadcrumbs"><a href="<?php echo esc_url(home_url('/')); ?>">География Межмирья</a><span> / </span><a href="<?php echo esc_url(home_url('/biblioteka/')); ?>">Библиотека</a><?php $ancestors=array_reverse(get_ancestors($term->term_id,'library_category','taxonomy')); foreach ($ancestors as $ancestor_id) : $ancestor=get_term($ancestor_id,'library_category'); if (!$ancestor || is_wp_error($ancestor)) continue; $ancestor_url=get_term_link($ancestor); if (!is_wp_error($ancestor_url)) : ?><span> / </span><a href="<?php echo esc_url($ancestor_url); ?>"><?php echo esc_html($ancestor->name); ?></a><?php endif; endforeach; ?><span> / </span><span><?php echo esc_html($term->name); ?></span></div><div class="iw-library-eyebrow">КНИГИ И ИССЛЕДОВАНИЯ</div><h1><?php echo esc_html($term->name); ?></h1><?php if ($lead || $term->description) : ?><div class="iw-library-intro"><?php echo wp_kses_post(wpautop($lead ?: $term->description)); ?></div><?php endif; ?><div class="iw-library-summary"><span><strong><?php echo esc_html($total_books); ?></strong> книг в категории</span></div></div></section>
  <?php if ($body) : ?><section class="iw-library-section iw-library-wrap iw-book-content"><h2>О направлении</h2><?php echo wp_kses_post(wpautop($body)); ?></section><?php endif; ?>
  <?php if ($children && !is_wp_error($children)) : ?><section class="iw-library-section iw-library-wrap"><div class="iw-library-section-head"><h2>Направления</h2></div><div class="iw-library-categories"><?php foreach ($children as $child) : $url=get_term_link($child); if (is_wp_error($url)) continue; ?><a class="iw-library-category" href="<?php echo esc_url($url); ?>"><span><?php echo esc_html($child->name); ?></span><b aria-hidden="true">↗</b></a><?php endforeach; ?></div></section><?php endif; ?>
  <?php if ($featured) : ?><section class="iw-library-section iw-library-wrap"><div class="iw-library-section-head"><h2>Избранные книги</h2></div><div class="iw-book-grid"><?php foreach ($featured as $book_id) if (get_post_type($book_id)==='library_book' && get_post_status($book_id)==='publish') echo interworld_library_book_card($book_id); ?></div></section><?php endif; ?>
  <?php if ($books_query->found_posts || !$featured) : ?><section class="iw-library-section iw-library-wrap"><div class="iw-library-section-head"><div><div class="iw-library-eyebrow">КНИЖНОЕ СОБРАНИЕ</div><h2><?php echo $featured ? 'Другие книги категории' : 'Книги категории'; ?></h2></div><a class="iw-library-back" href="<?php echo esc_url(home_url('/biblioteka/')); ?>">← Вся библиотека</a></div>
  <?php if ($books_query->have_posts()) : ?><div class="iw-book-grid"><?php while ($books_query->have_posts()) : $books_query->the_post(); echo interworld_library_book_card(get_the_ID()); endwhile; ?></div><nav class="iw-library-pagination" aria-label="Страницы категории"><?php echo wp_kses_post(paginate_links(array('total'=>$books_query->max_num_pages,'current'=>$paged,'type'=>'plain'))); ?></nav><?php else : ?><p class="iw-library-empty">В этой категории пока нет опубликованных книг.</p><?php endif; wp_reset_postdata(); ?></section>
<?php endif; interworld_knowledge_relations($context); interworld_knowledge_sources($context); interworld_knowledge_continue($context, array(home_url('/biblioteka/') => 'Библиотека')); ?>
</main>
<?php get_footer(); ?>

