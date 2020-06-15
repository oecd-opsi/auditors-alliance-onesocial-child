<?php
/**
 * BuddyPress - Members Settings Data
 *
 * @package BuddyPress
 * @subpackage bp-legacy
 * @version 4.0.0
 */

/** This action is documented in bp-templates/bp-legacy/buddypress/members/single/settings/profile.php */
do_action( 'bp_before_member_settings_template' ); ?>

<h2><?php _e( 'Data Export', 'buddypress' );?></h2>

<?php $request = bp_settings_get_personal_data_request(); ?>

<?php if ( $request ) : ?>

	<?php if ( 'request-completed' === $request->status ) : ?>

		<?php if ( bp_settings_personal_data_export_exists( $request ) ) : ?>

			<p><?php esc_html_e( 'Your request for an export of personal data has been completed.', 'buddypress' ); ?></p>
			<p><?php printf( esc_html__( 'You may download your personal data by clicking on the link below. For privacy and security, we will automatically delete the file on %s, so please download it before then.', 'buddypress' ), bp_settings_get_personal_data_expiration_date( $request ) ); ?></p>

			<p><strong><?php printf( '<a href="%1$s">%2$s</a>', bp_settings_get_personal_data_export_url( $request ), esc_html__( 'Download personal data', 'buddypress' ) ); ?></strong></p>

		<?php else : ?>

			<p><?php esc_html_e( 'Your previous request for an export of personal data has expired.', 'buddypress' ); ?></p>
			<p><?php esc_html_e( 'Please click on the button below to make a new request.', 'buddypress' ); ?></p>

			<form id="bp-data-export" method="post">
				<input type="hidden" name="bp-data-export-delete-request-nonce" value="<?php echo wp_create_nonce( 'bp-data-export-delete-request' ); ?>" />
				<button type="submit" name="bp-data-export-nonce" value="<?php echo wp_create_nonce( 'bp-data-export' ); ?>"><?php esc_html_e( 'Request new data export', 'buddypress' ); ?></button>
			</form>

		<?php endif; ?>

	<?php elseif ( 'request-confirmed' === $request->status ) : ?>

		<p><?php printf( esc_html__( 'You previously requested an export of your personal data on %s.', 'buddypress' ), bp_settings_get_personal_data_confirmation_date( $request ) ); ?></p>
		<p><?php esc_html_e( 'You will receive a link to download your export via email once we are able to fulfill your request.', 'buddypress' ); ?></p>

	<?php endif; ?>

<?php else : ?>

	<p><?php esc_html_e( 'You can request an export of your personal data, containing the following items if applicable:', 'buddypress' ); ?></p>

	<div class="export-data-list-wrapper">
		<?php
		/** This filter is documented in /wp-admin/includes/ajax-actions.php */
		$exporters             = apply_filters( 'wp_privacy_personal_data_exporters', array() );
		$custom_friendly_names = apply_filters( 'bp_settings_data_custom_friendly_names', array(
			'wordpress-comments' => _x( 'Comments', 'WP Comments data exporter friendly name', 'buddypress' ),
			'wordpress-media'    => _x( 'Media', 'WP Media data exporter friendly name', 'buddypress' ),
			'wordpress-user'     => _x( 'Personal information', 'WP Media data exporter friendly name', 'buddypress' ),
		) );

		?>
		<ul>
		<?php foreach ( $exporters as $exporter => $data ) :
			// Use the exporter friendly name by default.
			$friendly_name = $data['exporter_friendly_name'];

			// Remove MailPoet items
			if ( strpos( $friendly_name, 'MailPoet') !== false) {
				continue;
			}

			/**
			 * Use the exporter friendly name if directly available
			 * into the exporters array.
			 */
			if ( isset( $data['exporter_bp_friendly_name'] ) ) {
				$friendly_name = $data['exporter_bp_friendly_name'];

			// Look for a potential match into the custom friendly names.
			} elseif ( isset( $custom_friendly_names[ $exporter ] ) ) {
				$friendly_name = $custom_friendly_names[ $exporter ];
			}

			/**
			 * Filters the data exporter friendly name for display on the "Settings > Data" page.
			 *
			 * @since 4.0.0
			 * @since 5.0.0 replaces the `$name` parameter with the `$friendly_name` one.
			 *
			 * @param string $friendly_name Data exporter friendly name.
			 * @param string $exporter      Internal exporter name.
			 */
			$item = apply_filters( 'bp_settings_data_exporter_name', esc_html( $friendly_name ), $exporter );
			?>

			<li><?php echo $item; ?></li>

		<?php endforeach; ?>
		</ul>
	</div>

	<p><?php esc_html_e( 'If you want to make a request, please click on the button below:', 'buddypress' ); ?></p>

	<form id="bp-data-export" method="post">
		<button type="submit" name="bp-data-export-nonce" value="<?php echo wp_create_nonce( 'bp-data-export' ); ?>"><?php esc_html_e( 'Request personal data export', 'buddypress' ); ?></button>
	</form>

<?php endif; ?>

<!--
<h2 class="bp-screen-reader-text"><?php
	/* translators: accessibility text */
	_e( 'Data Erase', 'buddypress' );
?></h2>

<p>You can make a request to erase the following type of data from the site:</p>

<p>If you want to make a request, please click on the button below:</p>

	<form id="bp-data-erase" method="post">
		<button type="submit" name="bp-data-erase-nonce" value="<?php echo wp_create_nonce( 'bp-data-erase' ); ?>">Request data erasure</button>
	</form>
-->

<?php

/** This action is documented in bp-templates/bp-legacy/buddypress/members/single/settings/profile.php */
do_action( 'bp_after_member_settings_template' );
