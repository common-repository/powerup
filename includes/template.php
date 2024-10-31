<div class="app-container">
	<div class="main-container">
		
		<div id="gs-powerup-shortcode-app">

			<header class="gs-powerup-header">
				<div class="pu-containeer-f gs-height-100">
					<div class="pu-roow gs-height-100">
						<div class="logo-area col-xs-6 col-sm-5 col-md-3">
							<router-link to="/"><img src="<?php echo GSPU_PLUGIN_URI . '/assets/img/logo.svg'; ?>" alt="PowerUp Logo"></router-link>
							<span class="powerup-version">v<?php echo GSPU_VERSION; ?></span>
						</div>
						<div class="action-area col-xs-6 col-sm-7 col-md-9">
							<a href="https://www.paypal.com/donate/?hosted_button_id=K7K8YF4U3SCNQ" target="_blank" class="btn btn-brand btn-sm"><i class="fa-brands fa-paypal"></i><span>Donate us</span></a>
						</div>
					</div>
				</div>
			</header>

			<section class="gs-powerup-page-wrap">
				<div class="gs-powerup-page-wrap-inner">
					<app-sidebar></app-sidebar>
					<div class="gs-powerup-app-view-container">
						<router-view :key="$route.fullPath"></router-view>
						<div class="gs-powerup-loader"></div>
					</div>
				</div>
			</section>
		</div>		
	</div>
</div>