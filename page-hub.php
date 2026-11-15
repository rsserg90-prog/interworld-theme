<?php
/**
 * Template Name: Раздел энциклопедии
 * Template Post Type: page
 * Content hub for the encyclopedia sections.
 */
if (!defined('ABSPATH')) exit;
get_header();
$page_id = get_queried_object_id();
$lead = interworld_content_value('iw_page_lead', $page_id);
$body = interworld_content_value('iw_page_body', $page_id);
$image = interworld_content_image_url(interworld_content_value('iw_page_hero_image', $page_id));
$tiles = array(
    'zones'=>'Зоны и Эфиры', 'creatures'=>'Существа', 'spaces'=>'Пространства', 'afterlife'=>'Посмертие',
    'fairies'=>'Фейри', 'archons'=>'Архонты', 'traditions'=>'Традиции', 'library'=>'Библиотека',
    'articles'=>'Статьи', 'glossary'=>'Глоссарий', 'sources'=>'Источники',
);
?>
<main class="iw-editorial-page iw-hub-page">
  <?php while (have_posts()) : the_post(); ?>
    <header class="iw-editorial-archive-header<?php echo $image ? ' has-image' : ''; ?>" <?php if ($image) : ?>style="--iw-hub-image:url('<?php echo esc_url($image); ?>')"<?php endif; ?>><nav class="iw-editorial-breadcrumbs" aria-label="Хлебные крошки"><a href="<?php echo esc_url(home_url('/')); ?>">Главная</a><span aria-hidden="true">›</span><span><?php the_title(); ?></span></nav><div class="iw-editorial-eyebrow">ЭНЦИКЛОПЕДИЯ МЕЖМИРЬЯ</div><h1><?php the_title(); ?></h1><?php if ($lead) : ?><p class="iw-editorial-lead"><?php echo esc_html($lead); ?></p><?php endif; ?></header>
    <?php if ($body || trim(wp_strip_all_tags(get_the_content()))) : ?><section class="iw-editorial-body"><?php if ($body) echo wp_kses_post(wpautop($body)); the_content(); ?></section><?php endif; ?>
  <?php endwhile; ?>
  <section class="iw-hub-grid-section"><div class="iw-editorial-section-heading"><div class="iw-editorial-eyebrow">ПУТИ ПОЗНАНИЯ</div><h2>Разделы Межмирья</h2></div><div class="iw-hub-grid">
    <?php foreach ($tiles as $key=>$label) : ?><a class="iw-hub-tile" href="<?php echo esc_url(interworld_content_section_url($key)); ?>"><span><?php echo esc_html($label); ?></span><b aria-hidden="true">→</b></a><?php endforeach; ?>
  </div></section>
  <?php if (function_exists('interworld_knowledge_relations')) interworld_knowledge_relations($page_id); if (function_exists('interworld_knowledge_sources')) interworld_knowledge_sources($page_id); ?>
</main>
<?php get_footer(); ?>
