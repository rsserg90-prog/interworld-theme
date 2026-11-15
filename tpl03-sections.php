<?php
/** Shared editorial sections for type hubs and groups. */
if (!defined('ABSPATH')) exit;

function interworld_tpl03_anchor_links($term) {
    $links = array();
    if (interworld_term_field('iw_tpl03_seo_text', $term)) $links['description'] = 'Описание';
    if (interworld_tpl03_faq($term)) $links['faq'] = 'Вопросы';
    if (interworld_tpl03_relations($term)) $links['relations'] = 'Взаимосвязи';
    if (interworld_tpl03_related($term, 'iw_tpl03_people')) $links['people'] = 'Контекст';
    if (interworld_tpl03_materials($term) || interworld_tpl03_links($term, 'source')) $links['sources'] = 'Источники';
    if (interworld_tpl03_links($term, 'book')) $links['books'] = 'Книги';
    if (interworld_tpl03_related($term, 'iw_tpl03_topics') || interworld_tpl03_related_terms($term) || ($term->taxonomy === 'object_group' && interworld_parent_type($term))) $links['continue'] = 'Другие темы';
    foreach ($links as $id => $label) echo '<a href="#' . esc_attr($id) . '">' . esc_html($label) . '</a>';
}

function interworld_tpl03_render_sections($term) {
    $seo = interworld_term_field('iw_tpl03_seo_text', $term);
    $faq = interworld_tpl03_faq($term);
    $relations = interworld_tpl03_relations($term);
    $people = interworld_tpl03_related($term, 'iw_tpl03_people');
    $topics = interworld_tpl03_related($term, 'iw_tpl03_topics');
    $topic_terms = interworld_tpl03_related_terms($term);
    if ($term->taxonomy === 'object_group') {
        $parent_type = interworld_parent_type($term);
        if ($parent_type && !in_array($parent_type->term_id, wp_list_pluck($topic_terms, 'term_id'), true)) $topic_terms[] = $parent_type;
        if (!$topics) $topics = get_posts(array('post_type' => 'interworld_object', 'post_status' => 'publish', 'numberposts' => 12, 'fields' => 'ids', 'tax_query' => array(array('taxonomy' => 'object_group', 'field' => 'term_id', 'terms' => $term->term_id))));
    }
    $materials = interworld_tpl03_materials($term);
    foreach (interworld_tpl03_links($term, 'source') as $old) {
        $kind = preg_match('~(?:youtube\.com|youtu\.be|vimeo\.com)~i', $old['url']) ? 'video' : 'article';
        $materials[] = array_merge(array('kind' => $kind, 'image' => '', 'date' => '', 'credit' => ''), $old);
    }
    $books = interworld_tpl03_links($term, 'book');
    ?>
    <?php if ($seo) : ?><section class="iw-tpl03-section" id="description"><div class="iw-tpl03-inner"><h2><?php echo esc_html(interworld_term_field('iw_tpl03_seo_title', $term) ?: 'Подробнее о теме'); ?></h2><div class="iw-tpl03-prose"><?php echo wp_kses_post($seo); ?></div></div></section><?php endif; ?>
    <?php if ($faq) : ?><section class="iw-tpl03-section" id="faq"><div class="iw-tpl03-inner"><h2><?php echo esc_html(interworld_term_field('iw_tpl03_faq_title', $term) ?: 'Вопросы и ответы'); ?></h2><div class="iw-tpl03-faq"><?php foreach ($faq as $item) : ?><details><summary><?php echo esc_html($item['q']); ?></summary><div><?php echo wp_kses_post(wpautop($item['a'])); ?></div></details><?php endforeach; ?></div></div></section><?php endif; ?>
    <?php if ($relations) : ?><section class="iw-tpl03-section" id="relations"><div class="iw-tpl03-inner"><h2>Взаимосвязи</h2><div class="iw-tpl03-tabs" role="tablist" aria-label="Типы взаимосвязей"><?php $first = true; foreach ($relations as $key => $entry) : ?><button type="button" role="tab" id="iw-tab-<?php echo esc_attr($key); ?>" aria-controls="iw-panel-<?php echo esc_attr($key); ?>" aria-selected="<?php echo $first ? 'true' : 'false'; ?>"><?php echo esc_html($entry[0]); ?> <span><?php echo count($entry[1]); ?></span></button><?php $first = false; endforeach; ?></div><?php $first = true; foreach ($relations as $key => $entry) : ?><div class="iw-tpl03-grid" role="tabpanel" id="iw-panel-<?php echo esc_attr($key); ?>" aria-labelledby="iw-tab-<?php echo esc_attr($key); ?>" <?php echo $first ? '' : 'hidden'; ?>><?php interworld_tpl03_post_cards($entry[1]); ?></div><?php $first = false; endforeach; ?></div></section><?php endif; ?>
    <?php if ($people) : ?><section class="iw-tpl03-section" id="people"><div class="iw-tpl03-inner"><h2>Личности и контекст</h2><div class="iw-tpl03-grid"><?php interworld_tpl03_post_cards($people); ?></div></div></section><?php endif; ?>
    <?php if ($materials) : ?><section class="iw-tpl03-section" id="sources"><div class="iw-tpl03-inner"><h2>Материалы для исследования</h2><?php foreach (array('article' => 'Статьи', 'video' => 'Видео', 'text' => 'Текстовые источники') as $kind => $heading) : $rows = array_values(array_filter($materials, function($row) use ($kind) { return ($row['kind'] ?? 'article') === $kind; })); if (!$rows) continue; ?><h3><?php echo esc_html($heading); ?></h3><div class="iw-tpl03-grid"><?php foreach ($rows as $row) : ?><article class="iw-tpl03-card"><?php if (!empty($row['image'])) : ?><img src="<?php echo esc_url($row['image']); ?>" alt="" loading="lazy"><?php endif; ?><div><?php $date = !empty($row['date']) ? date_i18n(get_option('date_format'), strtotime($row['date'])) : ''; if (!empty($row['credit']) || $date) : ?><small><?php echo esc_html(trim(($row['credit'] ?? '') . ' · ' . $date, ' ·')); ?></small><?php endif; ?><h4><?php if (!empty($row['url'])) : ?><a href="<?php echo esc_url($row['url']); ?>"><?php echo esc_html($row['title']); ?></a><?php else : echo esc_html($row['title']); endif; ?></h4><?php if (!empty($row['description'])) : ?><p><?php echo esc_html($row['description']); ?></p><?php endif; ?></div></article><?php endforeach; ?></div><?php endforeach; ?></div></section><?php endif; ?>
    <?php if ($books) : ?><section class="iw-tpl03-section" id="books"><div class="iw-tpl03-inner"><h2>Книги для глубокого изучения</h2><div class="iw-tpl03-grid"><?php foreach ($books as $book) : ?><article class="iw-tpl03-card iw-tpl03-book-card"><?php if (!empty($book['image'])) : ?><img src="<?php echo esc_url($book['image']); ?>" alt="" loading="lazy"><?php endif; ?><div><h3><a href="<?php echo esc_url($book['url']); ?>"><?php echo esc_html($book['title']); ?></a></h3><?php if ($book['description']) : ?><p><?php echo esc_html($book['description']); ?></p><?php endif; ?></div></article><?php endforeach; ?></div></div></section><?php endif; ?>
    <?php if ($topics || $topic_terms) : $background = interworld_term_image_url($term); ?><section class="iw-tpl03-section iw-tpl03-continue" id="continue" <?php if ($background) : ?>style="--iw-continue-image:url('<?php echo esc_url($background); ?>')"<?php endif; ?>><div class="iw-tpl03-inner"><h2>Продолжить исследование Межмирья</h2><div class="iw-tpl03-tags"><?php foreach ($topics as $id) : ?><a class="iw-tpl03-tag" href="<?php echo esc_url(get_permalink($id)); ?>"><?php echo esc_html(get_the_title($id)); ?></a><?php endforeach; ?><?php foreach ($topic_terms as $related_term) : $url = get_term_link($related_term); if (is_wp_error($url)) continue; ?><a class="iw-tpl03-tag" href="<?php echo esc_url($url); ?>"><?php echo esc_html(interworld_term_field('iw_tpl03_title', $related_term) ?: $related_term->name); ?></a><?php endforeach; ?></div></div></section><?php endif; ?>
    <script>document.querySelectorAll('.iw-tpl03-tabs').forEach(function(tabs){tabs.addEventListener('click',function(e){const button=e.target.closest('[role="tab"]');if(!button)return;tabs.querySelectorAll('[role="tab"]').forEach(function(tab){tab.setAttribute('aria-selected',tab===button?'true':'false');document.getElementById(tab.getAttribute('aria-controls')).hidden=tab!==button;});});});</script>
    <?php
}

function interworld_tpl03_post_cards($ids) {
    foreach ($ids as $id) {
        $url = get_permalink($id);
        $image = get_the_post_thumbnail_url($id, 'medium');
        ?><a class="iw-tpl03-card" href="<?php echo esc_url($url); ?>"><?php if ($image) : ?><img src="<?php echo esc_url($image); ?>" alt="" loading="lazy"><?php endif; ?><div><h3><?php echo esc_html(get_the_title($id)); ?></h3><?php if (get_the_excerpt($id)) : ?><p><?php echo esc_html(get_the_excerpt($id)); ?></p><?php endif; ?><span>Подробнее →</span></div></a><?php
    }
}