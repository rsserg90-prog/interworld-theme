<?php
/** Shared helper functions for INTERWORLD Library templates. */
if (!defined('ABSPATH')) exit;
function interworld_library_book_card($book_id) {
  $title = get_the_title($book_id);
  $author = function_exists('get_field') ? get_field('avtor_knigi', $book_id) : get_post_meta($book_id, 'avtor_knigi', true);
  $short = function_exists('get_field') ? get_field('kratkoe_opisanie', $book_id) : get_post_meta($book_id, 'kratkoe_opisanie', true);
  $cover = function_exists('get_field') ? get_field('oblozhka_knigi', $book_id) : get_post_meta($book_id, 'oblozhka_knigi', true);
  $cover_url = '';
  if (is_array($cover)) $cover_url = !empty($cover['url']) ? $cover['url'] : (!empty($cover['ID']) ? wp_get_attachment_image_url($cover['ID'], 'large') : '');
  elseif (is_numeric($cover)) $cover_url = wp_get_attachment_image_url((int)$cover, 'large');
  elseif (is_string($cover) && filter_var($cover, FILTER_VALIDATE_URL)) $cover_url = $cover;
  if (!$cover_url && has_post_thumbnail($book_id)) $cover_url = get_the_post_thumbnail_url($book_id, 'large');
  $permalink = get_permalink($book_id);
  ob_start(); ?>
  <article class="iw-book-card"><a class="iw-book-cover" href="<?php echo esc_url($permalink); ?>" aria-label="Открыть книгу: <?php echo esc_attr($title); ?>"><?php if ($cover_url) : ?><img src="<?php echo esc_url($cover_url); ?>" alt="Обложка книги «<?php echo esc_attr($title); ?>" loading="lazy"><?php else : ?><span class="iw-book-cover-placeholder">КНИЖНОЕ<br>СОБРАНИЕ</span><?php endif; ?></a><div class="iw-book-card-body"><h3><a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a></h3><?php if ($author) : ?><div class="iw-book-author"><?php echo esc_html($author); ?></div><?php endif; ?><?php if ($short) : ?><p><?php echo esc_html(wp_trim_words(wp_strip_all_tags($short), 26)); ?></p><?php endif; ?><a class="iw-book-more" href="<?php echo esc_url($permalink); ?>">О книге <span aria-hidden="true">→</span></a></div></article>
  <?php return ob_get_clean();
}
