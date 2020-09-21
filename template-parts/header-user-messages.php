<?php
$messages	 = buddyboss_adminbar_messages();
$link		 = $messages[ 0 ];
unset( $messages[ 0 ] );

if ( $link && onesocial_get_option( 'messages_button' ) ) {
	?>

	<div class="header-notifications user-messages">

		<a id="user-messages" class="header-button underlined" href="<?php echo $link->href; ?>">
			<?php
			if ( preg_match( "/[0-9]/", $link->title ) ) {
				echo preg_replace('/>([0-9]*)<\/span/', '><b>$1</b></span', $link->title);
			} else {
				echo '<span class="no-alert"><b>0</b></span>';
			}
			?>
		</a>

		<div class="pop">
			<?php echo buddyboss_get_unread_messages_html() ?>
		</div>

	</div>

	<?php
}