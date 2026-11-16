<?php
/** Standard article and general singular fallback. */
if (!defined('ABSPATH')) exit;
get_header();
?>
<main class="iw-editorial-page">
  <?php while (have_posts()) : the_post(); $post_id = get_the_ID(); $lead = interworld_content_value('iw_lead', $post_id); ?>
    <article class="iw-editorial-entry">
      <nav class="iw-editorial-breadcrumbs" aria-label="Хлебные крошки"><a href="<?php echo esc_url(home_url('/')); ?>">Главная</a><span aria-hidden="true">›</span><span><?php echo esc_html(get_post_type_object(get_post_type())->labels->name ?? 'Материал'); ?></span></nav>
      <?php if (has_post_thumbnail()) : ?><div class="iw-editorial-hero-image"><?php the_post_thumbnail('large', array('alt'=>esc_attr(get_the_title()))); ?></div><?php endif; ?>
      <header class="iw-editorial-header"><div class="iw-editorial-eyebrow"><?php echo esc_html(get_post_type_object(get_post_type())->labels->singular_name ?? 'Материал'); ?></div><h1><?php the_title(); ?></h1><?php if ($lead) : ?><p class="iw-editorial-lead"><?php echo esc_html($lead); ?></p><?php elseif (has_excerpt()) : ?><p class="iw-editorial-lead"><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?><div class="iw-editorial-meta"><time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time><?php if (get_the_author()) : ?><span>Автор: <?php the_author(); ?></span><?php endif; ?></div></header>
      <div class="iw-editorial-body"><?php the_content(); ?></div>
      <?php $facts = (string)interworld_content_value('iw_verified_facts', $post_id); if ($facts) : ?><section class="iw-editorial-facts"><h2>Ключевые сведения</h2><ul><?php foreach (preg_split('/\r\n|\r|\n/', $facts) as $fact) if (trim($fact) !== '') : ?><li><?php echo esc_html(trim($fact)); ?></li><?php endif; ?></ul></section><?php endif; ?>
    </article>
    <?php interworld_content_provenance($post_id); if (function_exists('interworld_knowledge_relations')) interworld_knowledge_relations($post_id); if (function_exists('interworld_knowledge_sources')) interworld_knowledge_sources($post_id); ?>
  <?php endwhile; ?>
</main>
<?php get_footer(); ?>
