<aside
  id="mobile-sidebar"
  class="sidebar"
  aria-label="Навигация по Межмирью"
>
  <div class="sidebar-brand">
   <a href="<?php echo esc_url(home_url('/')); ?>" class="brand-symbol" aria-label="На главную">
  <img 
    src="https://geografiya-mezhmirya.ru/wp-content/uploads/2026/09/logo.webp" 
    alt="География Межмирья"
  >
</a>

<a href="<?php echo esc_url(home_url('/')); ?>" class="brand-name">
  INTERWORLD
</a>

<div class="brand-tagline">
  География Межмирья
</div>
  </div>
  <nav class="side-nav" aria-label="Разделы энциклопедии">
  <a href="#explore"><span>Эоны</span></a>
 <a href="#explore"><span>Эфиры</span></a>
  <a href="#creatures"><span>Существа</span></a>
  <a href="#spaces"><span>Пространства</span></a>
  <a href="#afterlife"><span>Посмертие</span></a>
  <a href="#fairies"><span>Фейри</span></a>
  <a href="#archons"><span>Архонты</span></a>
  <a href="#library"><span>Библиотека</span></a>
</nav>

<div class="sidebar-search-block">
    <form class="sidebar-search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
        <label class="screen-reader-text" for="sidebar-search-input">Поиск по Межмирью</label>

        <span class="sidebar-search-icon">⌕</span>

        <input
            id="sidebar-search-input"
            type="search"
            name="s"
            placeholder="Найти"
            value="<?php echo esc_attr(get_search_query()); ?>"
        >
    </form>
</div>

<div class="side-divider"></div>

<nav class="side-secondary" aria-label="Дополнительные разделы">

  <a href="#map"><span>Карта</span></a>
  <a href="#articles"><span>Статьи</span></a>
  <a href="#glossary"><span>Глоссарий</span></a>
  <a href="#about"><span>О проекте</span></a>
</nav>
 
<div class="sidebar-banners">

  <section class="sidebar-banner banner-blog">
    <img
      src="https://geografiya-mezhmirya.ru/wp-content/uploads/2026/09/banner-enmerkar.webp"
      alt="Блог Энмеркара — Другая Магия"
    >

    <div class="sidebar-banner-content">
      <h3>Блог Энмеркара</h3>
      <p class="banner-subtitle">Другая Магия</p>
      <p>Статьи о Магии и Пути в ключе Западной Традиции.</p>

      <a
        href="https://enmerkar.com/"
        target="_blank"
        rel="noopener"
      >
        Перейти в блог <span>→</span>
      </a>
    </div>
  </section>


  <section class="sidebar-banner banner-heart">
    <img
      src="https://geografiya-mezhmirya.ru/wp-content/uploads/2026/09/banner-serdce-mastera.webp"
      alt="Сердце Мастера"
    >

    <div class="sidebar-banner-content">
      <h3>Сердце Мастера</h3>
      <p>Сервис книг и лекций о Магии, Силе и Пути.</p>

      <a
        href="https://masters-heart.org/"
        target="_blank"
        rel="noopener"
      >
        Исследовать <span>→</span>
      </a>
    </div>
  </section>


  <section class="sidebar-banner banner-path">
    <img
      src="https://geografiya-mezhmirya.ru/wp-content/uploads/2026/09/banner-put-s-magom.webp"
      alt="Путь с Магом — Telegram-канал"
    >

    <div class="sidebar-banner-content">
      <h3>Путь с Магом</h3>
      <p>Telegram-канал с эксклюзивным контентом.</p>

      <a
        href="https://t.me/magia_enmerkara/583"
        target="_blank"
        rel="noopener"
      >
        Подписаться <span>→</span>
      </a>
    </div>
  </section>


  <section class="sidebar-banner banner-magic">
    <img
      src="https://geografiya-mezhmirya.ru/wp-content/uploads/2026/09/banner-magia-enmerkara.webp"
      alt="Магия Энмеркара"
    >

    <div class="sidebar-banner-content">
      <h3>Магия Энмеркара</h3>
      <p>Магия — это Путь Свободы и Дорога Творчества.</p>

      <a
        href="https://www.youtube.com/@Enmerkars_Magic"
        target="_blank"
        rel="noopener"
      >
        Смотреть <span>→</span>
      </a>
    </div>
  </section>

</div>
</aside>
