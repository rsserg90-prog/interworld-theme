<?php
/**
 * Template Name: Библиотека Межмирья
 * Template Post Type: page
 * Library landing page for INTERWORLD.
 */
if (!defined('ABSPATH')) exit;
require_once get_stylesheet_directory() . '/library-functions.php';
wp_enqueue_style('interworld-library', get_stylesheet_directory_uri() . '/library-templates.css', array('interworld-style'), '1.0.0');
get_header();
$categories = get_terms(array('taxonomy'=>'library_category','parent'=>0,'hide_empty'=>false,'orderby'=>'name','order'=>'ASC'));
$books_query = new WP_Query(array('post_type'=>'library_book','post_status'=>'publish','posts_per_page'=>12,'orderby'=>'title','order'=>'ASC','paged'=>max(1, get_query_var('paged') ? get_query_var('paged') : get_query_var('page'))));
$hub_id = get_queried_object_id();
$hub_lead = interworld_knowledge_value('iw_lib_hub_lead', $hub_id);
$hub_about = interworld_knowledge_value('iw_lib_hub_about', $hub_id);
$hub_featured = array_slice(interworld_knowledge_ids('iw_lib_hub_featured', $hub_id), 0, 3);
?>
<main class="iw-library-page">
  <section class="iw-library-hero">
    <div class="iw-library-wrap">
      <div class="iw-library-eyebrow">ГЕОГРАФИЯ МЕЖМИРЬЯ · КНИЖНОЕ СОБРАНИЕ</div>
      <h1><?php the_title(); ?></h1>
      <?php if ($hub_lead) : ?><div class="iw-library-intro"><?php echo esc_html($hub_lead); ?></div><?php elseif (have_posts()) : while (have_posts()) : the_post(); if (trim(wp_strip_all_tags(get_the_content()))) : ?><div class="iw-library-intro"><?php the_content(); ?></div><?php endif; endwhile; endif; ?>
      <div class="iw-library-summary"><span><strong><?php echo esc_html((int)wp_count_posts('library_book')->publish); ?></strong> книг</span><span><strong><?php echo esc_html(is_array($categories) ? count($categories) : 0); ?></strong> категорий</span></div>
    </div>
  </section>
  <section class="iw-library-section iw-library-wrap">
    <div class="iw-library-section-head"><div><div class="iw-library-eyebrow">ПУТЕВОДИТЕЛЬ ПО СОБРАНИЮ</div><h2>Категории библиотеки</h2></div></div>
    <?php if (!is_wp_error($categories) && $categories) : ?>
      <div class="iw-library-categories">
      <?php foreach ($categories as $category) : $term_link = get_term_link($category); if (is_wp_error($term_link)) continue; ?>
        <a class="iw-library-category" href="<?php echo esc_url($term_link); ?>"><span><?php echo esc_html($category->name); ?></span><small><?php echo esc_html((int)$category->count); ?> <?php echo ((int)$category->count === 1 ? 'книга' : (((int)$category->count >= 2 && (int)$category->count <= 4) ? 'книги' : 'книг')); ?></small><b aria-hidden="true">↗</b></a>
      <?php endforeach; ?>
      </div>
    <?php else : ?><p class="iw-library-empty">Категории пока не добавлены.</p><?php endif; ?>
  </section>
  <?php if ($hub_about) : ?><section class="iw-library-section iw-library-wrap iw-book-content"><h2>О собрании</h2><?php echo wp_kses_post(wpautop($hub_about)); ?></section><?php endif; ?>
  <?php if ($hub_featured) : ?><section class="iw-library-section iw-library-wrap"><div class="iw-library-section-head"><h2>Избранные книги</h2></div><div class="iw-book-grid"><?php foreach ($hub_featured as $book_id) if (get_post_type($book_id)==='library_book' && get_post_status($book_id)==='publish') echo interworld_library_book_card($book_id); ?></div></section><?php endif; ?>
  <section class="iw-library-section iw-library-wrap" id="books">
    <div class="iw-library-section-head"><div><div class="iw-library-eyebrow">КНИЖНЫЕ СТРАНИЦЫ</div><h2>Все книги</h2></div><span class="iw-library-muted"><?php echo esc_html((int)$books_query->found_posts); ?> в собрании</span></div>
    <?php if ($books_query->have_posts()) : ?><div class="iw-book-grid">
      <?php while ($books_query->have_posts()) : $books_query->the_post(); echo interworld_library_book_card(get_the_ID()); endwhile; ?>
    </div>
    <nav class="iw-library-pagination" aria-label="Страницы библиотеки"><?php echo wp_kses_post(paginate_links(array('total'=>$books_query->max_num_pages,'current'=>max(1,get_query_var('paged') ? get_query_var('paged') : get_query_var('page')),'type'=>'plain'))); ?></nav>
    <?php else : ?><p class="iw-library-empty">Книги пока не опубликованы. Добавьте первую запись в разделе «Библиотека».</p><?php endif; wp_reset_postdata(); ?>
  </section>
  <?php interworld_knowledge_relations(get_queried_object_id()); interworld_knowledge_sources(get_queried_object_id()); interworld_knowledge_continue(get_queried_object_id(), array(home_url('/efiry/') => 'Эфиры')); ?>
</main>
<?php get_footer();

