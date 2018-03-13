<h1 class="litespeed-title"><?php echo __('Quic Cloud User Panel', 'litespeed-cache') ; ?></h1>

<form method="post" action="admin.php" id="litespeed_form_quic" class="litespeed-relative">
	<input type="hidden" name="<?php echo LiteSpeed_Cache::ACTION_KEY ; ?>" value="<?php echo LiteSpeed_Cache::ACTION_CDN_QUICCLOUD ; ?>" />
	<input type="hidden" name="step" value="2" />
	<?php wp_nonce_field( LiteSpeed_Cache::ACTION_CDN_QUICCLOUD, LiteSpeed_Cache::NONCE_NAME ) ; ?>

	<div>
		<h4><?php echo __( 'Email', 'litespeed-cache' ) ; ?>:</h4>
		<input type="text" name="email" value="<?php echo LiteSpeed_Cache::config( LiteSpeed_Cache_Config::OPID_CDN_QUIC_EMAIL ) ; ?>" class="litespeed-regular-text litespeed-input-large" required placeholder="<?php echo __( 'Email', 'litespeed-cache' ) ; ?>" />
	</div>

	<input type="submit" class="button litespeed-btn-primary" value="<?php echo __( 'Next', 'litespeed-cache' ) ; ?>" />

</form>
