<footer class="site-footer">

  <div class="footer-inner">

    <div class="footer-top">

      <!-- ЛОГОТИП -->
      <div class="footer-logo-block">

        <a
          href="<?php echo esc_url(home_url('/')); ?>"
          class="footer-logo-link"
          aria-label="INTERWORLD — География Межмирья"
        >
          <img
            src="https://geografiya-mezhmirya.ru/wp-content/uploads/2026/09/logo.webp"
            alt="INTERWORLD — География Межмирья"
            class="footer-logo"
          >
        </a>

        <div class="footer-logo-name">

          <a href="<?php echo esc_url(home_url('/')); ?>">
            INTERWORLD
          </a>

          <span>
            ГЕОГРАФИЯ МЕЖМИРЬЯ
          </span>

        </div>

      </div>


      <!-- ОСНОВНАЯ НАВИГАЦИЯ -->
      <nav
        class="footer-navigation"
        aria-label="Навигация в подвале"
      >

        <a href="<?php echo esc_url(home_url('/')); ?>">
          Главная
        </a>

        <a href="#map">
          Карта
        </a>

        <a href="#library">
          Библиотека
        </a>

        <a href="#articles">
          Статьи
        </a>

        <a href="#glossary">
          Глоссарий
        </a>

        <a href="#about">
          О проекте
        </a>

        <a href="#contacts">
          Контакты
        </a>

      </nav>


      <!-- СОЦСЕТИ -->
      <div class="footer-social">

        <div class="footer-social-links">

          <!-- YouTube -->
          <a
            href="https://www.youtube.com/@Enmerkars_Magic"
            target="_blank"
            rel="noopener"
            aria-label="YouTube"
            title="YouTube"
          >
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.5 12 3.5 12 3.5s-7.5 0-9.4.6A3 3 0 0 0 .5 6.2 31 31 0 0 0 0 12a31 31 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.1c1.9.6 9.4.6 9.4.6s7.5 0 9.4-.6a3 3 0 0 0 2.1-2.1A31 31 0 0 0 24 12a31 31 0 0 0-.5-5.8ZM9.6 15.9V8.1l6.8 3.9-6.8 3.9Z"/>
            </svg>
          </a>


          <!-- Telegram -->
          <a
            href="#"
            target="_blank"
            rel="noopener"
            aria-label="Telegram"
            title="Telegram"
          >
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M21.8 3.2 2.9 10.5c-1.3.5-1.3 1.2-.2 1.5l4.8 1.5 1.8 5.5c.2.6.1.9.8.9.5 0 .7-.2 1-.4.3-.3 1.9-1.8 3.8-3.5l5 3.7c.9.5 1.6.3 1.8-.8l3.2-15c.3-1.4-.5-2-1.7-1.5ZM8.2 13.2l10.9-6.9c.5-.3.9-.1.5.2l-8.8 7.9-.3 3.2-1.4-4.4-.9-.3Z"/>
            </svg>
          </a>


          <!-- VK -->
          <a
            href="#"
            target="_blank"
            rel="noopener"
            aria-label="VK"
            title="VK"
          >
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M13.1 17.5c.4 0 .6-.3.6-.8 0-1.5.5-2.2 1.5-2.2.9 0 1.7.9 2.6 2 .7.8 1.2 1 2.1 1h1.3c.5 0 .8-.3.8-.7 0-.4-.3-.7-.8-1.2l-1.1-1.2c-.8-.9-.8-1.1-.1-2.1l1.2-1.7c.6-.8 1.1-1.6 1.1-2.1 0-.4-.3-.7-.8-.7h-1.3c-.4 0-.6.2-.8.7-.6 1.4-1.5 2.8-2.5 3.8-.3.3-.5.4-.7.4-.3 0-.4-.2-.4-.8V8.5c0-.7-.2-1-.8-1h-2.1c-.4 0-.6.2-.6.5 0 .4.2.5.6.6.4.1.5.4.5 1.1v2.9c0 .7-.1.9-.4.9-.7 0-2.5-2.7-3.3-5.2-.2-.6-.5-.8-1-.8H5.8c-.5 0-.8.3-.8.7 0 .5.7 2.4 1.6 4.1 1.2 2.3 3.7 5.2 6.5 5.2h.0Z"/>
            </svg>
          </a>


          <!-- Instagram -->
          <a
            href="#"
            target="_blank"
            rel="noopener"
            aria-label="Instagram"
            title="Instagram"
          >
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <rect x="3" y="3" width="18" height="18" rx="5" fill="none" stroke="currentColor" stroke-width="2"/>
              <circle cx="12" cy="12" r="4.2" fill="none" stroke="currentColor" stroke-width="2"/>
              <circle cx="17.4" cy="6.7" r="1.1" fill="currentColor"/>
            </svg>
          </a>

        </div>


        

      </div>

    </div>


    <!-- НИЖНЯЯ СТРОКА -->
    <div class="footer-bottom">

      <p class="footer-copyright">
        © 2026 INTERWORLD. Энциклопедия Межмирья.
      </p>

      <p class="footer-rights">
        Все права защищены.
      </p>

    </div>

  </div>

</footer>

</div><!-- /.interworld-main -->
</div><!-- /.interworld-layout -->


<script>
document.addEventListener('DOMContentLoaded', function () {

    const button = document.querySelector('.mobile-menu-toggle');
    const sidebar = document.querySelector('.sidebar');

    if (!button || !sidebar) {
        return;
    }

    function closeMobileMenu() {
        document.body.classList.remove('mobile-sidebar-open');
        button.setAttribute('aria-expanded', 'false');
    }

    function openMobileMenu() {
        document.body.classList.add('mobile-sidebar-open');
        button.setAttribute('aria-expanded', 'true');
    }

    button.addEventListener('click', function (event) {
        event.stopPropagation();

        const isOpen =
            document.body.classList.contains('mobile-sidebar-open');

        if (isOpen) {
            closeMobileMenu();
        } else {
            openMobileMenu();
        }
    });

    sidebar.addEventListener('click', function (event) {
        event.stopPropagation();
    });

    document.addEventListener('click', function () {
        if (document.body.classList.contains('mobile-sidebar-open')) {
            closeMobileMenu();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeMobileMenu();
        }
    });

    const sidebarLinks = sidebar.querySelectorAll('a');

    sidebarLinks.forEach(function (link) {
        link.addEventListener('click', function () {
            closeMobileMenu();
        });
    });

});
</script>

<?php wp_footer(); ?>

</body>
</html>