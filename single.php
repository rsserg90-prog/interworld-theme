<?php
get_header();

$object_id = get_queried_object_id();

/* Основные данные объекта */
$object_code = get_field('object_code', $object_id);
$object_number = get_field('object_number', $object_id);
$object_short_definition = get_field('object_short_definition', $object_id);
$object_banner_description = get_field('object_banner_description', $object_id);

$object_function = get_field('object_function', $object_id);
$object_central_element = get_field('object_center', $object_id);
$object_principle = get_field('object_principle', $object_id);
$object_epiklety = get_field('object_epiklety', $object_id);

/* Внешние материалы: показываем только те ссылки, у которых заполнен URL */
$object_external_links = array(
    array(
        'url' => get_field('object_link_youtube', $object_id),
        'label' => get_field('object_link_youtube_label', $object_id) ?: 'Смотреть на YouTube',
        'logo' => get_field('object_link_youtube_logo', $object_id),
        'class' => 'source-youtube',
        'fallback_icon' => '▶',
    ),
    array(
        'url' => get_field('object_link_enmerkar', $object_id),
        'label' => get_field('object_link_enmerkar_label', $object_id) ?: 'Блог Энмеркара',
        'logo' => get_field('object_link_enmerkar_logo', $object_id),
        'class' => 'source-blog',
        'fallback_icon' => '▤',
    ),
    array(
        'url' => get_field('object_link_masters_heart', $object_id),
        'label' => get_field('object_link_masters_heart_label', $object_id) ?: 'Сердце Мастера',
        'logo' => get_field('object_link_masters_heart_logo', $object_id),
        'class' => 'source-masters',
        'fallback_icon' => '♡',
    ),
    array(
        'url' => get_field('object_link_telegram', $object_id),
        'label' => get_field('object_link_telegram_label', $object_id) ?: 'Telegram',
        'logo' => get_field('object_link_telegram_logo', $object_id),
        'class' => 'source-telegram',
        'fallback_icon' => '✈',
    ),
    array(
        'url' => get_field('object_link_litres', $object_id),
        'label' => get_field('object_link_litres_label', $object_id) ?: 'Читать на ЛитРес',
        'logo' => get_field('object_link_litres_logo', $object_id),
        'class' => 'source-litres',
        'fallback_icon' => '▤',
    ),
    array(
        'url' => get_field('object_link_geography', $object_id),
        'label' => get_field('object_link_geography_label', $object_id) ?: 'География Межмирья',
        'logo' => get_field('object_link_geography_logo', $object_id),
        'class' => 'source-interworld',
        'fallback_icon' => '✦',
    ),
);

$object_types = get_the_terms($object_id, 'object_type');
$object_groups = get_the_terms($object_id, 'object_group');

$object_type = (!empty($object_types) && !is_wp_error($object_types)) ? $object_types[0] : null;
$object_group = (!empty($object_groups) && !is_wp_error($object_groups)) ? $object_groups[0] : null;

/* Собираем только заполненные характеристики */
$characteristics = array();

if ($object_type) {
    $type_link = get_term_link($object_type, 'object_type');
    $characteristics[] = array(
        'label' => 'Тип',
        'value' => !is_wp_error($type_link)
            ? '<a href="' . esc_url($type_link) . '">' . esc_html($object_type->name) . '</a>'
            : esc_html($object_type->name),
        'html' => true,
    );
}

if ($object_group) {
    $group_link = get_term_link($object_group, 'object_group');
    $characteristics[] = array(
        'label' => 'Группа',
        'value' => !is_wp_error($group_link)
            ? '<a href="' . esc_url($group_link) . '">' . esc_html($object_group->name) . '</a>'
            : esc_html($object_group->name),
        'html' => true,
    );
}

if ($object_code !== '' && $object_code !== null) {
    $characteristics[] = array('label' => 'Код', 'value' => esc_html($object_code));
}
if ($object_number !== '' && $object_number !== null) {
    $characteristics[] = array('label' => 'Номер', 'value' => esc_html($object_number));
}
if ($object_function) {
    $characteristics[] = array('label' => 'Функция', 'value' => wp_kses_post(wpautop($object_function)), 'html' => true);
}
if ($object_central_element) {
    $characteristics[] = array('label' => 'Центральный элемент', 'value' => esc_html($object_central_element));
}
if ($object_principle) {
    $characteristics[] = array('label' => 'Принцип', 'value' => esc_html($object_principle));
}
if ($object_epiklety) {
    $characteristics[] = array('label' => 'Эпиклеты', 'value' => wp_kses_post($object_epiklety), 'html' => true);
}

/* Галерея объекта: ID изображений сохраняются отдельным сниппетом */
$object_gallery_ids = get_post_meta($object_id, '_interworld_gallery_images', true);
if (!is_array($object_gallery_ids)) {
    $object_gallery_ids = array();
}
$object_gallery = array();
foreach ($object_gallery_ids as $gallery_image_id) {
    $gallery_image_id = absint($gallery_image_id);
    $full_url = wp_get_attachment_image_url($gallery_image_id, 'full');
    if (!$gallery_image_id || !$full_url) {
        continue;
    }
    $object_gallery[] = array(
        'id' => $gallery_image_id,
        'thumb' => wp_get_attachment_image_url($gallery_image_id, 'large') ?: $full_url,
        'full' => $full_url,
        'alt' => get_post_meta($gallery_image_id, '_wp_attachment_image_alt', true) ?: get_the_title($gallery_image_id),
        'caption' => wp_get_attachment_caption($gallery_image_id) ?: '',
    );
}
$object_gallery_count = count($object_gallery);

/* FAQ объекта: данные сохраняются сниппетом INTERWORLD — FAQ объектов */
$object_faq_items = get_post_meta($object_id, '_interworld_faq_items', true);
if (!is_array($object_faq_items)) {
    $object_faq_items = array();
}
$object_faq = array();
foreach ($object_faq_items as $faq_item) {
    if (!is_array($faq_item)) {
        continue;
    }
    $faq_question = isset($faq_item['question']) ? trim((string) $faq_item['question']) : '';
    $faq_answer = isset($faq_item['answer']) ? trim((string) $faq_item['answer']) : '';
    if ($faq_question === '' || $faq_answer === '') {
        continue;
    }
    $object_faq[] = array(
        'question' => $faq_question,
        'answer' => $faq_answer,
    );
}

/* Цитаты объекта: данные сохраняются сниппетом INTERWORLD — Цитаты объектов */
$object_quotes_raw = get_post_meta($object_id, '_interworld_quotes', true);
if (!is_array($object_quotes_raw)) {
    $object_quotes_raw = array();
}
$object_quotes = array();
foreach ($object_quotes_raw as $quote_item) {
    if (!is_array($quote_item)) {
        continue;
    }
    $quote_text = isset($quote_item['quote']) ? trim((string) $quote_item['quote']) : '';
    $quote_source = isset($quote_item['source']) ? trim((string) $quote_item['source']) : '';
    $quote_url = isset($quote_item['url']) ? trim((string) $quote_item['url']) : '';
    if ($quote_text === '') {
        continue;
    }
    $object_quotes[] = array(
        'quote' => $quote_text,
        'source' => $quote_source,
        'url' => $quote_url,
    );
}
$object_quotes_count = count($object_quotes);

$has_external_links = false;
foreach ($object_external_links as $external_link) {
    if (!empty($external_link['url'])) {
        $has_external_links = true;
        break;
    }
}
?>

<main class="interworld-object-page">
<?php while (have_posts()) : the_post(); ?>
    <article class="object-entry">

        <!-- 1. Главный баннер -->
        <section class="object-hero" id="definition">
            <nav class="object-breadcrumbs" aria-label="Хлебные крошки">
                <a href="<?php echo esc_url(home_url('/')); ?>">География Межмирья</a>

                <?php if ($object_type) : ?>
                    <span aria-hidden="true">→</span>
                    <?php $type_link = get_term_link($object_type, 'object_type'); ?>
                    <?php if (!is_wp_error($type_link)) : ?>
                        <a href="<?php echo esc_url($type_link); ?>"><?php echo esc_html($object_type->name); ?></a>
                    <?php else : ?>
                        <span><?php echo esc_html($object_type->name); ?></span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($object_group) : ?>
                    <span aria-hidden="true">→</span>
                    <?php $group_link = get_term_link($object_group, 'object_group'); ?>
                    <?php if (!is_wp_error($group_link)) : ?>
                        <a href="<?php echo esc_url($group_link); ?>"><?php echo esc_html($object_group->name); ?></a>
                    <?php else : ?>
                        <span><?php echo esc_html($object_group->name); ?></span>
                    <?php endif; ?>
                <?php endif; ?>

                <span aria-hidden="true">→</span>
                <span class="current"><?php the_title(); ?></span>
            </nav>

            <div class="object-hero-content">
                <div class="eyebrow">Материал энциклопедии</div>
                <h1><?php the_title(); ?></h1>

                <?php if ($object_short_definition) : ?>
                    <div class="object-subtitle"><?php echo esc_html($object_short_definition); ?></div>
                <?php endif; ?>

                <?php if ($object_banner_description) : ?>
                    <div class="object-banner-description">
                        <?php echo wp_kses_post(wpautop($object_banner_description)); ?>
                    </div>
                <?php endif; ?>

                <?php if ($has_external_links) : ?>
                    <div class="object-important-links">
                        <?php foreach ($object_external_links as $external_link) : ?>
                            <?php if (!empty($external_link['url'])) : ?>
                                <a
                                    href="<?php echo esc_url($external_link['url']); ?>"
                                    class="source-link <?php echo esc_attr($external_link['class']); ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <?php
                                    $logo_id = 0;
                                    if (is_array($external_link['logo']) && !empty($external_link['logo']['ID'])) {
                                        $logo_id = (int) $external_link['logo']['ID'];
                                    } elseif (is_numeric($external_link['logo'])) {
                                        $logo_id = (int) $external_link['logo'];
                                    }
                                    ?>
                                    <?php if ($logo_id) : ?>
                                        <?php echo wp_get_attachment_image($logo_id, 'thumbnail', false, array(
                                            'class' => 'source-link-logo',
                                            'alt' => esc_attr($external_link['label']),
                                        )); ?>
                                    <?php else : ?>
                                        <span class="source-link-icon" aria-hidden="true"><?php echo esc_html($external_link['fallback_icon']); ?></span>
                                    <?php endif; ?>
                                    <span><?php echo esc_html($external_link['label']); ?></span>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (has_post_thumbnail()) : ?>
                <div class="object-hero-image">
                    <?php the_post_thumbnail('large', array('alt' => esc_attr(get_the_title()))); ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- 2. Якорная навигация: находится под баннером -->
        <nav class="object-anchor-nav" aria-label="Навигация по странице">
            <div class="object-anchor-nav-inner">
                <?php if (!empty($characteristics)) : ?>
                    <a href="#characteristics" class="is-active">Характеристики</a>
                <?php endif; ?>

                <?php if (trim(get_the_content())) : ?>
                    <a href="#description">Описание</a>
                <?php endif; ?>
                <?php if (!empty($object_gallery_count)) : ?>
                    <a href="#gallery">Галерея</a>
                <?php endif; ?>
                <?php if (!empty($object_faq)) : ?>
                    <a href="#faq">Вопросы и ответы</a>
                <?php endif; ?>
                <?php if (!empty($object_quotes)) : ?>
                    <a href="#quotes">Цитаты</a>
                <?php endif; ?>
                <a href="#context">Личности и контекст</a>
                <a href="#deep-study">Материалы для изучения</a>
                <a href="#sources">Источники и литература</a>

                <?php
                /*
                 * Дополнительные разделы добавим в навигацию после настройки
                 * соответствующих полей ACF: взаимосвязи, изображения,
                 * особенности, цитаты, FAQ, источники и книги.
                 */
                ?>
            </div>
        </nav>

        <!-- 3. Характеристики -->
        <?php if (!empty($characteristics)) : ?>
            <section class="object-section object-characteristics" id="characteristics">
                <div class="object-section-heading">
                  
                    <h2>Характеристики</h2>
                </div>

                <div class="characteristics-list">
                    <?php foreach ($characteristics as $item) : ?>
                        <div class="characteristic-row">
                            <div class="characteristic-name"><?php echo esc_html($item['label']); ?></div>
                            <div class="characteristic-value">
                                <?php
                                if (!empty($item['html'])) {
                                    echo wp_kses_post($item['value']);
                                } else {
                                    echo $item['value'];
                                }
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- 4. Взаимосвязи Межмирья -->
        <?php
        $relation_labels = array(
            'aeon' => 'Эоны',
            'ether' => 'Эфиры',
            'archon' => 'Архонты',
            'deity' => 'Боги | Божества',
            'space' => 'Пространства Межмирья',
            'symbol' => 'Символы и атрибуты',
            'creature' => 'Существа Промежутка',
            'fairy' => 'Фейри',
            'city' => 'Города Межмирья',
            'book' => 'Книги',
            'afterlife' => 'Посмертие (Дуат)',
            'other' => 'Другие связи (ранее созданные)',
        );
        // Совместимость со старыми внутренними ключами категорий.
        $relation_type_aliases = array(
            'efir' => 'ether', 'efiry' => 'ether', 'efir-tex' => 'ether',
            'arkhonty' => 'archon', 'bogi' => 'deity',
            'prostranstva-mezhmirya' => 'space', 'simvoli-i-atributi' => 'symbol',
            'sushchestva-promezhutka' => 'creature', 'goroda-mezhmirya' => 'city',
            'knigi' => 'book', 'posmertie' => 'afterlife',
            'eony' => 'aeon', 'efiry' => 'ether',
        );
        $normalize_relation_type = function($value) use ($relation_type_aliases, $relation_labels) {
            $value = sanitize_key((string) $value);
            if (isset($relation_type_aliases[$value])) $value = $relation_type_aliases[$value];
            return isset($relation_labels[$value]) ? $value : 'other';
        };
        $relation_items = array();
        $outgoing_relations = get_post_meta($object_id, '_interworld_relations', true);
        if (is_array($outgoing_relations)) {
            foreach ($outgoing_relations as $relation) {
                if (!is_array($relation)) continue;
                $target_id = absint($relation['target_id'] ?? 0);
                if (!$target_id) $target_id = absint($relation['related_object_id'] ?? $relation['object_id'] ?? $relation['related_id'] ?? 0);
                if (!$target_id || get_post_type($target_id) !== 'interworld_object') continue;
                $target_status = get_post_status($target_id);
               if (!in_array($target_status, array('publish', 'draft'), true)) continue;
                $type = $normalize_relation_type($relation['type'] ?? 'other');
                $relation_items[] = array(
                    'target_id' => $target_id,
                    'type' => $type,
                    'context_section' => in_array(sanitize_key($relation['context_section'] ?? ''), array('persons', 'researchers', 'traditions'), true) ? sanitize_key($relation['context_section']) : '',
                    'label' => sanitize_text_field($relation['label'] ?? ''),
                    'explanation' => sanitize_textarea_field($relation['explanation'] ?? ''),
                    'order' => absint($relation['order'] ?? 0),
                    'direction' => 'out',
                );
            }
        }
        // Находим обратные связи, заданные на страницах других объектов.
        $all_relation_posts = get_posts(array(
            'post_type' => 'interworld_object',
            'post_status' => array('publish', 'private'),
            'posts_per_page' => -1,
            'fields' => 'ids',
            'post__not_in' => array($object_id),
            'no_found_rows' => true,
        ));
        foreach ($all_relation_posts as $source_id) {
            $source_relations = get_post_meta($source_id, '_interworld_relations', true);
            if (!is_array($source_relations)) continue;
            foreach ($source_relations as $relation) {
                if (!is_array($relation)) continue;
                $legacy_target_id = absint($relation['target_id'] ?? 0);
                if (!$legacy_target_id) $legacy_target_id = absint($relation['related_object_id'] ?? $relation['object_id'] ?? $relation['related_id'] ?? 0);
                if ($legacy_target_id !== (int) $object_id) continue;
                if (get_post_status($source_id) !== 'publish') continue;
                $type = $normalize_relation_type($relation['type'] ?? 'other');
                $relation_items[] = array(
                    'target_id' => (int) $source_id,
                    'type' => $type,
                    'context_section' => in_array(sanitize_key($relation['context_section'] ?? ''), array('persons', 'researchers', 'traditions'), true) ? sanitize_key($relation['context_section']) : '',
                    'label' => sanitize_text_field($relation['reverse_label'] ?? ''),
                    'explanation' => sanitize_textarea_field($relation['explanation'] ?? ''),
                    'order' => absint($relation['order'] ?? 0),
                    'direction' => 'in',
                );
            }
        }
        // Убираем возможные дубликаты и сортируем по заданному порядку.
        $unique_relations = array();
        foreach ($relation_items as $item) {
            $unique_relations[$item['target_id'] . ':' . $item['type'] . ':' . $item['context_section']] = $item;
        }
        $relation_items = array_values($unique_relations);
        usort($relation_items, function($a, $b) { return $a['order'] <=> $b['order']; });
        $relations_by_type = array();
        foreach ($relation_items as $item) $relations_by_type[$item['type']][] = $item;

        // «Личности и контекст» хранится отдельно от «Взаимосвязей Межмирья».
        $context_labels = array(
            'persons' => 'Личности',
            'researchers' => 'Исследователи',
            'traditions' => 'Традиции',
        );
        $context_by_section = array(
            'persons' => array(),
            'researchers' => array(),
            'traditions' => array(),
        );
        $saved_context_items = get_post_meta($object_id, '_interworld_context_items', true);
        if (is_array($saved_context_items)) {
            foreach ($saved_context_items as $context_item) {
                if (!is_array($context_item)) continue;
                $context_target_id = absint($context_item['target_id'] ?? 0);
                $context_section = sanitize_key($context_item['section'] ?? '');
                if (!$context_target_id || get_post_type($context_target_id) !== 'interworld_object') continue;
                if (!isset($context_by_section[$context_section])) continue;
                $context_status = get_post_status($context_target_id);
                if (!in_array($context_status, array('publish', 'draft'), true)) continue;
                $context_by_section[$context_section][] = array(
                    'target_id' => $context_target_id,
                    'label' => sanitize_text_field($context_item['label'] ?? ''),
                    'explanation' => sanitize_textarea_field($context_item['explanation'] ?? ''),
                    'order' => absint($context_item['order'] ?? 0),
                );
            }
        }
        foreach ($context_by_section as &$context_items_for_section) {
            usort($context_items_for_section, function($a, $b) { return $a['order'] <=> $b['order']; });
        }
        unset($context_items_for_section);
        $context_total = array_sum(array_map('count', $context_by_section));
        $interworld_section_items = get_post_meta($object_id, '_interworld_section_items', true);
        if (!is_array($interworld_section_items)) $interworld_section_items = array();
        $deep_study_items = array('articles' => array(), 'video' => array(), 'other' => array());
        $source_items = array('source' => array(), 'literature' => array());
        foreach ($interworld_section_items as $section_item) {
            if (!is_array($section_item)) continue;
            $section_target_id = absint($section_item['target_id'] ?? 0);
            if (!$section_target_id || get_post_type($section_target_id) !== 'interworld_object') continue;
            if (!in_array(get_post_status($section_target_id), array('publish','draft'), true)) continue;
            $section_key = sanitize_key($section_item['section'] ?? '');
            $prepared_item = array('target_id' => $section_target_id, 'label' => sanitize_text_field($section_item['label'] ?? ''), 'explanation' => sanitize_textarea_field($section_item['explanation'] ?? ''), 'order' => absint($section_item['order'] ?? 0));
            if ($section_key === 'deep_study') {
                $study_subsection = sanitize_key($section_item['subsection'] ?? 'articles');
                if (!isset($deep_study_items[$study_subsection])) $study_subsection = 'other';
                $deep_study_items[$study_subsection][] = $prepared_item;
            }
            if ($section_key === 'sources') {
                $subsection_key = sanitize_key($section_item['subsection'] ?? 'source');
                if (!isset($source_items[$subsection_key])) $subsection_key = 'source';
                $source_items[$subsection_key][] = $prepared_item;
            }
        }
        foreach ($deep_study_items as &$study_group) usort($study_group, function($a, $b) { return $a['order'] <=> $b['order']; });
        unset($study_group);
        foreach ($source_items as &$source_group) usort($source_group, function($a, $b) { return $a['order'] <=> $b['order']; });
        unset($source_group);
        ?>
        <?php if (!empty($relation_items)) : ?>
            <section class="object-section interworld-relations" id="relations">
                <div class="object-section-heading">
                    <div class="eyebrow">Связи Межмирья</div>
                    <h2>Взаимосвязи</h2>
                </div>
                <style>
                    .interworld-relations .relation-tabs{display:flex;flex-wrap:wrap;gap:24px;border-bottom:1px solid #dedbd3;margin:24px 0 20px}
                    .interworld-relations .relation-tab{appearance:none;background:transparent;border:0;border-bottom:2px solid transparent;padding:10px 0;color:inherit;cursor:pointer;font:inherit;opacity:.72}
                    .interworld-relations .relation-tab[aria-selected="true"]{border-bottom-color:#b88a4a;opacity:1}
                    .interworld-relations .relation-cards{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
                    .interworld-relations .relation-card{display:flex;align-items:center;gap:18px;min-width:0;border:1px solid #e1ded7;padding:16px;color:inherit;text-decoration:none;background:rgba(255,255,255,.25)}
                    .interworld-relations .relation-card:hover{border-color:#b88a4a}
                    .interworld-relations .relation-card-draft{cursor:default;opacity:.88}
                    .interworld-relations .relation-card-draft:hover{border-color:#e1ded7}
                    .interworld-relations .relation-image{width:86px;height:86px;flex:0 0 86px;object-fit:cover;background:#e9e6de;display:flex;align-items:center;justify-content:center;color:#9b927f;font-size:26px}
                    .interworld-relations .relation-image img{width:100%;height:100%;object-fit:cover}
                    .interworld-relations .relation-card-title{font-family:inherit;font-size:20px;line-height:1.3;margin-bottom:6px}
                    .interworld-relations .relation-card-description,.interworld-relations .relation-card-label{font-size:14px;opacity:.72;line-height:1.5}
                    .interworld-relations .relation-card-arrow{margin-left:auto;color:#b88a4a;font-size:24px}
                    @media(max-width:700px){.interworld-relations .relation-cards{grid-template-columns:1fr}.interworld-relations .relation-card{gap:12px;padding:12px}.interworld-relations .relation-image{width:68px;height:68px;flex-basis:68px}}
                </style>
                <div class="relation-tabs" role="tablist" aria-label="Типы взаимосвязей">
                    <?php $tab_index = 0; foreach ($relations_by_type as $type => $items) : ?>
                        <button type="button" class="relation-tab" role="tab" id="relation-tab-<?php echo esc_attr($type); ?>" aria-controls="relation-panel-<?php echo esc_attr($type); ?>" aria-selected="<?php echo $tab_index === 0 ? 'true' : 'false'; ?>" tabindex="<?php echo $tab_index === 0 ? '0' : '-1'; ?>">
                            <?php echo esc_html($relation_labels[$type]); ?> <span><?php echo count($items); ?></span>
                        </button>
                    <?php $tab_index++; endforeach; ?>
                </div>
                <?php $panel_index = 0; foreach ($relations_by_type as $type => $items) : ?>
                    <div class="relation-panel" role="tabpanel" id="relation-panel-<?php echo esc_attr($type); ?>" aria-labelledby="relation-tab-<?php echo esc_attr($type); ?>" <?php echo $panel_index > 0 ? 'hidden' : ''; ?>>
                        <div class="relation-cards">
                            <?php foreach ($items as $item) :
                                $target_id = $item['target_id'];
                                $title = get_the_title($target_id);
                                $url = get_permalink($target_id);
                                $excerpt = get_post_field('post_excerpt', $target_id);
                                if (!$excerpt) $excerpt = get_post_meta($target_id, '_interworld_short_definition', true);
                                $target_status = get_post_status($target_id);
                                $is_published = ($target_status === 'publish');

                                /*
                                 * Заглушка выбирается по типу самого связанного объекта (object_type),
                                 * а не по вкладке связи. Пока объект не опубликован, его собственная
                                 * миниатюра не показывается: вместо неё выводится типовая заглушка.
                                 * После публикации показываем миниатюру, назначенную объекту в админке.
                                 */
                                $placeholders = array(
                                    'arkhonty' => 'https://geografiya-mezhmirya.ru/wp-content/uploads/2026/10/Archons-interworld-1.webp',
                                    'bogi' => 'https://geografiya-mezhmirya.ru/wp-content/uploads/2026/10/Deities-interworld-1.webp',
                                    'goroda-mezhmirya' => 'https://geografiya-mezhmirya.ru/wp-content/uploads/2026/10/Cities-interworld-1.webp',
                                    'knigi' => 'https://geografiya-mezhmirya.ru/wp-content/uploads/2026/10/Book-interworld-1.webp',
                                    'person' => 'https://geografiya-mezhmirya.ru/wp-content/uploads/2026/10/Person-interworld-1.webp',
                                    'posmertie' => 'https://geografiya-mezhmirya.ru/wp-content/uploads/2026/10/Duat-interworld-1.webp',
                                    'sushchestva-promezhutka' => 'https://geografiya-mezhmirya.ru/wp-content/uploads/2026/10/Creatures-interworld-1.webp',
                                    'simvoli-i-atributi' => 'https://geografiya-mezhmirya.ru/wp-content/uploads/2026/10/Symbols-interworld-1.webp',
                                    'tradicii' => 'https://geografiya-mezhmirya.ru/wp-content/uploads/2026/10/Traditions-interworld-1.webp',
                                    'eony' => 'https://geografiya-mezhmirya.ru/wp-content/uploads/2026/10/Eony-interworld-1.webp',
                                    'efiry' => 'https://geografiya-mezhmirya.ru/wp-content/uploads/2026/10/Efiri-interworld-1.webp',
                                    'prostranstva-mezhmirya' => 'https://geografiya-mezhmirya.ru/wp-content/uploads/2026/10/Spaces-interworld-1.webp',
                                );
                                $fallback_placeholder = 'https://geografiya-mezhmirya.ru/wp-content/uploads/2026/10/other-interworld-1.webp';
                                $target_terms = get_the_terms($target_id, 'object_type');
                                $target_type_slug = (!empty($target_terms) && !is_wp_error($target_terms)) ? $target_terms[0]->slug : '';
                                $placeholder_url = isset($placeholders[$target_type_slug]) ? $placeholders[$target_type_slug] : $fallback_placeholder;

                                if ($is_published) {
                                    $thumbnail = get_the_post_thumbnail($target_id, 'thumbnail', array('alt' => $title));
                                    if (!$thumbnail) {
                                        $thumbnail = '<img src="' . esc_url($placeholder_url) . '" alt="' . esc_attr($title) . '" loading="lazy" />';
                                    }
                                } else {
                                    $thumbnail = '<img src="' . esc_url($placeholder_url) . '" alt="' . esc_attr($title . ' — материал готовится') . '" loading="lazy" />';
                                }

                                $card_tag = $is_published ? 'a' : 'div';
                                $card_attributes = $is_published
                                    ? ' class="relation-card" href="' . esc_url($url) . '"'
                                    : ' class="relation-card relation-card-draft" aria-label="' . esc_attr($title . ' — черновик') . '"';
                            ?>
                                <?php echo '<' . $card_tag . $card_attributes . '>'; ?>
                                    <span class="relation-image"><?php echo $thumbnail ? wp_kses_post($thumbnail) : '<span aria-hidden="true">✦</span>'; ?></span>
                                    <span class="relation-card-body">
                                        <span class="relation-card-title"><?php echo esc_html($title); ?></span>
                                        <?php if (!empty($excerpt)) : ?><span class="relation-card-description"><?php echo esc_html(wp_trim_words(wp_strip_all_tags($excerpt), 18)); ?></span><?php endif; ?>
                                        <?php if (!empty($item['label'])) : ?><span class="relation-card-label"><?php echo esc_html($item['label']); ?></span><?php endif; ?>
                                        <?php if (!empty($item['explanation'])) : ?><span class="relation-card-description"><?php echo esc_html($item['explanation']); ?></span><?php endif; ?>
                                        <?php if (!$is_published) : ?><span class="relation-card-label">Материал готовится</span><?php endif; ?>
                                    </span>
                                    <?php if ($is_published) : ?><span class="relation-card-arrow" aria-hidden="true">›</span><?php endif; ?>
                                <?php echo '</' . $card_tag . '>'; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php $panel_index++; endforeach; ?>
                <script>
                (function(){
                    document.querySelectorAll('.interworld-relations').forEach(function(section){
                        if(section.dataset.tabsReady) return;
                        section.dataset.tabsReady='1';
                        var tabs=Array.from(section.querySelectorAll('[role="tab"]'));
                        tabs.forEach(function(tab){tab.addEventListener('click',function(){
                            tabs.forEach(function(other){
                                var active=other===tab;
                                other.setAttribute('aria-selected',active?'true':'false');
                                other.tabIndex=active?0:-1;
                                var panel=section.querySelector('#'+other.getAttribute('aria-controls'));
                                if(panel) panel.hidden=!active;
                            });
                        });});
                    });
                })();
                </script>
            </section>
        <?php endif; ?>

        <!-- 5. Описание с фиксированной высотой и кнопкой раскрытия -->
        <?php if (trim(get_the_content())) : ?>
            <section class="object-section object-description" id="description">
                <div class="object-section-heading">
                    <div class="eyebrow">Исследование</div>
                    <h2>Описание</h2>
                </div>
                <div class="object-description-content">
                    <div class="object-description-text is-collapsed">
                        <?php the_content(); ?>
                    </div>
                    <button type="button" class="object-description-toggle" aria-expanded="false">
                        Читать далее <span aria-hidden="true">⌄</span>
                    </button>
                </div>
            </section>

            <style>
                .object-description-content{position:relative}
                .object-description-text{overflow-wrap:anywhere}
                .object-description-text.is-collapsed{
                    max-height:180px;
                    overflow:hidden;
                    mask-image:linear-gradient(to bottom,#000 0%,#000 72%,transparent 100%);
                    -webkit-mask-image:linear-gradient(to bottom,#000 0%,#000 72%,transparent 100%);
                }
                .object-description-text.is-expanded{
                    max-height:none;
                    overflow:visible;
                    mask-image:none;
                    -webkit-mask-image:none;
                }
                .object-description-toggle{
                    display:inline-flex;align-items:center;gap:8px;margin-top:14px;padding:8px 0;
                    border:0;background:transparent;color:var(--interworld-ink,#19303e);
                    font:inherit;font-weight:500;cursor:pointer
                }
                .object-description-toggle:hover{text-decoration:underline;text-underline-offset:4px}
                .object-description-toggle span{display:inline-block;font-size:1.2em;line-height:1;transition:transform .2s ease}
                .object-description-toggle[aria-expanded="true"] span{transform:rotate(180deg)}
            </style>

            <script>
            (function(){
                function initDescription(section){
                    if(section.dataset.descriptionReady==='1') return;
                    section.dataset.descriptionReady='1';

                    var text=section.querySelector('.object-description-text');
                    var button=section.querySelector('.object-description-toggle');
                    if(!text||!button) return;

                    button.addEventListener('click',function(){
                        var expanded=button.getAttribute('aria-expanded')==='true';
                        if(expanded){
                            text.classList.remove('is-expanded');
                            text.classList.add('is-collapsed');
                            button.setAttribute('aria-expanded','false');
                            button.innerHTML='Читать далее <span aria-hidden="true">⌄</span>';
                        }else{
                            text.classList.remove('is-collapsed');
                            text.classList.add('is-expanded');
                            button.setAttribute('aria-expanded','true');
                            button.innerHTML='Свернуть <span aria-hidden="true">⌃</span>';
                        }
                    });
                }

                function initAll(){
                    document.querySelectorAll('.object-description').forEach(initDescription);
                }

                if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',initAll);
                else initAll();
            })();
            </script>
        <?php endif; ?>

        <!-- 6. Галерея изображений объекта -->
        <?php if ($object_gallery_count > 0) : ?>
            <section class="object-section object-gallery-section" id="gallery">
                <div class="object-section-heading">
                    <div class="eyebrow">Визуальные материалы</div>
                    <h2>Галерея</h2>
                </div>

                <div class="object-gallery-carousel<?php echo $object_gallery_count > 4 ? ' has-slider' : ''; ?>">
                    <?php if ($object_gallery_count > 4) : ?>
                        <button type="button" class="object-gallery-prev" aria-label="Предыдущие изображения">‹</button>
                    <?php endif; ?>
                    <div class="object-gallery-viewport">
                        <div class="object-gallery-grid<?php echo $object_gallery_count > 4 ? ' is-slider-track' : ' is-static-row'; ?>" <?php echo $object_gallery_count > 4 ? 'data-gallery-track' : ''; ?>>
                            <?php foreach ($object_gallery as $index => $image) : ?>
                                <figure class="object-gallery-item">
                                    <button
                                        type="button"
                                        class="object-gallery-image-button"
                                        data-gallery-index="<?php echo esc_attr($index); ?>"
                                        aria-label="<?php echo esc_attr('Открыть изображение: ' . $image['alt']); ?>"
                                    >
                                        <img
                                            src="<?php echo esc_url($image['thumb']); ?>"
                                            alt="<?php echo esc_attr($image['alt']); ?>"
                                            loading="lazy"
                                        >
                                    </button>
                                    <?php if (trim($image['caption']) !== '') : ?>
                                        <figcaption><?php echo esc_html($image['caption']); ?></figcaption>
                                    <?php endif; ?>
                                </figure>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php if ($object_gallery_count > 4) : ?>
                        <button type="button" class="object-gallery-next" aria-label="Следующие изображения">›</button>
                    <?php endif; ?>
                </div>

                <script type="application/json" class="object-gallery-data"><?php
                    echo wp_json_encode($object_gallery, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
                ?></script>
            </section>

            <div class="object-gallery-lightbox" hidden role="dialog" aria-modal="true" aria-label="Просмотр изображения">
                <button type="button" class="object-gallery-lightbox-close" aria-label="Закрыть изображение">×</button>
                <button type="button" class="object-gallery-lightbox-prev" aria-label="Предыдущее изображение">←</button>
                <figure>
                    <img src="" alt="">
                    <figcaption hidden></figcaption>
                </figure>
                <button type="button" class="object-gallery-lightbox-next" aria-label="Следующее изображение">→</button>
            </div>

            <style>
                .object-gallery-carousel{display:flex;align-items:center;gap:18px;width:100%}
                .object-gallery-viewport{flex:1;min-width:0;overflow:hidden}
                .object-gallery-grid{display:flex;align-items:stretch;gap:18px;width:100%}
                .object-gallery-grid.is-static-row .object-gallery-item{flex:1 1 0;min-width:0}
                .object-gallery-grid.is-slider-track{transition:transform .35s ease;will-change:transform}
                .object-gallery-grid.is-slider-track .object-gallery-item{flex:0 0 calc((100% - 54px)/4);min-width:0}
                .object-gallery-item{margin:0;min-width:0;display:flex;flex-direction:column}
                .object-gallery-image-button{display:flex;align-items:center;justify-content:center;width:100%;aspect-ratio:1/1;height:auto;padding:0;border:0;background:rgba(20,35,45,.07);cursor:zoom-in;overflow:hidden}
                .object-gallery-image-button img{display:block;width:100%;height:100%;object-fit:contain;transition:transform .25s ease}
                .object-gallery-image-button:hover img{transform:scale(1.015)}
                .object-gallery-item figcaption{padding-top:10px;color:var(--interworld-muted,#697984);font-size:.92rem;line-height:1.5;text-align:left;overflow-wrap:anywhere}
                .object-gallery-prev,.object-gallery-next,.object-gallery-lightbox-prev,.object-gallery-lightbox-next,.object-gallery-lightbox-close{display:flex;align-items:center;justify-content:center;flex:0 0 46px;width:46px;height:56px;border:1px solid currentColor;background:transparent;color:inherit;cursor:pointer;padding:0;font-size:30px;line-height:1}
                .object-gallery-prev:hover,.object-gallery-next:hover{background:rgba(20,35,45,.06)}
                .object-gallery-prev:disabled,.object-gallery-next:disabled{cursor:default}
                .object-gallery-lightbox[hidden]{display:none}
                .object-gallery-lightbox{position:fixed;inset:0;z-index:99999;background:rgba(5,15,23,.94);display:flex;align-items:center;justify-content:space-between;gap:18px;padding:30px}
                .object-gallery-lightbox figure{margin:0;flex:1;min-width:0;display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%}
                .object-gallery-lightbox figure img{display:block;max-width:100%;max-height:calc(100vh - 120px);width:auto;height:auto;object-fit:contain}
                .object-gallery-lightbox figcaption{color:#fff;text-align:center;padding-top:12px}
                .object-gallery-lightbox-close{position:absolute;right:22px;top:18px;color:#fff;font-size:30px;line-height:1}
                .object-gallery-lightbox-prev,.object-gallery-lightbox-next{color:#fff;flex:0 0 auto}
                @media(max-width:900px){
                    .object-gallery-carousel{gap:10px}
                    .object-gallery-grid{gap:12px}
                    .object-gallery-grid.is-slider-track .object-gallery-item{flex-basis:calc((100% - 24px)/3)}
                    .object-gallery-grid.is-static-row .object-gallery-item{flex:1 1 0}
                }
                @media(max-width:600px){
                    .object-gallery-grid.is-static-row{flex-wrap:wrap}
                    .object-gallery-grid.is-static-row .object-gallery-item{flex:0 0 calc(50% - 6px)}
                    .object-gallery-grid.is-slider-track .object-gallery-item{flex-basis:100%}
                    .object-gallery-prev,.object-gallery-next{flex-basis:34px;width:34px;height:44px;font-size:24px}
                    .object-gallery-lightbox{padding:60px 12px 20px;gap:8px}
                    .object-gallery-lightbox-prev,.object-gallery-lightbox-next{padding:7px 9px}
                    .object-gallery-lightbox figure img{max-height:calc(100vh - 150px)}
                }
            </style>

            <script>
            (function() {
                function initInterworldGallery(section) {
                    if (section.dataset.galleryInitialized === '1') return;
                    section.dataset.galleryInitialized = '1';
                    var dataNode = section.querySelector('.object-gallery-data');
                    if (!dataNode) return;
                    var images;
                    try { images = JSON.parse(dataNode.textContent || '[]'); } catch (e) { return; }
                    if (!images.length) return;

                    var track = section.querySelector('[data-gallery-track]');
                    var carousel = section.querySelector('.object-gallery-carousel');
                    if (track && carousel) {
                        var items = Array.prototype.slice.call(track.querySelectorAll('.object-gallery-item'));
                        var prevButton = carousel.querySelector('.object-gallery-prev');
                        var nextButton = carousel.querySelector('.object-gallery-next');
                        var position = 0;
                        function visibleCount() {
                            if (window.matchMedia('(max-width: 600px)').matches) return 1;
                            if (window.matchMedia('(max-width: 900px)').matches) return 3;
                            return 4;
                        }
                        function updateTrack() {
                            if (!items.length) return;
                            var maxPosition = Math.max(0, items.length - visibleCount());
                            position = Math.max(0, Math.min(position, maxPosition));
                            var itemWidth = items[0].getBoundingClientRect().width;
                            var gap = parseFloat(window.getComputedStyle(track).columnGap || window.getComputedStyle(track).gap) || 0;
                            track.style.transform = 'translate3d(-' + (position * (itemWidth + gap)) + 'px,0,0)';
                            if (prevButton) {
                                prevButton.disabled = position === 0;
                                prevButton.style.opacity = prevButton.disabled ? '.35' : '1';
                            }
                            if (nextButton) {
                                nextButton.disabled = position >= maxPosition;
                                nextButton.style.opacity = nextButton.disabled ? '.35' : '1';
                            }
                        }
                        if (prevButton) prevButton.addEventListener('click', function() { position -= 1; updateTrack(); });
                        if (nextButton) nextButton.addEventListener('click', function() { position += 1; updateTrack(); });
                        window.addEventListener('resize', updateTrack);
                        if ('ResizeObserver' in window) new ResizeObserver(updateTrack).observe(carousel);
                        requestAnimationFrame(updateTrack);
                    }

                    var current = 0;
                    var lightbox = document.querySelector('.object-gallery-lightbox');
                    if (!lightbox) return;
                    var lightboxImg = lightbox.querySelector('img');
                    var lightboxCaption = lightbox.querySelector('figcaption');
                    var lastFocused = null;
                    function openLightbox(index) {
                        current = (index + images.length) % images.length;
                        var image = images[current];
                        lastFocused = document.activeElement;
                        lightboxImg.src = image.full;
                        lightboxImg.alt = image.alt || '';
                        lightboxCaption.textContent = image.caption || '';
                        lightboxCaption.hidden = !image.caption;
                        lightbox.hidden = false;
                        document.body.style.overflow = 'hidden';
                        lightbox.querySelector('.object-gallery-lightbox-close').focus();
                    }
                    function closeLightbox() {
                        lightbox.hidden = true;
                        lightboxImg.src = '';
                        document.body.style.overflow = '';
                        if (lastFocused && lastFocused.focus) lastFocused.focus();
                    }
                    section.querySelectorAll('[data-gallery-index]').forEach(function(button) {
                        button.addEventListener('click', function() {
                            openLightbox(parseInt(button.dataset.galleryIndex || '0', 10));
                        });
                    });
                    lightbox.querySelector('.object-gallery-lightbox-close').addEventListener('click', closeLightbox);
                    lightbox.querySelector('.object-gallery-lightbox-prev').addEventListener('click', function() { openLightbox(current - 1); });
                    lightbox.querySelector('.object-gallery-lightbox-next').addEventListener('click', function() { openLightbox(current + 1); });
                    lightbox.addEventListener('click', function(event) { if (event.target === lightbox) closeLightbox(); });
                    document.addEventListener('keydown', function(event) {
                        if (lightbox.hidden) return;
                        if (event.key === 'Escape') closeLightbox();
                        if (event.key === 'ArrowLeft') openLightbox(current - 1);
                        if (event.key === 'ArrowRight') openLightbox(current + 1);
                    });
                }
                function initAll() { document.querySelectorAll('.object-gallery-section').forEach(initInterworldGallery); }
                if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initAll);
                else initAll();
            })();
            </script>
        <?php endif; ?>

        <!-- 7. Вопросы и ответы объекта -->
        <?php if (!empty($object_faq)) : ?>
            <section class="object-section object-faq-section" id="faq">
                <div class="object-section-heading">
                    <div class="eyebrow">Коротко о главном</div>
                    <h2>Вопросы и ответы</h2>
                </div>
                <div class="object-faq-list">
                    <?php foreach ($object_faq as $index => $faq_item) : ?>
                        <details class="object-faq-item" <?php echo $index === 0 ? '' : ''; ?>>
                            <summary><?php echo esc_html($faq_item['question']); ?></summary>
                            <div class="object-faq-answer">
                                <?php echo wpautop(esc_html($faq_item['answer'])); ?>
                            </div>
                        </details>
                    <?php endforeach; ?>
                </div>
            </section>
            <style>
                .object-faq-list{border-top:1px solid rgba(25,48,62,.22)}
                .object-faq-item{border-bottom:1px solid rgba(25,48,62,.22)}
                .object-faq-item summary{position:relative;list-style:none;cursor:pointer;padding:20px 48px 20px 0;color:var(--interworld-ink,#19303e);font-size:1.08rem;line-height:1.5;font-weight:500}
                .object-faq-item summary::-webkit-details-marker{display:none}
                .object-faq-item summary::after{content:'+';position:absolute;right:8px;top:50%;transform:translateY(-50%);font-size:26px;font-weight:300;line-height:1}
                .object-faq-item[open] summary::after{content:'−'}
                .object-faq-answer{padding:0 48px 22px 0;color:var(--interworld-muted,#697984);font-size:1rem;line-height:1.75}
                .object-faq-answer p{margin:0 0 12px}
                .object-faq-answer p:last-child{margin-bottom:0}
                @media(max-width:600px){.object-faq-item summary{padding:16px 38px 16px 0;font-size:1rem}.object-faq-answer{padding:0 0 18px;font-size:.96rem}}
            </style>
        <?php endif; ?>

        <!-- 8. Цитаты объекта -->
        <?php if (!empty($object_quotes)) : ?>
            <section class="object-section object-quotes-section" id="quotes">
                <div class="object-section-heading">
                    <div class="eyebrow">Из источников</div>
                    <h2>Цитаты</h2>
                </div>

                <div class="object-quotes-carousel<?php echo $object_quotes_count > 2 ? ' has-slider' : ' is-grid'; ?>" data-quotes-count="<?php echo esc_attr($object_quotes_count); ?>">
                    <?php if ($object_quotes_count > 2) : ?>
                        <button type="button" class="object-quotes-prev" aria-label="Предыдущие цитаты" disabled>‹</button>
                    <?php endif; ?>
                    <div class="object-quotes-viewport">
                        <div class="object-quotes-track<?php echo $object_quotes_count > 2 ? ' is-slider-track' : ' is-static-grid'; ?>">
                            <?php foreach ($object_quotes as $quote_item) : ?>
                                <blockquote class="object-quote-card">
                                    <div class="object-quote-text"><?php echo wpautop(esc_html($quote_item['quote'])); ?></div>
                                    <?php if ($quote_item['source'] !== '') : ?>
                                        <div class="object-quote-source">
                                            <?php if ($quote_item['url'] !== '') : ?>
                                                <a href="<?php echo esc_url($quote_item['url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($quote_item['source']); ?></a>
                                            <?php else : ?>
                                                <span><?php echo esc_html($quote_item['source']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </blockquote>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php if ($object_quotes_count > 2) : ?>
                        <button type="button" class="object-quotes-next" aria-label="Следующие цитаты">›</button>
                    <?php endif; ?>
                </div>
            </section>

            <style>
                .object-quotes-carousel{display:flex;align-items:stretch;gap:18px;width:100%}
                .object-quotes-viewport{flex:1;min-width:0;overflow:hidden}
                .object-quotes-track{display:flex;align-items:stretch;gap:18px;width:100%}
                .object-quotes-track.is-static-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr))}
                .object-quotes-track.is-slider-track{transition:transform .35s ease;will-change:transform}
                .object-quote-card{box-sizing:border-box;min-width:0;margin:0;padding:24px;border:1px solid rgba(25,48,62,.18);background:transparent}
                .object-quotes-track.is-slider-track .object-quote-card{flex:0 0 calc((100% - 18px)/2)}
                .object-quote-text{font-size:1.04rem;line-height:1.75}
                .object-quote-text p{margin:0 0 12px}
                .object-quote-text p:last-child{margin-bottom:0}
                .object-quote-source{margin-top:18px;font-size:.92rem;line-height:1.5;color:var(--interworld-muted,#697984)}
                .object-quote-source a{text-decoration:underline;text-underline-offset:3px}
                .object-quotes-prev,.object-quotes-next{display:flex;align-items:center;justify-content:center;align-self:center;flex:0 0 46px;width:46px;height:56px;border:1px solid currentColor;background:transparent;color:inherit;cursor:pointer;padding:0;font-size:30px;line-height:1}
                .object-quotes-prev:disabled,.object-quotes-next:disabled{cursor:default;opacity:.35}
                @media(max-width:600px){
                    .object-quotes-carousel{gap:10px}
                    .object-quotes-track{gap:12px}
                    .object-quotes-track.is-static-grid{grid-template-columns:minmax(0,1fr)}
                    .object-quotes-track.is-slider-track .object-quote-card{flex-basis:100%}
                    .object-quote-card{padding:18px}
                    .object-quotes-prev,.object-quotes-next{flex-basis:32px;width:32px;height:44px;font-size:24px}
                }
            </style>

            <?php if ($object_quotes_count > 2) : ?>
                <script>
                (function() {
                    function initQuotesCarousel(carousel) {
                        if (carousel.dataset.initialized === '1') return;
                        carousel.dataset.initialized = '1';
                        var track = carousel.querySelector('.object-quotes-track');
                        var cards = track ? Array.prototype.slice.call(track.querySelectorAll('.object-quote-card')) : [];
                        var prev = carousel.querySelector('.object-quotes-prev');
                        var next = carousel.querySelector('.object-quotes-next');
                        if (!track || !cards.length) return;
                        var position = 0;
                        function visibleCount() {
                            return window.matchMedia('(max-width: 600px)').matches ? 1 : 2;
                        }
                        function update() {
                            var count = visibleCount();
                            var maxPosition = Math.max(0, cards.length - count);
                            position = Math.max(0, Math.min(position, maxPosition));
                            var width = cards[0].getBoundingClientRect().width;
                            var gap = parseFloat(window.getComputedStyle(track).gap) || 0;
                            track.style.transform = 'translate3d(-' + (position * (width + gap)) + 'px,0,0)';
                            if (prev) prev.disabled = position === 0;
                            if (next) next.disabled = position >= maxPosition;
                        }
                        if (prev) prev.addEventListener('click', function() { position = Math.max(0, position - visibleCount()); update(); });
                        if (next) next.addEventListener('click', function() { position = Math.min(cards.length - visibleCount(), position + visibleCount()); update(); });
                        window.addEventListener('resize', update);
                        if ('ResizeObserver' in window) new ResizeObserver(update).observe(carousel);
                        requestAnimationFrame(update);
                    }
                    function initAll() {
                        document.querySelectorAll('.object-quotes-carousel.has-slider').forEach(initQuotesCarousel);
                    }
                    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initAll);
                    else initAll();
                })();
                </script>
            <?php endif; ?>
        <?php endif; ?>

        <!-- 9. Личности и контекст: карточки из связей объектов -->
            <section class="object-section object-context-section" id="context">
                <div class="object-section-heading">
                    <div class="eyebrow">Люди и идеи</div>
                    <h2>Личности и контекст</h2>
                    <p class="object-section-intro">Личности, исследователи и традиции, связанные с этим объектом.</p>
                </div>
                <?php if ($context_total > 0) : ?>
                <div class="object-context-tabs" role="tablist" aria-label="Личности и контекст">
                    <?php $context_tab_index = 0; foreach ($context_labels as $context_key => $context_label) : ?>
                        <?php if (empty($context_by_section[$context_key])) continue; ?>
                        <button type="button" class="object-context-tab" role="tab"
                            id="context-tab-<?php echo esc_attr($context_key); ?>"
                            aria-controls="context-panel-<?php echo esc_attr($context_key); ?>"
                            aria-selected="<?php echo $context_tab_index === 0 ? 'true' : 'false'; ?>"
                            tabindex="<?php echo $context_tab_index === 0 ? '0' : '-1'; ?>">
                            <?php echo esc_html($context_label); ?>
                            <span><?php echo count($context_by_section[$context_key]); ?></span>
                        </button>
                        <?php $context_tab_index++; ?>
                    <?php endforeach; ?>
                </div>

                <?php $context_panel_index = 0; foreach ($context_labels as $context_key => $context_label) : ?>
                    <?php if (empty($context_by_section[$context_key])) continue; ?>
                    <div class="object-context-panel" id="context-panel-<?php echo esc_attr($context_key); ?>"
                         role="tabpanel" aria-labelledby="context-tab-<?php echo esc_attr($context_key); ?>"
                         <?php echo $context_panel_index > 0 ? 'hidden' : ''; ?>>
                        <div class="object-context-cards">
                            <?php foreach ($context_by_section[$context_key] as $context_item) :
                                $context_target_id = absint($context_item['target_id']);
                                $context_title = get_the_title($context_target_id);
                                $context_url = get_permalink($context_target_id);
                                $context_status = get_post_status($context_target_id);
                                $context_is_published = ($context_status === 'publish');
                                $context_excerpt = get_post_field('post_excerpt', $context_target_id);
                                if (!$context_excerpt) $context_excerpt = get_post_meta($context_target_id, '_interworld_short_definition', true);
                                if (!$context_excerpt) $context_excerpt = $context_item['explanation'] ?? '';

                                $context_placeholders = array(
                                    'arkhonty' => 'https://geografiya-mezhmirya.ru/wp-content/uploads/2026/10/Archons-interworld-1.webp',
                                    'bogi' => 'https://geografiya-mezhmirya.ru/wp-content/uploads/2026/10/Deities-interworld-1.webp',
                                    'goroda-mezhmirya' => 'https://geografiya-mezhmirya.ru/wp-content/uploads/2026/10/Cities-interworld-1.webp',
                                    'knigi' => 'https://geografiya-mezhmirya.ru/wp-content/uploads/2026/10/Book-interworld-1.webp',
                                    'person' => 'https://geografiya-mezhmirya.ru/wp-content/uploads/2026/10/Person-interworld-1.webp',
                                    'posmertie' => 'https://geografiya-mezhmirya.ru/wp-content/uploads/2026/10/Duat-interworld-1.webp',
                                    'sushchestva-promezhutka' => 'https://geografiya-mezhmirya.ru/wp-content/uploads/2026/10/Creatures-interworld-1.webp',
                                    'simvoli-i-atributi' => 'https://geografiya-mezhmirya.ru/wp-content/uploads/2026/10/Symbols-interworld-1.webp',
                                    'tradicii' => 'https://geografiya-mezhmirya.ru/wp-content/uploads/2026/10/Traditions-interworld-1.webp',
                                    'eony' => 'https://geografiya-mezhmirya.ru/wp-content/uploads/2026/10/Eony-interworld-1.webp',
                                    'efiry' => 'https://geografiya-mezhmirya.ru/wp-content/uploads/2026/10/Efiri-interworld-1.webp',
                                    'prostranstva-mezhmirya' => 'https://geografiya-mezhmirya.ru/wp-content/uploads/2026/10/Spaces-interworld-1.webp',
                                );
                                $context_terms = get_the_terms($context_target_id, 'object_type');
                                $context_type_slug = (!empty($context_terms) && !is_wp_error($context_terms)) ? $context_terms[0]->slug : '';
                                $context_placeholder = $context_placeholders[$context_type_slug] ?? 'https://geografiya-mezhmirya.ru/wp-content/uploads/2026/10/other-interworld-1.webp';

                                if ($context_is_published && has_post_thumbnail($context_target_id)) {
                                    $context_image = get_the_post_thumbnail($context_target_id, 'thumbnail', array('alt' => $context_title, 'loading' => 'lazy'));
                                } else {
                                    $context_image = '<img src="' . esc_url($context_placeholder) . '" alt="' . esc_attr($context_title) . '" loading="lazy" />';
                                }
                                $context_tag = $context_is_published ? 'a' : 'div';
                                $context_attributes = $context_is_published
                                    ? ' class="object-context-card" href="' . esc_url($context_url) . '"'
                                    : ' class="object-context-card object-context-card-draft" aria-label="' . esc_attr($context_title . ' — материал готовится') . '"';
                            ?>
                                <?php echo '<' . $context_tag . $context_attributes . '>'; ?>
                                    <span class="object-context-card-image"><?php echo wp_kses_post($context_image); ?></span>
                                    <span class="object-context-card-body">
                                        <span class="object-context-card-title"><?php echo esc_html($context_title); ?></span>
                                        <?php if (trim((string) $context_excerpt) !== '') : ?>
                                            <span class="object-context-card-description"><?php echo esc_html(wp_trim_words(wp_strip_all_tags($context_excerpt), 24)); ?></span>
                                        <?php endif; ?>
                                        <?php if (!$context_is_published) : ?>
                                            <span class="object-context-card-note">Материал готовится</span>
                                        <?php endif; ?>
                                    </span>
                                    <?php if ($context_is_published) : ?><span class="object-context-card-arrow" aria-hidden="true">›</span><?php endif; ?>
                                <?php echo '</' . $context_tag . '>'; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php $context_panel_index++; ?>
                <?php endforeach; ?>
                <?php else : ?>
                    <p class="object-context-empty">Связанные личности, исследователи и традиции пока не добавлены.</p>
                <?php endif; ?>
            </section>

        <!-- 10. Материалы для глубокого изучения: демонстрационный макет -->
        <section class="object-section object-deep-study-section" id="deep-study">
            <div class="object-section-heading">
                <div class="eyebrow">Продолжить исследование</div>
                <h2>Материалы для глубокого изучения</h2>
                <p class="object-section-intro">Подборка материалов, которая помогает перейти от краткого знакомства к самостоятельному изучению темы.</p>
            </div>
            <?php if (array_sum(array_map('count', $deep_study_items)) > 0) : ?>
                <?php $study_labels = array('articles' => 'Статьи', 'video' => 'Видео и лекции', 'other' => 'Другое'); foreach ($study_labels as $study_key => $study_label) : if (empty($deep_study_items[$study_key])) continue; ?>
                <div class="object-study-group"><h3><?php echo esc_html($study_label); ?></h3><div class="object-context-cards">
                    <?php foreach ($deep_study_items[$study_key] as $study_item) :
                        $study_id = $study_item['target_id']; $study_title = get_the_title($study_id); $study_status = get_post_status($study_id); $study_published = ($study_status === 'publish');
                        $study_excerpt = get_post_field('post_excerpt', $study_id); if (!$study_excerpt) $study_excerpt = get_post_meta($study_id, '_interworld_short_definition', true); if (!$study_excerpt) $study_excerpt = $study_item['explanation'];
                        $study_url = get_permalink($study_id); $study_image = ($study_published && has_post_thumbnail($study_id)) ? get_the_post_thumbnail($study_id, 'thumbnail', array('alt' => $study_title, 'loading' => 'lazy')) : '';
                        if (!$study_image) { $study_logo = get_theme_mod('custom_logo'); $study_image = $study_logo ? wp_get_attachment_image($study_logo, 'thumbnail', false, array('alt' => $study_title)) : '<span aria-hidden="true">✦</span>'; }
                        $study_tag = $study_published ? 'a' : 'div'; $study_attrs = $study_published ? ' class="object-context-card" href="' . esc_url($study_url) . '"' : ' class="object-context-card object-context-card-draft"';
                    ?>
                        <?php echo '<' . $study_tag . $study_attrs . '>'; ?><span class="object-context-card-image"><?php echo wp_kses_post($study_image); ?></span><span class="object-context-card-body"><span class="object-context-card-title"><?php echo esc_html($study_title); ?></span><?php if ($study_excerpt) : ?><span class="object-context-card-description"><?php echo esc_html(wp_trim_words(wp_strip_all_tags($study_excerpt), 24)); ?></span><?php endif; ?><?php if (!$study_published) : ?><span class="object-context-card-note">Материал готовится</span><?php endif; ?></span><?php echo '</' . $study_tag . '>'; ?>
                    <?php endforeach; ?>
                </div></div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="object-editorial-grid"><article class="object-editorial-card"><span class="object-editorial-type">Статьи</span><h3>Исследования и разборы</h3><p>Статьи энциклопедии, аналитические материалы и тематические публикации по объекту.</p><span class="object-placeholder-note">Здесь появятся ссылки на статьи</span></article><article class="object-editorial-card"><span class="object-editorial-type">Видео и лекции</span><h3>Посмотреть и послушать</h3><p>Лекции, видео, аудиоматериалы и выпуски, раскрывающие отдельные аспекты темы.</p><span class="object-placeholder-note">Здесь появятся видео и аудиоматериалы</span></article><article class="object-editorial-card"><span class="object-editorial-type">Подборки</span><h3>Тематические коллекции</h3><p>Подборки по смежным понятиям, направлениям и вопросам, которые стоит изучить дальше.</p><span class="object-placeholder-note">Здесь появятся тематические подборки</span></article></div>
            <?php endif; ?>
        </section>

        <!-- 11. Источники и литература: разделы разведены по назначению -->
        <section class="object-section object-sources-section" id="sources">
            <div class="object-section-heading">
                <div class="eyebrow">Основа материала</div>
                <h2>Источники и литература</h2>
                <p class="object-section-intro">Раздел для прозрачности происхождения сведений и дальнейшего чтения. Первичные источники и рекомендованная литература будут оформляться отдельно.</p>
            </div>
            <div class="object-source-columns">
                <div class="object-source-column">
                    <div class="object-source-column-heading"><span aria-hidden="true">▤</span><h3>Источники</h3></div>
                    <p>Первоисточники, тексты, документы и публикации, на которые опирается описание объекта.</p>
                    <?php if (!empty($source_items['source'])) : ?>
                        <ul class="object-bibliography-list">
                            <?php foreach ($source_items['source'] as $source_item) :
                                $sid = $source_item['target_id'];
                                $stitle = get_the_title($sid);
                                $spublished = get_post_status($sid) === 'publish';
                                $surl = get_post_meta($sid, '_interworld_source_url', true);
                                $bibliography_text = get_post_meta($sid, '_interworld_bibliography_text', true);
                                $source_display_text = $bibliography_text ? $bibliography_text : $stitle;
                                $sdescription = get_post_field('post_excerpt', $sid);
                                if (!$sdescription) $sdescription = get_post_meta($sid, '_interworld_short_definition', true);
                                if (!$sdescription) $sdescription = $source_item['explanation'];
                            ?>
                                <li class="object-bibliography-item">
                                    <?php if ($surl) : ?><a href="<?php echo esc_url($surl); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($source_display_text); ?></a>
                                    <?php else : ?><span><?php echo esc_html($source_display_text); ?></span><?php endif; ?>
                                    <?php if ($sdescription) : ?><span class="object-bibliography-description"><?php echo esc_html(wp_trim_words(wp_strip_all_tags($sdescription), 40)); ?></span><?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else : ?><div class="object-source-placeholder">Пока источники не добавлены</div><?php endif; ?>
                </div>
                <div class="object-source-column">
                    <div class="object-source-column-heading"><span aria-hidden="true">▥</span><h3>Книги и литература</h3></div>
                    <p>Книги и исследования для знакомства с темой и её углублённого изучения.</p>
                    <?php if (!empty($source_items['literature'])) : ?>
                        <div class="object-literature-list">
                            <?php foreach ($source_items['literature'] as $source_item) :
                                $sid = $source_item['target_id'];
                                $stitle = get_the_title($sid);
                                $spublished = get_post_status($sid) === 'publish';
                                $surl = get_permalink($sid);
                                $sdescription = get_post_field('post_excerpt', $sid);
                                if (!$sdescription) $sdescription = get_post_meta($sid, '_interworld_short_definition', true);
                                if (!$sdescription) $sdescription = wp_strip_all_tags(get_post_field('post_content', $sid));
                                if (!$sdescription) $sdescription = $source_item['explanation'];
                                $book_author = get_post_meta($sid, '_interworld_book_author', true);
                                if (!$book_author) $book_author = get_post_meta($sid, 'interworld_book_author', true);
                                if (!$book_author) $book_author = get_post_meta($sid, 'book_author', true);
                                if (!$book_author) $book_author = get_post_meta($sid, 'author', true);
                                $book_placeholder = 'https://geografiya-mezhmirya.ru/wp-content/uploads/2026/10/Book-interworld-1.webp';
                                if ($spublished && has_post_thumbnail($sid)) {
                                    $book_image = get_the_post_thumbnail($sid, 'medium', array('alt' => $stitle, 'loading' => 'lazy'));
                                } else {
                                    $book_image = '<img src="' . esc_url($book_placeholder) . '" alt="' . esc_attr($stitle . ' — обложка книги') . '" loading="lazy" />';
                                }
                                $book_tag = $spublished ? 'a' : 'div';
                                $book_attributes = $spublished ? ' class="object-literature-card" href="' . esc_url($surl) . '"' : ' class="object-literature-card object-context-card-draft" aria-label="' . esc_attr($stitle . ' — материал готовится') . '"';
                            ?>
                                <?php echo '<' . $book_tag . $book_attributes . '>'; ?>
                                    <span class="object-literature-cover"><?php echo wp_kses_post($book_image); ?></span>
                                    <span class="object-literature-body">
                                        <span class="object-literature-title"><?php echo esc_html($stitle); ?></span>
                                        <?php if ($book_author) : ?><span class="object-literature-author"><?php echo esc_html($book_author); ?></span><?php endif; ?>
                                        <?php if ($sdescription) : ?><span class="object-literature-description"><?php echo esc_html(wp_trim_words(wp_strip_all_tags($sdescription), 45)); ?></span><?php endif; ?>
                                        <?php if (!$spublished) : ?><span class="object-context-card-note">Материал готовится</span><?php endif; ?>
                                    </span>
                                    <?php if ($spublished) : ?><span class="object-context-card-arrow" aria-hidden="true">›</span><?php endif; ?>
                                <?php echo '</' . $book_tag . '>'; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?><div class="object-source-placeholder">Пока книги не добавлены</div><?php endif; ?>
                </div>
            </div>
        </section>

        <style>
            .object-section-intro{max-width:760px;margin:10px 0 0;color:var(--interworld-muted,#697984);line-height:1.7}
            .object-context-tabs{display:flex;flex-wrap:wrap;gap:26px;margin:26px 0 22px;border-bottom:1px solid rgba(25,48,62,.2)}
            .object-context-cards{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
            .object-context-empty{padding:18px 20px;border:1px solid rgba(25,48,62,.17);color:var(--interworld-muted,#697984);line-height:1.6}
            .object-context-card{display:flex;align-items:center;gap:18px;min-width:0;padding:16px;border:1px solid rgba(25,48,62,.17);color:inherit;text-decoration:none;background:rgba(255,255,255,.2)}
            .object-context-card:hover{border-color:#b88a4a}
            .object-context-card-draft{cursor:default;opacity:.88}
            .object-context-card-draft:hover{border-color:rgba(25,48,62,.17)}
            .object-context-card-image{display:flex;align-items:center;justify-content:center;flex:0 0 86px;width:86px;height:86px;background:#e9e6de;overflow:hidden}
            .object-context-card-image img{display:block;width:100%;height:100%;object-fit:cover}
            .object-context-card-body{display:flex;flex-direction:column;gap:5px;min-width:0}
            .object-context-card-title{font-size:20px;line-height:1.3}
            .object-context-card-description,.object-context-card-note{font-size:14px;line-height:1.5;color:var(--interworld-muted,#697984)}
            .object-context-card-arrow{margin-left:auto;color:#b88a4a;font-size:24px;flex:0 0 auto}
            .object-context-tab{appearance:none;border:0;border-bottom:2px solid transparent;background:transparent;color:inherit;padding:12px 0;margin-bottom:-1px;font:inherit;cursor:pointer;opacity:.7}
            .object-context-tab[aria-selected="true"]{border-bottom-color:#b88a4a;opacity:1}
            .object-placeholder-card{display:flex;gap:22px;align-items:flex-start;padding:24px;border:1px solid rgba(25,48,62,.17);min-height:150px}
            .object-placeholder-symbol{display:flex;align-items:center;justify-content:center;flex:0 0 64px;width:64px;height:64px;border:1px solid rgba(184,138,74,.45);color:#b88a4a;font-size:30px}
            .object-placeholder-card h3,.object-editorial-card h3{margin:0 0 10px;font-size:1.2rem;line-height:1.4}
            .object-placeholder-card p,.object-editorial-card p,.object-source-column>p{margin:0 0 14px;line-height:1.7;color:var(--interworld-muted,#697984)}
            .object-placeholder-note{display:inline-block;font-size:.82rem;line-height:1.5;color:var(--interworld-muted,#697984);opacity:.85}
            .object-editorial-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-top:26px}
            .object-editorial-card{padding:22px;border:1px solid rgba(25,48,62,.17);min-width:0}
            .object-editorial-type{display:block;margin-bottom:14px;font-size:.78rem;letter-spacing:.08em;text-transform:uppercase;color:#a47a3d}
            .object-study-group{margin-top:24px}.object-study-group>h3{margin:0 0 14px;font-size:1.2rem}.object-source-columns{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;margin-top:26px}
            .object-source-column{padding:24px;border:1px solid rgba(25,48,62,.17);min-width:0}
            .object-source-column-heading{display:flex;align-items:center;gap:12px;margin-bottom:14px}
            .object-source-column-heading>span{font-size:24px;color:#b88a4a}
            .object-source-column-heading h3{margin:0;font-size:1.25rem}
            .object-source-placeholder{padding:14px 16px;background:rgba(25,48,62,.045);color:var(--interworld-muted,#697984);font-size:.92rem}
            .object-bibliography-list{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:12px}
            .object-bibliography-item{display:flex;flex-direction:column;gap:5px;padding:0 0 12px;border-bottom:1px solid rgba(25,48,62,.12);line-height:1.6}
            .object-bibliography-item a{text-decoration:underline;text-underline-offset:3px}
            .object-bibliography-description{font-size:.92rem;color:var(--interworld-muted,#697984)}
            .object-literature-list{display:flex;flex-direction:column;gap:14px}
            .object-literature-card{display:flex;align-items:flex-start;gap:16px;min-width:0;padding:16px;border:1px solid rgba(25,48,62,.17);color:inherit;text-decoration:none;background:rgba(255,255,255,.2)}
            .object-literature-card:hover{border-color:#b88a4a}
            .object-literature-cover{display:flex;align-items:center;justify-content:center;flex:0 0 104px;width:104px;height:148px;background:#e9e6de;overflow:hidden}
            .object-literature-cover img{display:block;width:100%;height:100%;object-fit:cover}
            .object-literature-body{display:flex;flex-direction:column;gap:6px;min-width:0;flex:1}
            .object-literature-title{font-size:19px;line-height:1.35}
            .object-literature-author{font-size:.9rem;color:var(--interworld-muted,#697984)}
            .object-literature-description{font-size:.94rem;line-height:1.6;color:var(--interworld-muted,#697984)}
            @media(max-width:700px){.object-editorial-grid,.object-source-columns,.object-context-cards{grid-template-columns:1fr}.object-context-tabs{gap:18px}.object-context-card{gap:12px;padding:12px}.object-context-card-image{width:68px;height:68px;flex-basis:68px}.object-context-card-title{font-size:18px}.object-literature-card{gap:12px;padding:12px}.object-literature-cover{width:82px;height:118px;flex-basis:82px}.object-literature-title{font-size:17px}.object-placeholder-card{padding:18px;gap:14px}.object-placeholder-symbol{flex-basis:48px;width:48px;height:48px;font-size:24px}}
        </style>
        <script>
        (function(){
            document.querySelectorAll('.object-context-section').forEach(function(section){
                if(section.dataset.tabsReady==='1') return;
                section.dataset.tabsReady='1';
                var tabs=Array.from(section.querySelectorAll('[role="tab"]'));
                tabs.forEach(function(tab){
                    tab.addEventListener('click',function(){
                        tabs.forEach(function(other){
                            var active=other===tab;
                            other.setAttribute('aria-selected',active?'true':'false');
                            other.tabIndex=active?0:-1;
                            var panel=section.querySelector('#'+other.getAttribute('aria-controls'));
                            if(panel) panel.hidden=!active;
                        });
                    });
                });
            });
        })();
        </script>

      

    </article>
<?php endwhile; ?>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const nav = document.querySelector('.object-anchor-nav');
    if (!nav) return;

    const navLinks = Array.from(nav.querySelectorAll('a[href^="#"]'));
    const sections = navLinks
        .map(function (link) {
            const target = document.querySelector(link.getAttribute('href'));
            return target ? { link: link, section: target } : null;
        })
        .filter(Boolean);

    function updateActiveAnchor() {
        let current = sections[0];
        const scrollPosition = window.scrollY + nav.offsetHeight + 120;

        sections.forEach(function (item) {
            if (item.section.getBoundingClientRect().top + window.scrollY <= scrollPosition) {
                current = item;
            }
        });

        navLinks.forEach(function (link) {
            link.classList.remove('is-active');
        });

        if (current) current.link.classList.add('is-active');
    }

    navLinks.forEach(function (link) {
        link.addEventListener('click', function (event) {
            const target = document.querySelector(this.getAttribute('href'));
            if (!target) return;

            event.preventDefault();
            const offset = nav.offsetHeight + 20;
            const top = target.getBoundingClientRect().top + window.scrollY - offset;

            window.scrollTo({ top: top, behavior: 'smooth' });
        });
    });

    window.addEventListener('scroll', updateActiveAnchor, { passive: true });
    window.addEventListener('resize', updateActiveAnchor);
    updateActiveAnchor();
});
</script>

<?php get_footer(); ?>
