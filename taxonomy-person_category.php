<?php
if (!defined('ABSPATH')) exit;
$term = get_queried_object();
$context = 'person_category_' . (int) $term->term_id;
$children = get_terms(array('taxonomy'=>'person_category','parent'=>$term->term_id,'hide_empty'=>false,'orderby'=>'name'));
get_header();
?>
<main class="iw-library-page iw-people-page">
  <section class="iw-library-hero"><div class="iw-library-wrap"><div class="iw-library-breadcrumbs"><a href="<?php echo esc_url(home_url('/')); ?>">Главная</a> / <a href="<?php echo esc_url(get_post_type_archive_link('person')); ?>">Личности</a><?php $ancestors = array_reverse(get_ancestors($term->term_id, 'person_category', 'taxonomy')); foreach ($ancestors as $ancestor_id) : $ancestor=get_term($ancestor_id,'person_category'); $url=get_term_link($ancestor); if ($ancestor && !is_wp_error($url)) : ?> / <a href="<?php echo esc_url($url); ?>"><?php echo esc_html($ancestor->name); ?></a><?php endif; endforeach; ?> / <?php echo esc_html($term->name); ?></div><h1><?php echo esc_html($term->name); ?></h1><?php $lead=interworld_knowledge_value('iw_person_cat_lead',$context); if ($lead) : ?><p class="iw-library-intro"><?php echo esc_html($lead); ?></p><?php endif; ?></div></section>
  <?php $body=interworld_knowledge_value('iw_person_cat_body',$context); if ($body) : ?><section class="iw-library-section iw-library-wrap iw-book-content"><h2>О направлении</h2><?php echo wp_kses_post(wpautop($body)); ?></section><?php endif; ?>
  <?php if ($children && !is_wp_error($children)) : ?><section class="iw-library-section iw-library-wrap"><h2>Направления</h2><div class="iw-library-categories"><?php foreach ($children as $child) : $url=get_term_link($child); if (is_wp_error($url)) continue; ?><a class="iw-library-category" href="<?php echo esc_url($url); ?>"><span><?php echo esc_html($child->name); ?></span><b aria-hidden="true">↗</b></a><?php endforeach; ?></div></section><?php endif; ?>
  <?php $featured=array_slice(interworld_knowledge_ids('iw_person_cat_featured',$context),0,3); if ($featured) : ?><section class="iw-library-section iw-library-wrap"><h2>Избранные личности</h2><div class="iw-person-grid"><?php foreach ($featured as $person_id) echo interworld_person_card($person_id); ?></div></section><?php endif; ?>
  <section class="iw-library-section iw-library-wrap"><h2>Личности</h2><?php if (have_posts()) : ?><div class="iw-person-grid"><?php while (have_posts()) : the_post(); echo interworld_person_card(get_the_ID()); endwhile; ?></div><nav class="iw-library-pagination" aria-label="Страницы категории"><?php echo wp_kses_post(get_the_posts_pagination()); ?></nav><?php else : ?><p class="iw-library-empty">Пока нет опубликованных карточек.</p><?php endif; ?></section>
  <?php interworld_knowledge_relations($context); interworld_knowledge_sources($context); interworld_knowledge_continue($context, array(get_post_type_archive_link('person')=>'Личности')); ?>
</main>
<?php get_footer(); ?>
