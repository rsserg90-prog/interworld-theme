</div><!-- /.interworld-main -->
<footer class="site-footer">
  <div class="footer-inner">
    <div class="iw-footer-grid">
      <div class="iw-footer-brand">
        <a class="iw-footer-brand-head" href="<?php echo esc_url(home_url('/')); ?>" aria-label="INTERWORLD — на главную">
          <img src="https://geografiya-mezhmirya.ru/wp-content/uploads/2026/09/logo.webp" alt="" width="56" height="56">
          <span><strong>INTERWORLD</strong><small>ГЕОГРАФИЯ МЕЖМИРЬЯ</small></span>
        </a>
        <p>Энциклопедия миров, существ и пространств с указанием связанных материалов и источников.</p>
        <div class="iw-footer-social" aria-label="Социальные сети">
          <a href="https://www.youtube.com/@Enmerkars_Magic" target="_blank" rel="noopener noreferrer" aria-label="YouTube">
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="2" y="5" width="20" height="14" rx="4" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="m10 9 5 3-5 3z" fill="currentColor"/></svg>
          </a>
          <a href="https://t.me/magia_enmerkara" target="_blank" rel="noopener noreferrer" aria-label="Telegram">
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M22 2 2 10l7 3 3 9L22 2ZM9 13 22 2" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
          </a>
        </div>
      </div>
      <nav class="iw-footer-nav" aria-label="Карта сайта">
        <h2>Карта сайта</h2>
        <?php wp_nav_menu(array('theme_location'=>'footer','container'=>false,'fallback_cb'=>'interworld_fallback_footer_menu')); ?>
      </nav>
      <nav class="iw-footer-nav" aria-label="Разделы Межмирья">
        <h2>Разделы</h2>
        <ul>
          <li><a href="<?php echo esc_url(interworld_content_section_url('zones')); ?>">Зоны и Эфиры</a></li>
          <li><a href="<?php echo esc_url(interworld_content_section_url('creatures')); ?>">Существа</a></li>
          <li><a href="<?php echo esc_url(interworld_content_section_url('spaces')); ?>">Пространства</a></li>
          <li><a href="<?php echo esc_url(interworld_content_section_url('afterlife')); ?>">Посмертие</a></li>
          <li><a href="<?php echo esc_url(interworld_content_section_url('fairies')); ?>">Фейри</a></li>
          <li><a href="<?php echo esc_url(interworld_content_section_url('archons')); ?>">Архонты</a></li>
        </ul>
      </nav>
      <nav class="iw-footer-nav" aria-label="О проекте">
        <h2>О проекте</h2>
        <ul>
          <li><a href="<?php echo esc_url(home_url('/')); ?>">Главная</a></li>
          <li><a href="<?php echo esc_url(interworld_content_section_url('map')); ?>">Карта</a></li>
          <li><a href="<?php echo esc_url(interworld_content_section_url('persons')); ?>">Личности</a></li>
          <li><a href="<?php echo esc_url(interworld_content_section_url('about')); ?>">О проекте</a></li>
        </ul>
      </nav>
    </div>
    <div class="footer-bottom"><p class="footer-copyright">© <?php echo esc_html(wp_date('Y')); ?> INTERWORLD</p><p class="footer-rights">Содержание сайта подготовлено для ознакомления. Публикации проходят редакторскую проверку.</p></div>
  </div>
</footer>

</div><!-- /.interworld-layout -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const button = document.querySelector('.mobile-menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    if (!button || !sidebar) return;
    function closeMenu() { document.body.classList.remove('mobile-sidebar-open'); button.setAttribute('aria-expanded', 'false'); }
    function openMenu() { document.body.classList.add('mobile-sidebar-open'); button.setAttribute('aria-expanded', 'true'); }
    button.addEventListener('click', function (event) { event.stopPropagation(); document.body.classList.contains('mobile-sidebar-open') ? closeMenu() : openMenu(); });
    sidebar.addEventListener('click', function (event) { event.stopPropagation(); });
    document.addEventListener('click', closeMenu);
    document.addEventListener('keydown', function (event) { if (event.key === 'Escape') closeMenu(); });
    sidebar.querySelectorAll('a').forEach(function (link) { link.addEventListener('click', closeMenu); });
});
</script>
<?php wp_footer(); ?>
</body>
</html>
