<?php
/**
 * Load up the menu page
 */
add_action( 'admin_menu', 'ors_rental_options_add_page' );
function ors_rental_options_add_page() {
	add_submenu_page( "edit.php?post_type=rental", "Rental Options", "Options", 'read', 'rental_options', 'rental_options_do_page');
}

/**
 * Create the options page
 */
function rental_options_do_page() {
	$updated = false;

	if ($_POST) {
		if ($_POST['inquiry-form']) {
			update_option('ors-inquiry-form', trim(stripslashes($_POST['inquiry-form'])));
			$updated = true;
		}
		if ($_POST['tell-a-friend-form']) {
			update_option('ors-tell-a-friend-form', trim(stripslashes($_POST['tell-a-friend-form'])));
			$updated = true;
		}
		if ($_POST['global-features']) {
			update_option('ors-global-features', trim($_POST['global-features']));
			$updated = true;
		}
		if ($_POST['global-options']) {
			update_option('ors-global-options', trim($_POST['global-options']));
			$updated = true;
		}
	}

	?>
	<div class="wrap">
		<?php screen_icon(); echo "<h2>" . 'Rental Options' . "</h2>"; ?>

		<?php if ( $updated == true ) : ?>
		<div class="updated fade"><p><strong>Options Saved</strong></p></div>
		<?php endif; ?>

		<form method="post">
			<table class="form-table">
				<tr valign="top">
					<th scope="row">Inquiry Form</th>
					<td><textarea name="inquiry-form" cols=80 rows=5><?php echo get_option('ors-inquiry-form'); ?></textarea></td>
				</tr>
				<tr valign="top">
					<th scope="row">Tell-A-Friend</th>
					<td><textarea name="tell-a-friend-form" cols=80 rows=5><?php echo get_option('ors-tell-a-friend-form'); ?></textarea></td>
				</tr>
				<tr valign="top">
					<th scope="row">Features</th>
					<td><textarea name="global-features" cols=80 rows=5><?php echo get_option('ors-global-features'); ?></textarea></td>
				</tr>
				<tr valign="top">
					<th scope="row">Options</th>
					<td><textarea name="global-options" cols=80 rows=5><?php echo get_option('ors-global-options'); ?></textarea></td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="Save Options" />
			</p>
		</form>
	</div>
<?php
}
