<?php
/** Shared editorial blocks for Library and People. */
if (!defined('ABSPATH')) exit;

function interworld_knowledge_value($name, $context) {
    if (function_exists('get_field')) {
        $value = get_field($name, $context);
        if ($value !== null && $value !== false && $value !== '') return $value;
    }
    return is_string($context) ? get_term_meta((int) substr(strrchr($context, '_'), 1), $name, true) : get_post_meta((int) $context, $name, true);
}

function interworld_knowledge_ids($name, $context) {
    $value = interworld_knowledge_value($name, $context);
    if (!is_array($value)) $value = $value ? array($value) : array();
    return array_values(array_unique(array_filter(array_map(function($item) {
        return absint(is_object($item) ? ($item->ID ?? $item->term_id ?? 0) : $item);
    }, $value))));
}

function interworld_knowledge_links($context) {
    $groups = array();
    $current_id = is_numeric($context) ? absint($context) : 0;
    foreach (interworld_knowledge_ids('iw_knowledge_objects', $context) as $id) {
        if ($id === $current_id || get_post_type($id) !== 'interworld_object' || get_post_status($id) !== 'publish') continue;
        $types = get_the_terms($id, 'object_type');
        $label = ($types && !is_wp_error($types)) ? $types[0]->name : 'Объекты Межмирья';
        $groups[$label][$id] = $id;
    }
    foreach (array('iw_knowledge_books' => array('library_book', 'Книги'), 'iw_knowledge_people' => array('person', 'Личности')) as $field => $config) {
        foreach (interworld_knowledge_ids($field, $context) as $id) {
            if ($id !== $current_id && get_post_type($id) === $config[0] && get_post_status($id) === 'publish') $groups[$config[1]][$id] = $id;
        }
    }
    return array_filter($groups);
}

function interworld_knowledge_relations($context) {
    $groups = interworld_knowledge_links($context);
    if (!$groups) return;
    $uid = 'iw-knowledge-' . sanitize_html_class(str_replace('_', '-', (string) $context));
    ?>
    <section class="iw-knowledge-section iw-library-wrap" aria-labelledby="<?php echo esc_attr($uid); ?>-heading">
      <h2 id="<?php echo esc_attr($uid); ?>-heading">Взаимосвязи</h2>
      <div class="iw-knowledge-tabs" role="tablist" aria-label="Типы взаимосвязей">
        <?php $index = 0; foreach ($groups as $label => $ids) : $panel = $uid . '-panel-' . $index; ?>
          <button type="button" role="tab" id="<?php echo esc_attr($panel); ?>-tab" aria-controls="<?php echo esc_attr($panel); ?>" aria-selected="<?php echo $index ? 'false' : 'true'; ?>" tabindex="<?php echo $index ? '-1' : '0'; ?>"><?php echo esc_html($label); ?> <span><?php echo count($ids); ?></span></button>
        <?php $index++; endforeach; ?>
      </div>
      <?php $index = 0; foreach ($groups as $ids) : $panel = $uid . '-panel-' . $index; $many = count($ids) > 4; ?>
        <div class="iw-knowledge-panel" role="tabpanel" id="<?php echo esc_attr($panel); ?>" aria-labelledby="<?php echo esc_attr($panel); ?>-tab" <?php echo $index ? 'hidden' : ''; ?>>
          <div class="iw-knowledge-viewport" data-iw-knowledge-viewport><div class="iw-knowledge-cards<?php echo $many ? ' is-scrollable' : ''; ?>">
          <?php foreach ($ids as $id) : $description = get_post_field('post_excerpt', $id); if (!$description) $description = get_post_meta($id, '_interworld_short_definition', true); ?>
            <a class="iw-knowledge-card" href="<?php echo esc_url(get_permalink($id)); ?>">
              <span class="iw-knowledge-image"><?php if (has_post_thumbnail($id)) echo get_the_post_thumbnail($id, 'thumbnail', array('loading' => 'lazy', 'alt' => '')); else echo '<span aria-hidden="true">✦</span>'; ?></span>
              <span class="iw-knowledge-copy"><strong><?php echo esc_html(get_the_title($id)); ?></strong><?php if ($description) : ?><small><?php echo esc_html(wp_trim_words(wp_strip_all_tags($description), 16)); ?></small><?php endif; ?></span><span aria-hidden="true">›</span>
            </a>
          <?php endforeach; ?>
          </div></div>
          <?php if ($many) : ?><div class="iw-knowledge-controls"><button type="button" data-iw-knowledge-prev aria-label="Предыдущие связи">←</button><button type="button" data-iw-knowledge-next aria-label="Следующие связи">→</button></div><?php endif; ?>
        </div>
      <?php $index++; endforeach; ?>
    </section>
    <?php
}

function interworld_knowledge_sources($context) {
    $items = interworld_knowledge_value('iw_knowledge_sources', $context);
    if (!is_array($items)) return;
    $groups = array('article' => array(), 'video' => array(), 'other' => array());
    foreach ($items as $item) {
        if (!is_array($item) || empty($item['title'])) continue;
        $kind = sanitize_key($item['kind'] ?? 'other');
        $groups[isset($groups[$kind]) ? $kind : 'other'][] = $item;
    }
    $groups = array_filter($groups);
    if (!$groups) return;
    $labels = array('article' => 'Статьи', 'video' => 'Видео', 'other' => 'Другие источники');
    $uid = 'iw-sources-' . sanitize_html_class(str_replace('_', '-', (string) $context));
    ?>
    <section class="iw-knowledge-section iw-library-wrap" aria-labelledby="<?php echo esc_attr($uid); ?>-heading"><h2 id="<?php echo esc_attr($uid); ?>-heading">Источники</h2>
      <div class="iw-knowledge-tabs" role="tablist" aria-label="Типы источников">
        <?php $index = 0; foreach ($groups as $kind => $rows) : $panel = $uid . '-' . $kind; ?><button type="button" role="tab" id="<?php echo esc_attr($panel); ?>-tab" aria-controls="<?php echo esc_attr($panel); ?>" aria-selected="<?php echo $index ? 'false' : 'true'; ?>" tabindex="<?php echo $index ? '-1' : '0'; ?>"><?php echo esc_html($labels[$kind]); ?> <span><?php echo count($rows); ?></span></button><?php $index++; endforeach; ?>
      </div>
      <?php $index = 0; foreach ($groups as $kind => $rows) : $panel = $uid . '-' . $kind; ?><div class="iw-knowledge-panel iw-knowledge-sources" role="tabpanel" id="<?php echo esc_attr($panel); ?>" aria-labelledby="<?php echo esc_attr($panel); ?>-tab" <?php echo $index ? 'hidden' : ''; ?>>
        <?php foreach ($rows as $row) : $title = sanitize_text_field($row['title']); $url = esc_url($row['url'] ?? ''); ?><article class="iw-knowledge-source"><?php if (!empty($row['image']) && $kind !== 'other') : ?><img src="<?php echo esc_url($row['image']); ?>" alt="" loading="lazy"><?php endif; ?><div><h3><?php if ($url) : ?><a href="<?php echo $url; ?>" rel="noopener noreferrer"><?php echo esc_html($title); ?></a><?php else : echo esc_html($title); endif; ?></h3><?php if (!empty($row['date'])) : ?><small><?php echo esc_html($row['date']); ?></small><?php endif; ?><?php if (!empty($row['description'])) : ?><p><?php echo esc_html($row['description']); ?></p><?php endif; ?></div></article><?php endforeach; ?>
      </div><?php $index++; endforeach; ?>
    </section>
    <?php
}

function interworld_knowledge_continue($context, $fallback = array()) {
    $links = array();
    foreach (interworld_knowledge_links($context) as $ids) foreach ($ids as $id) $links[get_permalink($id)] = get_the_title($id);
    foreach ($fallback as $url => $label) if ($url && $label) $links[$url] = $label;
    if (!$links) return;
    ?><section class="iw-tpl03-section iw-tpl03-continue"><div class="iw-tpl03-inner"><h2>Продолжить исследование Межмирья</h2><div class="iw-tpl03-tags"><?php foreach ($links as $url => $label) : ?><a class="iw-tpl03-tag" href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a><?php endforeach; ?></div></div></section><?php
}

add_action('wp_enqueue_scripts', function() {
    if (is_singular(array('library_book', 'person')) || is_tax(array('library_category', 'person_category')) || is_post_type_archive('person') || is_page_template('page-biblioteka.php')) {
        wp_enqueue_style('interworld-tpl03', get_template_directory_uri() . '/tpl03.css', array(), filemtime(get_template_directory() . '/tpl03.css'));
        wp_enqueue_style('interworld-knowledge', get_template_directory_uri() . '/knowledge-blocks.css', array(), filemtime(get_template_directory() . '/knowledge-blocks.css'));
        wp_enqueue_script('interworld-knowledge', get_template_directory_uri() . '/knowledge-blocks.js', array(), filemtime(get_template_directory() . '/knowledge-blocks.js'), true);
    }
}, 30);
