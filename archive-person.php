<?php
if (!defined('ABSPATH')) exit;
get_header();
$roles = get_terms(array('taxonomy'=>'person_category','parent'=>0,'hide_empty'=>false,'orderby'=>'name'));
$hub = get_option('iw_people_hub', array());
?>
<main class="iw-library-page iw-people-page">
  <section class="iw-library-hero"><div class="iw-library-wrap"><div class="iw-library-eyebrow">ГЕОГРАФИЯ МЕЖМИРЬЯ</div><h1>Личности</h1><p class="iw-library-intro"><?php echo esc_html($hub['lead'] ?? 'Авторы, исследователи и другие личности, связанные с изучением Межмирья.'); ?></p></div></section>
  <?php if (!empty($hub['about'])) : ?><section class="iw-library-section iw-library-wrap iw-book-content"><h2>О разделе</h2><?php echo wp_kses_post(wpautop($hub['about'])); ?></section><?php endif; ?>
  <?php if ($roles && !is_wp_error($roles)) : ?><section class="iw-library-section iw-library-wrap"><div class="iw-library-section-head"><h2>Направления</h2></div><div class="iw-library-categories"><?php foreach ($roles as $role) : $url=get_term_link($role); if (is_wp_error($url)) continue; ?><a class="iw-library-category" href="<?php echo esc_url($url); ?>"><span><?php echo esc_html($role->name); ?></span><small><?php echo esc_html($role->description); ?></small><b aria-hidden="true">↗</b></a><?php endforeach; ?></div></section><?php endif; ?>
  <section class="iw-library-section iw-library-wrap"><div class="iw-library-section-head"><h2>Все личности</h2></div><?php if (have_posts()) : ?><div class="iw-person-grid"><?php while (have_posts()) : the_post(); echo interworld_person_card(get_the_ID()); endwhile; ?></div><nav class="iw-library-pagination" aria-label="Страницы личностей"><?php echo wp_kses_post(get_the_posts_pagination()); ?></nav><?php else : ?><p class="iw-library-empty">Карточки личностей пока не опубликованы.</p><?php endif; ?></section>
  <?php interworld_knowledge_continue(0, array(home_url('/efiry/')=>'Эфиры',home_url('/biblioteka/')=>'Библиотека')); ?>
</main>
<?php get_footer(); ?>
