<?php
/**
 * Universal category template for the INTERWORLD encyclopedia.
 * Prototype: category "Эфиры" with test data.
 * Later the test arrays below will be replaced by WordPress/ACF data.
 */
if (!defined('ABSPATH')) exit;

$term = get_queried_object();
$term_name = ($term && !empty($term->name)) ? $term->name : 'Эфиры';

/* ---------------------------------------------------------
 * Prototype data — visual only. Do not treat as final content.
 * Source structure: География Межмирья. Том 1.
 * --------------------------------------------------------- */
$category = array(
    'eyebrow' => 'ЭНЦИКЛОПЕДИЯ МЕЖМИРЬЯ',
    'title' => 'Эфиры Межмирья',
    'subtitle' => 'География Межмирья · Том 1',
    'description' => 'Эфиры — пространства Межмирья, порожденные эоническими силами. Они формируют структуру Промежутка и служат основой для описания его пространств, переходов и состояний.',
    'image' => 'https://geografiya-mezhmirya.ru/wp-content/uploads/2026/09/karta-meszmiria.webp',
    'count' => 30,
    'groups_count' => 3,
);

$groups = array(
    array(
        'slug' => 'archontic',
        'title' => 'Архонтные Эфиры',
        'count' => 12,
        'note' => '12 Эфиров, образующих первый уровень системы.',
        'items' => array(
            array('number'=>'30','code'=>'TEX','title'=>'Перекрёсток миров','description'=>'Место перехода между различными реальностями.'),
            array('number'=>'29','code'=>'RII','title'=>'Астральные пространства','description'=>'Пространства, связанные с тонкими уровнями материи.'),
            array('number'=>'28','code'=>'BAG','title'=>'Острова блаженных','description'=>'Пространства гармонии и особых состояний сознания.'),
            array('number'=>'27','code'=>'ZAA','title'=>'Эгрегоры','description'=>'Уровень формирования и взаимодействия коллективных структур.'),
            array('number'=>'26','code'=>'DES','title'=>'Мир мысли','description'=>'Пространство, связанное с мыслью и её формами.'),
            array('number'=>'25','code'=>'VTI','title'=>'Высший разум','description'=>'Эфир, связанный с высшими уровнями разума.'),
            array('number'=>'24','code'=>'NLA','title'=>'Пространство высших коллективностей','description'=>'Крупные коллективные структуры сознания.'),
            array('number'=>'23','code'=>'TOR','title'=>'Река жизни','description'=>'Пространство движения и переходов.'),
        ),
    ),
    array(
        'slug' => 'proto',
        'title' => 'Эфиры «Протосефир»',
        'count' => 10,
        'note' => '10 Эфиров следующего уровня системы.',
        'items' => array(
            array('number'=>'18','code'=>'ZEN','title'=>'Возможность проявления','description'=>'Потенциал, из которого начинается проявление.'),
            array('number'=>'17','code'=>'TAN','title'=>'Гармония формы','description'=>'Пространство формирования и гармонизации формы.'),
            array('number'=>'16','code'=>'LEA','title'=>'Река смыслов','description'=>'Поток смыслов и их взаимосвязей.'),
            array('number'=>'15','code'=>'0X0','title'=>'Танец Мистерий','description'=>'Название и обозначение требуют проверки по оригиналу.'),
        ),
    ),
    array(
        'slug' => 'divine',
        'title' => 'Божественные Эфиры',
        'count' => 8,
        'note' => '8 Эфиров высшего уровня системы.',
        'items' => array(
            array('number'=>'8','code'=>'ZID','title'=>'Логосы в ожидании','description'=>'Эфир божественных принципов и ожидания проявления.'),
            array('number'=>'7','code'=>'DEO','title'=>'Вселенский Человек','description'=>'Пространство, связанное с образом Вселенского Человека.'),
            array('number'=>'6','code'=>'MAZ','title'=>'Жизнь безначальная','description'=>'Эфир, связанный с безначальным принципом жизни.'),
            array('number'=>'5','code'=>'LIT','title'=>'Иерархия смыслов','description'=>'Уровень иерархии и смысловых взаимосвязей.'),
        ),
    ),
);

$card_image = $category['image'];
?>

<?php get_header(); ?>



<main class="interworld-category-page">

    <section class="category-hero" id="definition">
        <img class="category-hero-image" src="<?php echo esc_url($category['image']); ?>" alt="Карта Межмирья — Эфиры" loading="eager">
        <div class="category-hero-content">
            <div class="category-breadcrumbs">
                <a href="<?php echo esc_url(home_url('/')); ?>">География Межмирья</a>
                <span> · </span>
                <span><?php echo esc_html($term_name); ?></span>
            </div>
            <div class="category-eyebrow"><?php echo esc_html($category['eyebrow']); ?></div>
            <h1><?php echo esc_html($category['title']); ?></h1>
            <div class="category-subtitle"><?php echo esc_html($category['subtitle']); ?></div>
            <p class="category-hero-description"><?php echo esc_html($category['description']); ?></p>
            <div class="category-stats">
                <div class="category-stat"><strong><?php echo esc_html($category['count']); ?></strong><span>объектов</span></div>
                <div class="category-stat"><strong><?php echo esc_html($category['groups_count']); ?></strong><span>группы</span></div>
                <div class="category-stat"><strong>1</strong><span>том</span></div>
            </div>
        </div>
    </section>

    <nav class="category-anchor-nav" aria-label="Навигация по странице">
        <div class="category-inner" style="display:flex;min-width:max-content;padding-top:0;padding-bottom:0">
            <a href="#definition">Определение</a>
            <a href="#system">Система</a>
            <a href="#catalog">Эфиры</a>
            <a href="#groups">Группы</a>
            <a href="#faq">Вопросы и ответы</a>
            <a href="#sources">Источники</a>
            <a href="#books">Книги</a>
        </div>
    </nav>

    <section class="category-section" id="about-category">
        <div class="category-inner">
            <div class="category-intro-grid">
                <div>
                    <div class="section-eyebrow">О КАТЕГОРИИ</div>
                    <h2 class="category-intro-title">Что такое<br>Эфиры</h2>
                </div>
                <div class="category-intro-copy">
                    <p>Первый том «Географии Межмирья» посвящён рассмотрению общих принципов организации гиперпространств Межмирья. В работе сопоставляются системы эонов Валентинианского гнозиса и эфиров «Енохианской» системы.</p>
                    <p>Эта страница станет навигационным слоем между общим описанием системы и отдельными карточками Эфиров.</p>
                    <a class="text-link" href="#system">Перейти к системе Эфиров</a>
                </div>
            </div>
        </div>
    </section>

    <section class="category-section system-section" id="system">
        <div class="category-inner">
            <div class="section-heading">
                <div class="section-eyebrow">СТРУКТУРА КАТЕГОРИИ</div>
                <h2>Система Эфиров</h2>
                <p class="category-section-intro">Для человеческого уровня сознания в материалах проекта описываются 30 Эфиров, разделённых на три большие группы.</p>
            </div>
            <div class="system-grid">
                <article class="system-card"><div class="system-orb"><strong>12</strong></div><h3>Архонтных Эфиров</h3><p>30 — 19</p></article>
                <article class="system-card"><div class="system-orb"><strong>10</strong></div><h3>Протосефир</h3><p>18 — 9</p></article>
                <article class="system-card"><div class="system-orb"><strong>8</strong></div><h3>Божественных Эфиров</h3><p>8 — 1</p></article>
            </div>
        </div>
    </section>

    <section class="category-section catalog-section" id="catalog">
        <div class="category-inner">
            <div class="section-heading">
                <div class="section-eyebrow">КАТАЛОГ</div>
                <h2>Эфиры Межмирья</h2>
                <p class="category-section-intro">Выберите группу или найдите нужный Эфир. Карточки будут вести на отдельные страницы объектов.</p>
            </div>

            <div class="catalog-toolbar" id="groups">
                <div class="catalog-tabs">
                    <button class="catalog-tab is-active" type="button">Все Эфиры (30)</button>
                    <?php foreach ($groups as $group) : ?>
                        <button class="catalog-tab" type="button"><?php echo esc_html($group['title']); ?> (<?php echo esc_html($group['count']); ?>)</button>
                    <?php endforeach; ?>
                </div>
                <div class="catalog-tools">
                    <input class="catalog-search" type="search" placeholder="Поиск среди Эфиров…" aria-label="Поиск среди Эфиров">
                    <select class="catalog-select" aria-label="Сортировка">
                        <option>По номеру ↓</option>
                        <option>По номеру ↑</option>
                        <option>По названию</option>
                    </select>
                </div>
            </div>

            <?php foreach ($groups as $group_index => $group) : ?>
                <section class="catalog-group" aria-labelledby="group-<?php echo esc_attr($group['slug']); ?>">
                    <div class="catalog-group-head">
                        <div>
                            <h3 id="group-<?php echo esc_attr($group['slug']); ?>"><?php echo esc_html($group['title']); ?></h3>
                            <p><?php echo esc_html($group['note']); ?></p>
                        </div>
                        <a class="catalog-more-link" href="#">Показать все <?php echo esc_html($group['count']); ?> →</a>
                    </div>
                    <div class="category-card-grid">
                        <?php foreach ($group['items'] as $item) : ?>
                            <article class="category-card">
                                <div class="category-card-image">
                                    <img src="<?php echo esc_url($card_image); ?>" alt="<?php echo esc_attr($item['code'] . ' — ' . $item['title']); ?>" loading="lazy">
                                </div>
                                <div class="category-card-body">
                                    <div class="category-card-number"><?php echo esc_html($item['number']); ?></div>
                                    <div class="category-card-code"><?php echo esc_html($item['code']); ?></div>
                                    <div class="category-card-title"><?php echo esc_html($item['title']); ?></div>
                                    <p class="category-card-description"><?php echo esc_html($item['description']); ?></p>
                                    <a class="category-card-link" href="#" aria-label="Открыть <?php echo esc_attr($item['code']); ?>">›</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>

            <div class="load-more-wrap">
                <button class="load-more" type="button">Показать ещё 12</button>
                <div class="pagination" aria-label="Пагинация">
                    <span class="current">1</span><a href="#">2</a><a href="#">3</a><a href="#">4</a><a href="#">5</a>
                </div>
            </div>
        </div>
    </section>

    <section class="category-section faq-section" id="faq">
        <div class="category-inner">
            <div class="faq-layout">
                <div>
                    <div class="section-eyebrow">ЧАСТЫЕ ВОПРОСЫ</div>
                    <h2>Вопросы и ответы<br>об Эфирах</h2>
                </div>
                <div class="faq-list">
                    <details class="faq-item"><summary>Что такое Эфиры Межмирья?</summary><div class="faq-answer">Ответ будет подключаться из редакционного материала категории.</div></details>
                    <details class="faq-item"><summary>Сколько существует Эфиров?</summary><div class="faq-answer">В текущей структуре первого тома описывается 30 Эфиров.</div></details>
                    <details class="faq-item"><summary>Как Эфиры связаны с Эонами?</summary><div class="faq-answer">Связь и формулировка будут вынесены в окончательный редакционный текст страницы.</div></details>
                    <details class="faq-item"><summary>Чем отличаются три группы Эфиров?</summary><div class="faq-answer">Группы будут описаны на основании материалов Энмеркара без добавления неподтверждённых трактовок.</div></details>
                </div>
            </div>
        </div>
    </section>

    <section class="category-section" id="sources">
        <div class="category-inner">
            <div class="section-heading">
                <div class="section-eyebrow">МАТЕРИАЛЫ</div>
                <h2>Источники</h2>
                <p class="category-section-intro">Материалы, использованные для описания категории Эфиров.</p>
            </div>
            <div class="source-grid">
                <a class="source-card" href="#"><div class="source-thumb"><img src="<?php echo esc_url($card_image); ?>" alt=""></div><div><div class="source-type">География Межмирья</div><div class="source-title">География Межмирья. Том 1. Эоны и Эфиры</div><p class="source-description">Основной материал для описания системы Эфиров.</p></div></a>
                <a class="source-card" href="#"><div class="source-thumb"><img src="<?php echo esc_url($card_image); ?>" alt=""></div><div><div class="source-type">Энмеркар</div><div class="source-title">Статьи и материалы</div><p class="source-description">Дополнительные материалы исследования Межмирья.</p></div></a>
            </div>
        </div>
    </section>

    <section class="category-section" id="books">
        <div class="category-inner">
            <div class="section-heading">
                <div class="section-eyebrow">ГЛУБОКОЕ ИЗУЧЕНИЕ</div>
                <h2>Книги для глубокого изучения</h2>
            </div>
            <div class="books-grid">
                <a class="book-card" href="#"><div class="book-thumb"><img src="https://geografiya-mezhmirya.ru/wp-content/uploads/2026/09/geografia-mezhmiria-1.webp" alt="География Межмирья. Том 1"></div><div><div class="source-type">ЭНМЕРКАР</div><div class="book-title">География Межмирья. Том 1. Эоны и Эфиры</div><p class="book-description">Система реальностей и пространств Межмирья.</p></div></a>
                <a class="book-card" href="#"><div class="book-thumb"><img src="https://geografiya-mezhmirya.ru/wp-content/uploads/2026/09/geografia-mezhmiria-2.webp" alt="География Межмирья. Том 2"></div><div><div class="source-type">ЭНМЕРКАР</div><div class="book-title">География Межмирья. Том 2. Существа и Пространства</div><p class="book-description">Следующий уровень исследования Межмирья.</p></div></a>
            </div>
        </div>
    </section>

    <section class="category-final-cta">
        <div class="category-inner final-inner">
            <h2>Продолжить исследование Межмирья</h2>
            <p>Переходите к другим категориям и связывайте отдельные объекты в единую карту знаний.</p>
            <div class="final-links">
                <a href="#">Эоны</a><a href="#">Эфиры</a><a href="#">Существа</a><a href="#">Пространства</a><a href="#">Архонты</a><a href="#">Города</a><a href="#">Посмертие</a>
            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
