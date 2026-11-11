<?php
/** Single book template for the Library custom post type. */
if (!defined('ABSPATH')) exit;
wp_enqueue_style('interworld-library', get_stylesheet_directory_uri() . '/library-templates.css', array('interworld-style'), '1.0.0');
wp_enqueue_script('interworld-book-contents', get_stylesheet_directory_uri() . '/library-contents.js', array(), '1.1.0', true);
get_header();
?>
<main class="iw-library-page iw-single-book">
<?php while (have_posts()) : the_post();
  $book_id = get_the_ID();
  $field = function($name) use ($book_id) { return function_exists('get_field') ? get_field($name, $book_id) : get_post_meta($book_id, $name, true); };
  $author = $field('avtor_knigi'); $short = $field('kratkoe_opisanie'); $full = $field('polnoe_opisanie'); $contents = $field('soderzhanie_knigi'); $cover = $field('oblozhka_knigi'); $year = $field('god_izdania'); $publisher = $field('izdatelstvo'); $isbn = $field('isbn'); $reading = $field('url_chtenie');
  $cover_url = '';
  if (is_array($cover)) $cover_url = !empty($cover['url']) ? $cover['url'] : (!empty($cover['ID']) ? wp_get_attachment_image_url($cover['ID'], 'large') : '');
  elseif (is_numeric($cover)) $cover_url = wp_get_attachment_image_url((int)$cover, 'large');
  elseif (is_string($cover) && filter_var($cover, FILTER_VALIDATE_URL)) $cover_url = $cover;
  if (!$cover_url && has_post_thumbnail()) $cover_url = get_the_post_thumbnail_url(get_the_ID(), 'large');
  $categories = get_the_terms($book_id, 'library_category');
?>
<section class="iw-book-detail-hero"><div class="iw-library-wrap"><div class="iw-library-breadcrumbs"><a href="<?php echo esc_url(home_url('/')); ?>">Главная</a><span> / </span><a href="<?php echo esc_url(home_url('/biblioteka/')); ?>">Библиотека</a><span> / </span><span><?php the_title(); ?></span></div><div class="iw-book-detail-grid"><div class="iw-book-detail-cover"><?php if ($cover_url) : ?><img src="<?php echo esc_url($cover_url); ?>" alt="Обложка книги «<?php echo esc_attr(get_the_title()); ?>"><?php else : ?><div class="iw-book-cover-placeholder">КНИЖНОЕ<br>СОБРАНИЕ</div><?php endif; ?></div><div class="iw-book-detail-copy"><div class="iw-library-eyebrow">КНИЖНОЕ СОБРАНИЕ МЕЖМИРЬЯ</div><h1><?php the_title(); ?></h1><?php if ($author) : ?><p class="iw-book-detail-author"><?php echo esc_html($author); ?></p><?php endif; ?><?php if ($short) : ?><div class="iw-book-detail-short"><?php echo wp_kses_post(wpautop($short)); ?></div><?php endif; ?><div class="iw-book-actions"><?php if ($reading && is_string($reading) && filter_var($reading, FILTER_VALIDATE_URL)) : ?><a class="iw-library-button" href="<?php echo esc_url($reading); ?>" target="_blank" rel="noopener noreferrer">Перейти к чтению <span aria-hidden="true">↗</span></a><?php endif; ?>
<?php if (is_string($contents) && trim($contents) !== '') : ?><details class="iw-book-contents"><summary>Содержание</summary><div class="iw-book-contents-panel"><?php echo wp_kses_post(wpautop($contents)); ?></div></details><?php endif; ?></div>
<?php if ($year || $publisher || $isbn) : ?><dl class="iw-book-meta"><?php if ($year) : ?><div><dt>Год издания</dt><dd><?php echo esc_html($year); ?></dd></div><?php endif; ?><?php if ($publisher) : ?><div><dt>Издательство</dt><dd><?php echo esc_html($publisher); ?></dd></div><?php endif; ?><?php if ($isbn) : ?><div><dt>ISBN</dt><dd><?php echo esc_html($isbn); ?></dd></div><?php endif; ?></dl><?php endif; ?>
<?php if ($categories && !is_wp_error($categories)) : ?><div class="iw-book-tags"><span>Категории:</span><?php foreach ($categories as $cat) : $link = get_term_link($cat); if (!is_wp_error($link)) : ?><a href="<?php echo esc_url($link); ?>"><?php echo esc_html($cat->name); ?></a><?php endif; endforeach; ?></div><?php endif; ?></div></div></div></section>
<?php if ($full) : ?><section class="iw-library-section iw-library-wrap iw-book-full-description"><div class="iw-library-eyebrow">О КНИГЕ</div><h2>Полное описание</h2><div class="iw-book-content"><?php echo apply_filters('the_content', $full); ?></div></section><?php endif; ?>

<?php
/**
 * INTERWORLD — Вывод цитат из книги.
 * Данные хранятся в метаполе _interworld_book_quotes.
 */
$iw_book_quotes = get_post_meta($book_id, '_interworld_book_quotes', true);

if (is_array($iw_book_quotes)) {
    $iw_book_quotes = array_values(array_filter(
        $iw_book_quotes,
        function ($item) {
            return is_array($item)
                && !empty($item['quote'])
                && is_string($item['quote']);
        }
    ));
} else {
    $iw_book_quotes = array();
}

$iw_book_quotes_count = count($iw_book_quotes);

if ($iw_book_quotes_count > 0) :
    $iw_quotes_uid = 'iw-book-quotes-' . (int) $book_id;
?>
<section
    class="iw-library-section iw-library-wrap iw-book-quotes"
    aria-labelledby="<?php echo esc_attr($iw_quotes_uid); ?>-heading"
>
    <div class="iw-library-eyebrow">ИЗ КНИГИ</div>
    <h2 id="<?php echo esc_attr($iw_quotes_uid); ?>-heading">
        Цитаты из книги
    </h2>

    <div
        class="iw-book-quotes-slider"
        id="<?php echo esc_attr($iw_quotes_uid); ?>"
        data-iw-quotes-slider
        data-count="<?php echo esc_attr($iw_book_quotes_count); ?>"
    >
        <div class="iw-book-quotes-viewport">
            <?php foreach ($iw_book_quotes as $iw_quote_index => $iw_quote) :
                $iw_quote_text = trim($iw_quote['quote']);
                $iw_quote_location = isset($iw_quote['location'])
                    ? trim((string) $iw_quote['location'])
                    : '';
            ?>
                <figure
                    class="iw-book-quote<?php echo $iw_quote_index === 0 ? ' is-active' : ''; ?>"
                    data-iw-quote
                    <?php echo $iw_quote_index === 0 ? '' : 'hidden'; ?>
                >
                    <blockquote>
                        <?php echo wpautop(esc_html($iw_quote_text)); ?>
                    </blockquote>

                    <?php if ($iw_quote_location !== '') : ?>
                        <figcaption>
                            <?php echo esc_html($iw_quote_location); ?>
                        </figcaption>
                    <?php endif; ?>
                </figure>
            <?php endforeach; ?>
        </div>

        <?php if ($iw_book_quotes_count > 1) : ?>
            <div class="iw-book-quotes-controls">
                <button
                    type="button"
                    class="iw-book-quotes-prev"
                    data-iw-quotes-prev
                    aria-label="Предыдущая цитата"
                >
                    ←
                </button>

                <span class="iw-book-quotes-counter" aria-live="polite">
                    <span data-iw-quotes-current>1</span>
                    /
                    <?php echo esc_html($iw_book_quotes_count); ?>
                </span>

                <button
                    type="button"
                    class="iw-book-quotes-next"
                    data-iw-quotes-next
                    aria-label="Следующая цитата"
                >
                    →
                </button>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
.iw-book-quotes-viewport {
    width: 100%;
}

.iw-book-quote {
    box-sizing: border-box;
    width: 100%;
    margin: 0;
}

.iw-book-quote[hidden] {
    display: none !important;
}

.iw-book-quote blockquote {
    margin: 0;
    padding: 28px 32px;
    border-left: 3px solid var(--iw-accent, #b99a65);
    font-size: 1.2em;
    line-height: 1.7;
}

.iw-book-quote blockquote p:first-child {
    margin-top: 0;
}

.iw-book-quote blockquote p:last-child {
    margin-bottom: 0;
}

.iw-book-quote figcaption {
    margin: 12px 32px 0;
    font-size: 0.9em;
    opacity: 0.75;
}

.iw-book-quotes-controls {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    margin-top: 24px;
}

.iw-book-quotes-controls button {
    cursor: pointer;
}

@media (max-width: 600px) {
    .iw-book-quote blockquote {
        padding: 20px;
        font-size: 1.05em;
    }

    .iw-book-quote figcaption {
        margin-left: 20px;
        margin-right: 20px;
    }
}
</style>

<script>
(function () {
    const sliders = document.querySelectorAll('[data-iw-quotes-slider]');

    sliders.forEach(function (slider) {
        if (slider.dataset.initialized === 'true') return;
        slider.dataset.initialized = 'true';

        const quotes = Array.from(slider.querySelectorAll('[data-iw-quote]'));
        const prev = slider.querySelector('[data-iw-quotes-prev]');
        const next = slider.querySelector('[data-iw-quotes-next]');
        const current = slider.querySelector('[data-iw-quotes-current]');

        if (quotes.length <= 1) return;

        let index = 0;

        function showQuote(newIndex) {
            index = (newIndex + quotes.length) % quotes.length;

            quotes.forEach(function (quote, i) {
                const active = i === index;
                quote.hidden = !active;
                quote.classList.toggle('is-active', active);
            });

            if (current) {
                current.textContent = String(index + 1);
            }
        }

        if (prev) {
            prev.addEventListener('click', function () {
                showQuote(index - 1);
            });
        }

        if (next) {
            next.addEventListener('click', function () {
                showQuote(index + 1);
            });
        }
    });
})();
</script>
<?php endif; ?>

<?php
  // Тематические связи книги с категориями, подкатегориями и отдельными объектами Межмирья.
  $iw_type_ids = array_map('absint', (array) get_post_meta($book_id, '_iw_book_object_type_terms', true));
  $iw_group_ids = array_map('absint', (array) get_post_meta($book_id, '_iw_book_object_group_terms', true));
  $iw_object_ids = array_map('absint', (array) get_post_meta($book_id, '_iw_book_related_objects', true));
  $iw_relation_note = (string) get_post_meta($book_id, '_iw_book_relation_note', true);
  $iw_tabs = array();
  foreach (array('object_type' => array('ids' => $iw_type_ids, 'label' => 'Категории'), 'object_group' => array('ids' => $iw_group_ids, 'label' => 'Подкатегории')) as $iw_tax => $iw_config) {
    $iw_terms = array();
    foreach ($iw_config['ids'] as $iw_term_id) {
      $iw_term = get_term($iw_term_id, $iw_tax);
      if ($iw_term && !is_wp_error($iw_term)) { $iw_terms[] = $iw_term; }
    }
    if ($iw_terms) { $iw_tabs[] = array('key' => $iw_tax, 'label' => $iw_config['label'], 'terms' => $iw_terms); }
  }
  $iw_objects = array();
  foreach ($iw_object_ids as $iw_object_id) {
    $iw_obj = get_post($iw_object_id);
    if ($iw_obj && $iw_obj->post_type === 'interworld_object' && $iw_obj->post_status === 'publish') { $iw_objects[] = $iw_obj; }
  }
  if ($iw_objects) { $iw_tabs[] = array('key' => 'objects', 'label' => 'Отдельные объекты', 'objects' => $iw_objects); }
  if ($iw_tabs) :
    $iw_tab_uid = 'iw-book-topics-' . (int)$book_id;
?>
<section class="iw-library-section iw-library-wrap iw-book-topics" aria-labelledby="<?php echo esc_attr($iw_tab_uid); ?>-heading">
  <div class="iw-library-eyebrow">ТЕМАТИЧЕСКИЕ СВЯЗИ</div>
  <h2 id="<?php echo esc_attr($iw_tab_uid); ?>-heading">Ключевые идеи, упомянутые в книге</h2>
  <?php if ($iw_relation_note !== '') : ?><div class="iw-book-content iw-book-relation-note"><?php echo wpautop(esc_html($iw_relation_note)); ?></div><?php endif; ?>
  <div class="iw-book-topic-tabs" data-iw-book-tabs>
    <div class="iw-book-topic-tablist" role="tablist" aria-label="Темы книги">
      <?php foreach ($iw_tabs as $iw_index => $iw_tab) : $iw_panel_id = $iw_tab_uid . '-' . $iw_tab['key']; ?>
        <button type="button" role="tab" id="<?php echo esc_attr($iw_panel_id); ?>-tab" aria-controls="<?php echo esc_attr($iw_panel_id); ?>-panel" aria-selected="<?php echo $iw_index === 0 ? 'true' : 'false'; ?>" tabindex="<?php echo $iw_index === 0 ? '0' : '-1'; ?>"><?php echo esc_html($iw_tab['label']); ?></button>
      <?php endforeach; ?>
    </div>
    <?php foreach ($iw_tabs as $iw_index => $iw_tab) : $iw_panel_id = $iw_tab_uid . '-' . $iw_tab['key']; ?>
      <div class="iw-book-topic-panel" role="tabpanel" id="<?php echo esc_attr($iw_panel_id); ?>-panel" aria-labelledby="<?php echo esc_attr($iw_panel_id); ?>-tab" <?php echo $iw_index === 0 ? '' : 'hidden'; ?>>
        <?php if (!empty($iw_tab['terms'])) : ?>
          <ul class="iw-book-topic-links">
            <?php foreach ($iw_tab['terms'] as $iw_term) : $iw_link = get_term_link($iw_term); if (!is_wp_error($iw_link)) : ?>
              <li><a href="<?php echo esc_url($iw_link); ?>"><?php echo esc_html($iw_term->name); ?><span aria-hidden="true">↗</span></a></li>
            <?php endif; endforeach; ?>
          </ul>
        <?php elseif (!empty($iw_tab['objects'])) : ?>
          <ul class="iw-book-topic-links">
            <?php foreach ($iw_tab['objects'] as $iw_obj) : ?><li><a href="<?php echo esc_url(get_permalink($iw_obj)); ?>"><?php echo esc_html(get_the_title($iw_obj)); ?><span aria-hidden="true">↗</span></a></li><?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<script>
(function(){
  const root=document.querySelector('.iw-book-topics [data-iw-book-tabs]'); if(!root)return;
  const tabs=Array.from(root.querySelectorAll('[role="tab"]'));
  function activate(tab, focus){tabs.forEach(t=>{const active=t===tab;t.setAttribute('aria-selected',active?'true':'false');t.tabIndex=active?0:-1;const panel=document.getElementById(t.getAttribute('aria-controls'));if(panel)panel.hidden=!active;});if(focus)tab.focus();}
  tabs.forEach((tab,index)=>{tab.addEventListener('click',()=>activate(tab,false));tab.addEventListener('keydown',e=>{let next=index;if(e.key==='ArrowRight')next=(index+1)%tabs.length;else if(e.key==='ArrowLeft')next=(index-1+tabs.length)%tabs.length;else if(e.key==='Home')next=0;else if(e.key==='End')next=tabs.length-1;else return;e.preventDefault();activate(tabs[next],true);});});
})();
</script>
<?php endif; ?>

<?php
// Вопросы и ответы о книге.
$iw_book_faq = get_post_meta($book_id, '_iw_book_faq_items', true);

if (is_array($iw_book_faq)) {
    $iw_book_faq = array_values(array_filter($iw_book_faq, function ($item) {
        return is_array($item)
            && !empty(trim($item['question'] ?? ''))
            && !empty(trim($item['answer'] ?? ''));
    }));
} else {
    $iw_book_faq = array();
}
?>

<?php if (!empty($iw_book_faq)) : ?>
<section class="iw-library-section iw-library-wrap iw-book-faq">
    <div class="iw-library-eyebrow">ВОПРОСЫ И ОТВЕТЫ</div>
    <h2>Часто задаваемые вопросы о книге</h2>

    <div class="iw-book-faq-list">
        <?php foreach ($iw_book_faq as $index => $item) : ?>
            <details class="iw-book-faq-item">
                <summary>
                    <?php echo esc_html($item['question']); ?>
                </summary>

                <div class="iw-book-faq-answer">
                    <?php
                    echo wpautop(
                        esc_html($item['answer'])
                    );
                    ?>
                </div>
            </details>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
<?php endwhile; ?>
</main>
<?php get_footer(); ?>
