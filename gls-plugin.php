<?php
/*
Plugin Name: GLS Plugin
Description: This is a plugin to create GLS shipping labels
Version: 1.0
Author: Candan Tombas
Author URI: https://devantia.com
Text Domain: gls-plugin
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gls_plugin_load_textdomain() {
	load_plugin_textdomain( 'gls-plugin', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}

add_action( 'init', 'gls_plugin_load_textdomain' );

function gls_plugin_load() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	function gls_plugin_styles() {
		wp_enqueue_style('gls-plugin-styles', plugins_url('styles.css', __FILE__));
	}

	add_action('admin_enqueue_scripts', 'gls_plugin_styles');

	if ( ! defined( 'GLS_PLUGIN_DIR' ) ) {
		define( 'GLS_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
	}

	require_once( GLS_PLUGIN_DIR . '/vendor/autoload.php' );


	// Add the settings page
	add_action( 'admin_menu', function () {
		add_options_page( __( "GLS Settings", "gls-plugin" ), __( "GLS Settings", "gls-plugin" ), 'manage_options', 'gls-plugin', function () {
			?>
			<div class="wrap">
				<h1>
					<?php echo __( "GLS Settings", "gls-plugin" ); ?>
				</h1>
				<form method="post" action="options.php">
					<?php
					settings_fields( 'gls-plugin' );
					do_settings_sections( 'gls-plugin' );
					submit_button();
					?>
				</form>
			</div>
			<?php
		} );
	} );

	// Add the settings
	add_action( 'admin_init', function () {
		// Register the settings
		register_setting( 'gls-plugin', 'gls_plugin_settings' );

		// Auth section
		add_settings_section( 'gls_plugin_auth_section', __( 'Auth', "gls-plugin" ), null, 'gls-plugin' );

		add_settings_field( 'gls_plugin_user_id', __( 'User ID', "gls-plugin" ), function () {
			$options = get_option( 'gls_plugin_settings' );
			echo '<input type="text" id="gls_plugin_user_id" name="gls_plugin_settings[user_id]" value="' . esc_attr( $options['user_id'] ?? "" ) . '">';
		}, 'gls-plugin', 'gls_plugin_auth_section' );

		add_settings_field( 'gls_plugin_password', __( 'Password', "gls-plugin" ), function () {
			$options = get_option( 'gls_plugin_settings' );
			echo '<input type="password" id="gls_plugin_password" name="gls_plugin_settings[password]" value="' . esc_attr( $options['password'] ?? "" ) . '">';
		}, 'gls-plugin', 'gls_plugin_auth_section' );

		// Shipper Account section
		add_settings_section( 'gls_plugin_shipper_account_section', __( 'Shipper Account Number', "gls-plugin" ), null, 'gls-plugin' );

		add_settings_field( 'gls_plugin_shipper_account', __( "Shipper Account Number", "gls-plugin" ), function () {
			$options = get_option( 'gls_plugin_settings' );
			echo '<input type="text" id="gls_plugin_shipper_account" name="gls_plugin_settings[shipper_account]" value="' . esc_attr( $options['shipper_account'] ?? "" ) . '">';
		}, 'gls-plugin', 'gls_plugin_shipper_account_section' );

		// Shipper Address section
		add_settings_section( 'gls_plugin_shipper_address_section', __( 'Shipper Address', "gls-plugin" ), null, 'gls-plugin' );

		add_settings_field( 'gls_plugin_country', __( 'Country', "gls-plugin" ), function () {
			$options = get_option( 'gls_plugin_settings' );
			$selected_country = $options['country'] ?? "DE";

			$countries = array(
				'DE' => __( 'Germany' ),
				'AT' => __( 'Austria' ),
				'BE' => __( 'Belgium' ),
				'BG' => __( 'Bulgaria' ),
				'CZ' => __( 'Czech Republic' ),
				'DK' => __( 'Denmark' ),
				'EE' => __( 'Estonia' ),
				'ES' => __( 'Spain' ),
				'FI' => __( 'Finland' ),
				'FR' => __( 'France' ),
				'IE' => __( 'Ireland' ),
				'HR' => __( 'Croatia' ),
				'HU' => __( 'Hungary' ),
				'IT' => __( 'Italy' ),
				'LT' => __( 'Lithuania' ),
				'LU' => __( 'Luxembourg' ),
				'LV' => __( 'Latvia' ),
				'MC' => __( 'Monaco' ),
				'NL' => __( 'Netherlands' ),
				'PL' => __( 'Poland' ),
				'PT' => __( 'Portugal' ),
				'RO' => __( 'Romania' ),
				'SE' => __( 'Sweden' ),
				'SI' => __( 'Slovenia' ),
				'SK' => __( 'Slovakia' )
			);

			echo '<select id="gls_plugin_country" name="gls_plugin_settings[country]">';
			foreach ( $countries as $code => $name ) {
				$selected = ( $code == $selected_country ) ? 'selected' : '';
				echo "<option value='$code' $selected>$name</option>";
			}
			echo '</select>';
		}, 'gls-plugin', 'gls_plugin_shipper_address_section' );

		add_settings_field( 'gls_plugin_postal_code', __( 'Postal Code', "gls-plugin" ), function () {
			$options = get_option( 'gls_plugin_settings' );
			echo '<input type="text" id="gls_plugin_postal_code" name="gls_plugin_settings[postal_code]" value="' . esc_attr( $options['postal_code'] ?? "" ) . '">';
		}, 'gls-plugin', 'gls_plugin_shipper_address_section' );

		add_settings_field( 'gls_plugin_city', __( 'City', "gls-plugin" ), function () {
			$options = get_option( 'gls_plugin_settings' );
			echo '<input type="text" id="gls_plugin_city" name="gls_plugin_settings[city]" value="' . esc_attr( $options['city'] ?? "" ) . '">';
		}, 'gls-plugin', 'gls_plugin_shipper_address_section' );

		add_settings_field( 'gls_plugin_street', __( 'Street', "gls-plugin" ), function () {
			$options = get_option( 'gls_plugin_settings' );
			echo '<input type="text" id="gls_plugin_street" name="gls_plugin_settings[street]" value="' . esc_attr( $options['street'] ?? "" ) . '">';
		}, 'gls-plugin', 'gls_plugin_shipper_address_section' );

		add_settings_field( 'gls_plugin_name', __( 'Name', "gls-plugin" ), function () {
			$options = get_option( 'gls_plugin_settings' );
			echo '<input type="text" id="gls_plugin_name" name="gls_plugin_settings[name]" value="' . esc_attr( $options['name'] ?? "" ) . '">';
		}, 'gls-plugin', 'gls_plugin_shipper_address_section' );
	} );

	function gls_plugin_meta_box_callback( $post ) {
		echo '<div class="gls-plugin gls-plugin-grid">';
    
		echo '<div class="form-field"><a class="button" href="' . admin_url( 'admin-post.php?action=gls_plugin_download_label&order_id=' . $post->ID ) . '" id="gls-plugin-create-label">' . __( "Create Shipping Label", "gls-plugin" ) . '</a></div>';
		echo '<div class="form-field"><a class="button" href="' . admin_url( 'admin-post.php?action=gls_plugin_download_return_label&order_id=' . $post->ID ) . '" id="gls-plugin-create-return-label">' . __( "Create Return Label", "gls-plugin" ) . '</a></div>';
	
		echo '</div>';
	}

	function gls_plugin_order_meta_box() {
		add_meta_box(
			'gls-plugin-meta-box', // ID der Meta-Box
			__( 'GLS Shipping Label', "gls-plugin" ), // Titel der Meta-Box
			'gls_plugin_meta_box_callback', // Callback-Funktion für den Inhalt der Box
			'woocommerce_page_wc-orders', // Post-Typ (WooCommerce Bestellung)
			'side', // Kontext (wo die Box erscheint): 'normal', 'side', 'advanced'
			//'high' // Priorität, in der die Box erscheint: 'high', 'low', 'default'
		);
	}

	add_action( 'add_meta_boxes', 'gls_plugin_order_meta_box' );

	function handle_gls_plugin_download_return_label() {
		$options = get_option( 'gls_plugin_settings' );

		$order_id = $_GET['order_id'];

		$order = wc_get_order( $order_id );

		$userId = $options['user_id'] ?? "";
		$password = $options['password'] ?? "";
		$shipperAccount = $options["shipper_account"] ?? "";

		$logger = new \Psr\Log\NullLogger();
		$serviceFactory = new \GlsGroup\Sdk\ParcelProcessing\Service\ServiceFactory();
		$service = $serviceFactory->createShipmentService( $userId, $password, $logger, false );

		$requestBuilder = new \GlsGroup\Sdk\ParcelProcessing\RequestBuilder\ReturnShipmentRequestBuilder();

		$requestBuilder->setShipperAccount( $shipperAccount );
		$requestBuilder->setShipperAddress(
			$options['country'] ?? 'DE',
			$options['postal_code'],
			$options['city'],
			$options['street'],
			$options['name']
		);

		// Set your company as the recipient
		$requestBuilder->setRecipientAddress(
			$order->get_shipping_country() ?? "DE",
			$order->get_billing_postcode(),
			$order->get_billing_city(),
			$order->get_billing_address_1(),
			$order->get_formatted_billing_full_name()
		);


		foreach ( $order->get_items() as $item_id => $item ) {
			$product = $item->get_product();
			$requestBuilder->addParcel( $product->get_weight() * $item->get_quantity() );
		}

		$request = $requestBuilder->create();

		$shipment = $service->createShipment( $request );
		$upload_dir = wp_upload_dir();
		$base_dir = $upload_dir['basedir'];

		$consignment_id = $shipment->getConsignmentId();

		foreach ( $shipment->getLabels() as $i => $label ) {
			$file_path = "{$base_dir}/{$shipment->getConsignmentId()}-{$i}.pdf";
			file_put_contents( $file_path, $label );

			// Get the URL of the file
			$file_url = content_url("/uploads/{$shipment->getConsignmentId()}-{$i}.pdf");

			// Add the URL to the order note
			$order->add_order_note(
				sprintf(
					__( 'GLS Return Shipping Label created. Consignment ID: %s. <a href=\'%s\'>Download label</a>', "gls-plugin" ),
					$consignment_id,
					$file_url
				)
			);
		}

		foreach ( $shipment->getLabels() as $i => $label ) {
			$file_path = "{$base_dir}/{$shipment->getConsignmentId()}-{$i}.pdf";
			file_put_contents( $file_path, $label );
		}

		wp_redirect( admin_url( 'post.php?post=' . $order_id . '&action=edit' ) );
	}

	add_action( 'admin_post_gls_plugin_download_return_label', 'handle_gls_plugin_download_return_label' );

	function handle_gls_plugin_download_label() {
		$options = get_option( 'gls_plugin_settings' );

		$order_id = $_GET['order_id'];

		$order = wc_get_order( $order_id );

		$userId = $options['user_id'] ?? "";
		$password = $options['password'] ?? "";
		$shipperAccount = $options["shipper_account"] ?? "";

		$logger = new \Psr\Log\NullLogger();
		$serviceFactory = new \GlsGroup\Sdk\ParcelProcessing\Service\ServiceFactory();
		$service = $serviceFactory->createShipmentService( $userId, $password, $logger, false );

		$requestBuilder = new \GlsGroup\Sdk\ParcelProcessing\RequestBuilder\ShipmentRequestBuilder();
		$requestBuilder->setShipperAccount( $shipperAccount );
		$requestBuilder->setRecipientAddress(
			$order->get_shipping_country() ?? "DE",
			$order->get_billing_postcode(),
			$order->get_billing_city(),
			$order->get_billing_address_1(),
			$order->get_formatted_billing_full_name()
		);


		foreach ( $order->get_items() as $item_id => $item ) {
			$product = $item->get_product();
			$requestBuilder->addParcel( $product->get_weight() * $item->get_quantity() );
		}

		$request = $requestBuilder->create();

		$shipment = $service->createShipment( $request );

		$upload_dir = wp_upload_dir();
		$base_dir = $upload_dir['basedir'];

		$consignment_id = $shipment->getConsignmentId();

		foreach ( $shipment->getLabels() as $i => $label ) {
			$file_path = "{$base_dir}/{$shipment->getConsignmentId()}-{$i}.pdf";
			file_put_contents( $file_path, $label );

			// Get the URL of the file
			$file_url = content_url("/uploads/{$shipment->getConsignmentId()}-{$i}.pdf");

			// Add the URL to the order note
			$order->add_order_note(
				sprintf(
					__( 'GLS Shipping Label created. Consignment ID: %s. <a href=\'%s\'>Download label</a>', "gls-plugin" ),
					$consignment_id,
					$file_url
				)
			);
		}

		wp_redirect( admin_url( 'post.php?post=' . $order_id . '&action=edit' ) );
	}

	add_action( 'admin_post_gls_plugin_download_label', 'handle_gls_plugin_download_label' );
}

add_action( 'plugins_loaded', 'gls_plugin_load', 10 );