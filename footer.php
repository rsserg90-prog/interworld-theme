<footer class="site-footer">
  <div class="footer-inner">
    <div class="footer-top">
      <div class="footer-logo-block"><div class="footer-brand-head"><a href="<?php echo esc_url(home_url('/')); ?>" class="footer-logo-link" aria-label="INTERWORLD — География Межмирья"><img src="https://geografiya-mezhmirya.ru/wp-content/uploads/2026/09/logo.webp" alt="" class="footer-logo"></a><div class="footer-logo-name"><a href="<?php echo esc_url(home_url('/')); ?>">INTERWORLD</a><span>ГЕОГРАФИЯ МЕЖМИРЬЯ</span></div></div><p>Энциклопедия миров, существ и пространств с указанием связанных материалов и источников.</p><div class="footer-social-links"><a href="https://www.youtube.com/@Enmerkars_Magic" target="_blank" rel="noopener noreferrer" aria-label="YouTube">YouTube</a><a href="https://t.me/magia_enmerkara" target="_blank" rel="noopener noreferrer" aria-label="Telegram">Telegram</a></div></div>
      <nav class="footer-navigation" aria-label="Навигация в подвале"><?php wp_nav_menu(array('theme_location'=>'footer','container'=>false,'fallback_cb'=>'interworld_fallback_footer_menu')); ?></nav>
      <div class="footer-navigation"><strong>Разделы</strong><a href="<?php echo esc_url(interworld_content_section_url('zones')); ?>">Зоны и Эфиры</a><a href="<?php echo esc_url(interworld_content_section_url('creatures')); ?>">Существа</a><a href="<?php echo esc_url(interworld_content_section_url('spaces')); ?>">Пространства</a><a href="<?php echo esc_url(interworld_content_section_url('afterlife')); ?>">Посмертие</a><a href="<?php echo esc_url(interworld_content_section_url('fairies')); ?>">Фейри</a><a href="<?php echo esc_url(interworld_content_section_url('archons')); ?>">Архонты</a></div>
    </div>
    <div class="footer-bottom"><p class="footer-copyright">© <?php echo esc_html(wp_date('Y')); ?> INTERWORLD</p><p class="footer-rights">Содержание сайта подготовлено для ознакомления. Публикации проходят редакторскую проверку.</p></div>
  </div>
</footer>
</div><!-- /.interworld-main -->
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
