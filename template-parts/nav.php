<nav id="menu" class="notranslate w-full fixed top-0 left-0 transition-transform duration-300 z-[999] <?php if ( is_admin_bar_showing() ) {echo 'top-[46px] md:top-[32px]'; } ?>">
    <div class="grid grid-cols-3 items-center h-[45px] justify-center border-b border-black bg-white">
        <div class="items-center justify-start">
            <?php
                wp_nav_menu([
                    'theme_location' => 'main-menu',
                    'container' => false,
                    'menu_class' => 'header-menu hidden lg:flex ml-1',
                    'depth' => 1
                ]);
            ?>
            <svg class="cursor-pointer pl-2 inline-block lg:hidden" id="menu-mobile-btn" xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 0 24 24" width="40px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/></svg>
        </div>
        <div class="flex items-center justify-center">
            <a href="<?php echo bloginfo('url'); ?>" class="text-lg md:text-2xl font-bold !no-underline">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo.png" alt="Aesir Studio Logo" class="!h-6 w-auto"/>
            </a>
        </div>
        <div class="flex items-center justify-end">
            <ul class="header-menu hidden lg:flex">
                <!-- <li><a href="#">Client services</a></li> -->
                <?php if ( is_user_logged_in() ) : ?>
                    <li><a href="<?php echo esc_url( get_permalink( get_option('woocommerce_myaccount_page_id') ) ); ?>">Account</a></li>
                <?php else : ?>
                    <li><a href="<?php echo bloginfo('url'); ?>/my-account">Login</a></li>
                <?php endif; ?>
            </ul>
            <svg class="cursor-pointer px-2 search-icon-header" xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 0 24 24" width="40px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
        </div>
    </div>
    <form class="w-full flex items-center h-[45px] border-b border-black hidden bg-white" action="<?php bloginfo('url'); ?>" id="searchform" method="get">
        <svg class="cursor-pointer px-2 flex-none" xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 0 24 24" width="40px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
        <input type="text" id="s" name="s" value="" class="flex-auto flex outline-none h-6" />
    </form>
    <div id="menu-side" class="<?php if ( is_admin_bar_showing() ) {echo 'h-[calc(100vh-91px)] md:h-[calc(100vh-77px)]'; } else {echo 'h-[calc(100vh-45px)]';} ?> w-1/4 bg-white border-r border-black p-4 hidden">
        <div id="menu-side-close-btn" class="flex items-center justify-end">
            <svg class="cursor-pointer"xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 0 24 24" width="40px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/></svg>
        </div>
        <div class="h-[calc(100%-24px)] flex flex-col justify-between">
            <div id="child-menu-slide"></div>
            <div>
                <ul>
                    <?php if ( is_user_logged_in() ) : ?>
                        <li><a href="<?php echo esc_url( get_permalink( get_option('woocommerce_myaccount_page_id') ) ); ?>">Account</a></li>
                    <?php else : ?>
                        <li><a href="<?php echo bloginfo('url'); ?>/my-account">Login</a></li>
                    <?php endif; ?>
                </ul>
                <div class="mt-2">
                    <p>Aesir Flagship Store<br />
                    128G Nguyễn Đình Chính, Phường 8, Quận Phú Nhuận, TP. Hồ Chí Minh</p>
                    <p>Aesir Space<br />
                    20 Đường 46, Phường Thảo Điền, TP. Thủ Đức, TP. Hồ Chí Minh</p>
                </div>
            </div>
        </div>
    </div> 
    <div id="menu-mobile" class="<?php if ( is_admin_bar_showing() ) {echo 'h-[calc(100vh-91px)] md:h-[calc(100vh-77px)]'; } else {echo 'h-[calc(100vh-45px)]';} ?> w-full bg-white p-4 hidden">
        <div id="menu-mobile-close-btn" class="flex items-center justify-end">
            <svg class="cursor-pointer"xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 0 24 24" width="40px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/></svg>
        </div>
        <div class="h-[calc(100%-24px)] flex flex-col justify-between">
            <div id="child-menu-slide">
                <?php
                    wp_nav_menu([
                        'theme_location' => 'main-menu',
                        'container' => false,
                        'menu_class' => '[&_.sub-menu]:pl-4',
                    ]);
                ?>
            </div>
            <div>
                <ul>
                    <?php if ( is_user_logged_in() ) : ?>
                        <li><a href="<?php echo esc_url( get_permalink( get_option('woocommerce_myaccount_page_id') ) ); ?>">Account</a></li>
                    <?php else : ?>
                        <li><a href="<?php echo bloginfo('url'); ?>/my-account">Login</a></li>
                    <?php endif; ?>
                </ul>
                <div class="mt-2">
                    <p>Aesir Flagship Store<br />
                    128G Nguyễn Đình Chính, Phường 8, Quận Phú Nhuận, TP. Hồ Chí Minh</p>
                    <p>Aesir Space<br />
                    20 Đường 46, Phường Thảo Điền, TP. Thủ Đức, TP. Hồ Chí Minh</p>
                </div>
            </div>
        </div>
    </div>
</nav>
