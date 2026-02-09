</div> <!-- Close main-container from header.php -->
<footer class="bg-[#aaaaac] border-t border-black">
	<div id="footer-sections" class="
		grid grid-cols-1 lg:grid-cols-6
		[&>.footer-section]:py-4 [&>.footer-section]:px-3 [&>.footer-section]:border-b [&>.footer-section]:border-black
		[&>.footer-section]:border-r-0 lg:[&>.footer-section:not(:last-child)]:border-r
		[&_.footer-title]:flex [&_.footer-title]:items-center [&_.footer-title]:justify-between [&_.footer-title]:cursor-pointer lg:[&_.footer-title]:cursor-default [&_.footer-title]:mb-0 lg:[&_.footer-title]:mb-3
		[&_.footer-content]:flex [&_.footer-content]:flex-col [&_.footer-content]:gap-2 [&_.footer-content]:pb-0 lg:[&_.footer-content]:pb-[72px] [&_.footer-content]:mt-3 lg:[&_.footer-content]:mt-0
	">
		<!-- Newsletter -->
		<div class="footer-section">
			<h3 class="footer-title">
				NEWSLETTER
				<i class="cursor-pointer arrow lg:hidden">
					<svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 0 24 24" width="40px" fill="#000000"><path d="M24 24H0V0h24v24z" fill="none" opacity=".87"/><path d="M16.59 8.59L12 13.17 7.41 8.59 6 10l6 6 6-6-1.41-1.41z"/></svg>
				</i>
			</h3>
			<ul class="footer-content hidden lg:block">
				<li><a href="#" class="underline">Subscribe to our newsletter</a></li>
			</ul>
		</div>

		<!-- Client Services -->
		<div class="footer-section">
			<h3 class="footer-title">
				CLIENT SERVICES
				<i class="cursor-pointer arrow lg:hidden">
					<svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 0 24 24" width="40px" fill="#000000"><path d="M24 24H0V0h24v24z" fill="none" opacity=".87"/><path d="M16.59 8.59L12 13.17 7.41 8.59 6 10l6 6 6-6-1.41-1.41z"/></svg>
				</i>
			</h3>
			<ul class="footer-content hidden lg:block">
				<li><a href="<?php echo esc_url(home_url('/payment-methods/')); ?>">Payment Methods</a></li>
				<li><a href="<?php echo esc_url(home_url('/gift-option/')); ?>">Gift Option</a></li>
				<li><a href="<?php echo esc_url(home_url('/shipping-delivery/')); ?>">Shipping & Delivery</a></li>
				<li><a href="<?php echo esc_url(home_url('/exchanges-return/')); ?>">Exchanges & Return</a></li>
			</ul>
		</div>

		<!-- About Us -->
		<div class="footer-section">
			<h3 class="footer-title">
				ABOUT US
				<i class="cursor-pointer arrow lg:hidden">
					<svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 0 24 24" width="40px" fill="#000000"><path d="M24 24H0V0h24v24z" fill="none" opacity=".87"/><path d="M16.59 8.59L12 13.17 7.41 8.59 6 10l6 6 6-6-1.41-1.41z"/></svg>
				</i>
			</h3>
			<ul class="footer-content hidden lg:block">
				<li><a href="<?php echo esc_url(home_url('/aesir-studios/')); ?>">Aesir Studios</a></li>
				<li><a href="<?php echo esc_url(home_url('/careers/')); ?>">Careers</a></li>
				<li><a href="<?php echo esc_url(home_url('/stores/')); ?>">Stores</a></li>
				<li><a href="<?php echo esc_url(home_url('/legal/')); ?>">Legal</a></li>
				<li><a href="<?php echo esc_url(home_url('/privacy-policy-cookie/')); ?>">Privacy Policy & Cookie</a></li>
			</ul>
		</div>

		<!-- Follow Us -->
		<div class="footer-section">
			<h3 class="footer-title">
				FOLLOW US
				<i class="cursor-pointer arrow lg:hidden">
					<svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 0 24 24" width="40px" fill="#000000"><path d="M24 24H0V0h24v24z" fill="none" opacity=".87"/><path d="M16.59 8.59L12 13.17 7.41 8.59 6 10l6 6 6-6-1.41-1.41z"/></svg>
				</i>
			</h3>
			<ul class="footer-content hidden lg:block">
				<li><a href="https://www.instagram.com/aesir.studios/" target="_blank" rel="noopener">Instagram</a></li>
				<li><a href="https://www.facebook.com/AESIR.stds/" target="_blank" rel="noopener">Facebook</a></li>
				<li><a href="https://www.tiktok.com/@aesirstudios/" target="_blank" rel="noopener">Tiktok</a></li>
			</ul>
		</div>

		<!-- Stores -->
		<div class="footer-section">
			<h3 class="footer-title">
				Stores
				<i class="cursor-pointer arrow lg:hidden">
					<svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 0 24 24" width="40px" fill="#000000"><path d="M24 24H0V0h24v24z" fill="none" opacity=".87"/><path d="M16.59 8.59L12 13.17 7.41 8.59 6 10l6 6 6-6-1.41-1.41z"/></svg>
				</i>
			</h3>
			<div class="footer-content hidden lg:block">
				<p>Aesir Flagship Store<br />
				128G Nguyen Dinh Chinh, Ward 8, Phu Nhuan District, Ho Chi Minh City</p>
				<p>Aesir Space<br />
				20 Street 46, Thao Dien Ward, Thu Duc City, Ho Chi Minh City</p>
			</div>
		</div>

		<!-- Contact -->
		<div class="footer-section">
			<h3 class="footer-title">
				CONTACT US
				<i class="cursor-pointer arrow lg:hidden">
					<svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 0 24 24" width="40px" fill="#000000"><path d="M24 24H0V0h24v24z" fill="none" opacity=".87"/><path d="M16.59 8.59L12 13.17 7.41 8.59 6 10l6 6 6-6-1.41-1.41z"/></svg>
				</i>
			</h3>
			<ul class="footer-content hidden lg:block">
				<li>CALL US AT <br><a href="tel:+84916181192" class="underline">+84916181192</a></li>
				<li><a href="mailto:info@aesirstudios.com" class="underline">SEND US AN EMAIL</a></li>
			</ul>
		</div>
	</div>

	<!-- Copyright -->
	<div class="text-center p-3">
		&copy; <?php echo date('Y'); ?> Aesir Studios
	</div>
</footer>

<?php if (function_exists('is_product') && is_product()): ?>
	<?php include_once get_template_directory() . '/template-parts/suggest-size-drawer.php'; ?>
<?php endif; ?>

<?php wp_footer(); ?>

<!--
	PERF-005: Inline JS has been moved to external files:
	- /assets/js/footer-scripts.js
	- /assets/js/product-page.js
	- /assets/js/checkout-methods.js

	These are enqueued in functions.php
-->

</body>
</html>
