<?php
/*
Plugin Name: WooGLS Labels
Description: This is a WooCommerce plugin to create GLS shipping and return labels
Version: 1.0.2
Author: Candan Tombas
Author URI: https://devantia.com
Text Domain: woogls-labels
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check if the plugin is configured
function isPluginConfigured() {
	// check all options are set
	$options = get_option( 'woogls_labels_settings' );

	if ( ! isset( $options['user_id'] ) || empty( $options['user_id'] ) ) {
		return false;
	}

	if ( ! isset( $options['password'] ) || empty( $options['password'] ) ) {
		return false;
	}

	if ( ! isset( $options['shipper_account'] ) || empty( $options['shipper_account'] ) ) {
		return false;
	}

	if ( ! isset( $options['country'] ) || empty( $options['country'] ) ) {
		return false;
	}

	if ( ! isset( $options['postal_code'] ) || empty( $options['postal_code'] ) ) {
		return false;
	}

	if ( ! isset( $options['city'] ) || empty( $options['city'] ) ) {
		return false;
	}

	if ( ! isset( $options['street'] ) || empty( $options['street'] ) ) {
		return false;
	}

	if ( ! isset( $options['name'] ) || empty( $options['name'] ) ) {
		return false;
	}

	return true;
}

// Create the plugin directory and .htaccess file
function create_woogls_labels_directory_and_htaccess() {
	$upload_dir = wp_upload_dir();
	$base_dir = $upload_dir['basedir'];
	$woogls_labels_dir = "{$base_dir}/woogls-labels";

	// Check if the directory exists, if not, create it
	if ( ! file_exists( $woogls_labels_dir ) ) {
		mkdir( $woogls_labels_dir, 0755, true );
	}

	// Check if the .htaccess file exists, if not, create it
	$htaccess_file = "{$woogls_labels_dir}/.htaccess";
		
	if ( ! file_exists( $htaccess_file ) ) {
		$htaccess_content = "deny from all";
		file_put_contents( $htaccess_file, $htaccess_content );
	}
}

function woogls_labels_load_textdomain() {
	load_plugin_textdomain( 'woogls-labels', false, basename( dirname( __FILE__ ) ) . '/languages/' );

	create_woogls_labels_directory_and_htaccess();
}

add_action( 'init', 'woogls_labels_load_textdomain' );

function woogls_labels_load() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	function woogls_labels_styles() {
		wp_enqueue_style('woogls-labels-styles', plugins_url('styles.css', __FILE__));
	}

	add_action('admin_enqueue_scripts', 'woogls_labels_styles');

	if ( ! defined( 'WOOGLS_LABELS_DIR' ) ) {
		define( 'WOOGLS_LABELS_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
	}

	require_once( WOOGLS_LABELS_DIR . '/vendor/autoload.php' );


	// Add the settings page
	add_action( 'admin_menu', function () {
		add_options_page( __( "GLS Settings", "woogls-labels" ), __( "GLS Settings", "woogls-labels" ), 'manage_options', 'woogls-labels', function () {
			?>
			<div class="wrap">
				<h1>
					<?php echo __( "GLS Settings", "woogls-labels" ); ?>
				</h1>
				<form method="post" action="options.php">
					<?php
					settings_fields( 'woogls-labels' );
					do_settings_sections( 'woogls-labels' );
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
		register_setting( 'woogls-labels', 'woogls_labels_settings' );

		// Auth section
		add_settings_section( 'woogls_labels_auth_section', __( 'Auth', "woogls-labels" ), null, 'woogls-labels' );

		add_settings_field( 'woogls_labels_user_id', __( 'User ID', "woogls-labels" ), function () {
			$options = get_option( 'woogls_labels_settings' );
			echo '<input type="text" id="woogls_labels_user_id" name="woogls_labels_settings[user_id]" value="' . esc_attr( $options['user_id'] ?? "" ) . '">';
		}, 'woogls-labels', 'woogls_labels_auth_section' );

		add_settings_field( 'woogls_labels_password', __( 'Password', "woogls-labels" ), function () {
			$options = get_option( 'woogls_labels_settings' );
			echo '<input type="password" id="woogls_labels_password" name="woogls_labels_settings[password]" value="' . esc_attr( $options['password'] ?? "" ) . '">';
		}, 'woogls-labels', 'woogls_labels_auth_section' );

		// Shipper Account section
		add_settings_section( 'woogls_labels_shipper_account_section', __( 'Shipper Number', "woogls-labels" ), null, 'woogls-labels' );

		add_settings_field( 'woogls_labels_shipper_account', __( "Shipper Number", "woogls-labels" ), function () {
			$options = get_option( 'woogls_labels_settings' );
			echo '<input type="text" id="woogls_labels_shipper_account" name="woogls_labels_settings[shipper_account]" value="' . esc_attr( $options['shipper_account'] ?? "" ) . '">';
		}, 'woogls-labels', 'woogls_labels_shipper_account_section' );

		// Shipper Address section
		add_settings_section( 'woogls_labels_shipper_address_section', __( 'Shipper Address', "woogls-labels" ), null, 'woogls-labels' );

		add_settings_field( 'woogls_labels_country', __( 'Country', "woogls-labels" ), function () {
			$options = get_option( 'woogls_labels_settings' );
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

			echo '<select id="woogls_labels_country" name="woogls_labels_settings[country]">';
			foreach ( $countries as $code => $name ) {
				$selected = ( $code == $selected_country ) ? 'selected' : '';
				echo "<option value='$code' $selected>$name</option>";
			}
			echo '</select>';
		}, 'woogls-labels', 'woogls_labels_shipper_address_section' );

		add_settings_field( 'woogls_labels_postal_code', __( 'Postal Code', "woogls-labels" ), function () {
			$options = get_option( 'woogls_labels_settings' );
			echo '<input type="text" id="woogls_labels_postal_code" name="woogls_labels_settings[postal_code]" value="' . esc_attr( $options['postal_code'] ?? "" ) . '">';
		}, 'woogls-labels', 'woogls_labels_shipper_address_section' );

		add_settings_field( 'woogls_labels_city', __( 'City', "woogls-labels" ), function () {
			$options = get_option( 'woogls_labels_settings' );
			echo '<input type="text" id="woogls_labels_city" name="woogls_labels_settings[city]" value="' . esc_attr( $options['city'] ?? "" ) . '">';
		}, 'woogls-labels', 'woogls_labels_shipper_address_section' );

		add_settings_field( 'woogls_labels_street', __( 'Street', "woogls-labels" ), function () {
			$options = get_option( 'woogls_labels_settings' );
			echo '<input type="text" id="woogls_labels_street" name="woogls_labels_settings[street]" value="' . esc_attr( $options['street'] ?? "" ) . '">';
		}, 'woogls-labels', 'woogls_labels_shipper_address_section' );

		add_settings_field( 'woogls_labels_name', __( 'Name', "woogls-labels" ), function () {
			$options = get_option( 'woogls_labels_settings' );
			echo '<input type="text" id="woogls_labels_name" name="woogls_labels_settings[name]" value="' . esc_attr( $options['name'] ?? "" ) . '">';
		}, 'woogls-labels', 'woogls_labels_shipper_address_section' );
	} );

	// Add the meta box callback function
	function woogls_labels_meta_box_callback( $post ) {

		if(!isPluginConfigured()) {
			echo '<div class="woogls-labels woogls-labels-grid">';
			echo '<div class="form-field"><a class="button" href="' . admin_url( 'options-general.php?page=woogls-labels' ) . '" id="woogls-labels-configure">' . __( "Configure WooGLS Labels", "woogls-labels" ) . '</a></div>';
			echo '</div>';
		} else {
			echo '<div class="woogls-labels woogls-labels-grid">';
    
			echo '<div class="form-field"><a class="button" href="' . admin_url( 'admin-post.php?action=woogls_labels_download_label&order_id=' . $post->ID ) . '" id="woogls-labels-create-label">' . __( "Create Shipping Label", "woogls-labels" ) . '</a></div>';
			echo '<div class="form-field"><a class="button" href="' . admin_url( 'admin-post.php?action=woogls_labels_download_return_label&order_id=' . $post->ID ) . '" id="woogls-labels-create-return-label">' . __( "Create Return Label", "woogls-labels" ) . '</a></div>';
		
			echo '</div>';
		}
	}

	// Add the meta box
	function woogls_labels_order_meta_box() {
		add_meta_box(
			'woogls-labels-meta-box', // ID
			__( 'GLS Shipping Label', "woogls-labels" ), // Title
			'woogls_labels_meta_box_callback', // Callback
			'woocommerce_page_wc-orders', // Post Type (page, post, etc.)
			'side', // Context (normal, advanced, side)
			'high' // Priority (default, low, high, core)
		);
	}

	add_action( 'add_meta_boxes', 'woogls_labels_order_meta_box' );

	// Handle the download return label action
	function handle_woogls_labels_download_return_label() {
		if(!is_admin()) {
			return;
		}

		$options = get_option( 'woogls_labels_settings' );

		$order_id = $_GET['order_id'];

		$order = wc_get_order( $order_id );

		$userId = $options['user_id'] ?? "";
		$password = $options['password'] ?? "";
		$shipperAccount = $options["shipper_account"] ?? "";

		$logger = new \Psr\Log\NullLogger();
		$serviceFactory = new \GlsGroup\Sdk\ParcelProcessing\Service\ServiceFactory();
		$service = $serviceFactory->createShipmentService( $userId, $password, $logger, false );

		$requestBuilder = new \GlsGroup\Sdk\ParcelProcessing\RequestBuilder\ReturnShipmentRequestBuilder();

		// Set the shipper account
		$requestBuilder->setShipperAccount( $shipperAccount );

		// Set the shipper address
		$requestBuilder->setShipperAddress(
			$order->get_shipping_country() ?? "DE",
			$order->get_billing_postcode(),
			$order->get_billing_city(),
			$order->get_billing_address_1(),
			$order->get_formatted_billing_full_name()
		);

		// Set the recipient address
		$requestBuilder->setRecipientAddress(
			$options['country'] ?? 'DE',
			$options['postal_code'],
			$options['city'],
			$options['street'],
			$options['name']
		);


		$hasWeight = false;

		foreach ( $order->get_items() as $item_id => $item ) {
			$product = $item->get_product();
			$requestBuilder->addParcel( $product->get_weight() * $item->get_quantity() );

			if($product->get_weight() > 0 && $item->get_quantity() > 0) {
				$hasWeight = true;
			}
		}

		if(!$hasWeight) {
			$requestBuilder->addParcel( 0.1 );
		}

		$request = $requestBuilder->create();

		$shipment = $service->createShipment( $request );
		$upload_dir = wp_upload_dir();
		$base_dir = $upload_dir['basedir'] . '/woogls-labels';

		$consignment_id = $shipment->getConsignmentId();

		foreach ( $shipment->getLabels() as $i => $label ) {
			$file_path = "{$base_dir}/{$shipment->getConsignmentId()}-{$i}.pdf";
			file_put_contents( $file_path, $label );

			// Get the URL of the file
			$file_url = admin_url('admin-post.php?action=download_pdf&file_name='.$consignment_id.'-'.$i.'.pdf');

			// Add the URL to the order note
			$order->add_order_note(
				sprintf(
					__( 'GLS Return Shipping Label created. Consignment ID: %s. <a href=\'%s\'>Download label</a>', "woogls-labels" ),
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

	add_action( 'admin_post_woogls_labels_download_return_label', 'handle_woogls_labels_download_return_label' );

	// Handle the download label action
	function handle_woogls_labels_download_label() {
		if(!is_admin()) {
			return;
		}

		$options = get_option( 'woogls_labels_settings' );

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

		$hasWeight = false;

		foreach ( $order->get_items() as $item_id => $item ) {
			$product = $item->get_product();
			$requestBuilder->addParcel( $product->get_weight() * $item->get_quantity() );

			if($product->get_weight() > 0 && $item->get_quantity() > 0) {
				$hasWeight = true;
			}
		}

		if(!$hasWeight) {
			$requestBuilder->addParcel( 0.1 );
		}

		$request = $requestBuilder->create();

		$shipment = $service->createShipment( $request );

		$upload_dir = wp_upload_dir();
		$base_dir = $upload_dir['basedir'] . '/woogls-labels';

		$consignment_id = $shipment->getConsignmentId();

		foreach ( $shipment->getLabels() as $i => $label ) {
			$file_path = "{$base_dir}/{$shipment->getConsignmentId()}-{$i}.pdf";
			file_put_contents( $file_path, $label );

			// Get the URL of the file
			$file_url = admin_url('admin-post.php?action=download_pdf&file_name='.$consignment_id.'-'.$i.'.pdf');

			// Add the URL to the order note
			$order->add_order_note(
				sprintf(
					__( 'GLS Shipping Label created. Consignment ID: %s. <a href=\'%s\'>Download label</a>', "woogls-labels" ),
					$consignment_id,
					$file_url
				)
			);
		}

		wp_redirect( admin_url( 'post.php?post=' . $order_id . '&action=edit' ) );
	}

	add_action( 'admin_post_woogls_labels_download_label', 'handle_woogls_labels_download_label' );

	// Handle the download pdf action
	function handle_woogls_labels_download_pdf() {
		// Check if the user is an admin
		if ( is_admin() ) {
			// Get the file name from the request
			$file_name = $_REQUEST['file_name'];

			// Build the file path
			$upload_dir = wp_upload_dir();
			$base_dir = $upload_dir['basedir'];
			$file_path = "{$base_dir}/woogls-labels/{$file_name}";

			// Check if the file exists
			if ( file_exists($file_path) ) {
				// Set the headers to force download
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename="'.basename($file_path).'"');
				header('Content-Length: ' . filesize($file_path));

				// Read the file and output its contents
				readfile($file_path);

				// Stop the script execution
				exit;
			} else {
				$redirect_url = wp_get_referer() ? wp_get_referer() : admin_url();
				wp_redirect( $redirect_url );

				exit;
			}
		}

		// If the user is not an admin or the file does not exist, redirect to the admin dashboard
		wp_redirect( admin_url() );
		
		exit;
	}

	add_action( 'admin_post_download_pdf', 'handle_woogls_labels_download_pdf' );
}

add_action( 'plugins_loaded', 'woogls_labels_load', 10 );