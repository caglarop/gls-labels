<?php
/*
Plugin Name: GLS Labels
Description: This is a WooCommerce plugin to create GLS shipping and return labels
Version: 1.0.3
Author: Candan Tombas
Author URI: https://devantia.com
Text Domain: gls-labels
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Function to create the shipping label
function create_gls_shipment($order, $options, $isReturn) {
    $userId = $options['user_id'] ?? "";
    $password = $options['password'] ?? "";
    $shipperAccount = $options["shipper_account"] ?? "";

    $logger = new \Psr\Log\NullLogger();
    $serviceFactory = new \GlsGroup\Sdk\ParcelProcessing\Service\ServiceFactory();
    $service = $serviceFactory->createShipmentService( $userId, $password, $logger, false );

    $requestBuilder = $isReturn ? new \GlsGroup\Sdk\ParcelProcessing\RequestBuilder\ReturnShipmentRequestBuilder() : new \GlsGroup\Sdk\ParcelProcessing\RequestBuilder\ShipmentRequestBuilder();

    // Set the shipper account
    $requestBuilder->setShipperAccount( $shipperAccount );

    if ($isReturn) {
		$requestBuilder->setShipperAddress(
			$order->get_shipping_country() ?? "DE",
			$order->get_billing_postcode(),
			$order->get_billing_city(),
			$order->get_billing_address_1(),
			$order->get_formatted_billing_full_name()
		);

		$requestBuilder->setRecipientAddress(
			$options['country'] ?? 'DE',
			$options['postal_code'],
			$options['city'],
			$options['street'],
			$options['name']
		);
    } else {
        $requestBuilder->setShipperAddress(
			$options['country'] ?? 'DE',
			$options['postal_code'],
			$options['city'],
			$options['street'],
			$options['name']
        );

		$requestBuilder->setRecipientAddress(
            $order->get_shipping_country() ?? "DE",
            $order->get_billing_postcode(),
            $order->get_billing_city(),
            $order->get_billing_address_1(),
            $order->get_formatted_billing_full_name()
		);
	}

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

    return $service->createShipment( $request );
}

// Handle the download label action
function handle_gls_labels_download($isReturn) {
    if(!is_admin()) {
        return;
    }

    $options = get_option( 'gls_labels_settings' );
    $order_id = $_GET['order_id'];
    $order = wc_get_order( $order_id );

    $shipment = create_gls_shipment($order, $options, $isReturn);

    $upload_dir = wp_upload_dir();
    $base_dir = $upload_dir['basedir'] . '/gls-labels';

    $consignment_id = $shipment->getConsignmentId();

    foreach ( $shipment->getLabels() as $i => $label ) {
        $file_path = "{$base_dir}/{$shipment->getConsignmentId()}-{$i}.pdf";
        file_put_contents( $file_path, $label );

        // Get the URL of the file
        $file_url = admin_url('admin-post.php?action=download_pdf&file_name='.$consignment_id.'-'.$i.'.pdf');

        // Add the URL to the order note
       	$labelType = $isReturn ? __( 'GLS Return Shipping Label created. Consignment ID: %s. <a href=\'%s\'>Download label</a>', "gls-labels" ) : __( 'GLS Shipping Label created. Consignment ID: %s. <a href=\'%s\'>Download label</a>', "gls-labels" );

		$order->add_order_note(
			sprintf(
				$labelType,
				$consignment_id,
				$file_url
			)
		);
    }

    wp_redirect( admin_url( 'post.php?post=' . $order_id . '&action=edit' ) );
}

// Check if the plugin is configured
function isPluginConfigured() {
    // check all options are set
    $options = get_option('gls_labels_settings');
    $requiredOptions = ['user_id', 'password', 'shipper_account', 'country', 'postal_code', 'city', 'street', 'name'];

    foreach ($requiredOptions as $option) {
        if (!isset($options[$option]) || empty($options[$option])) {
            return false;
        }
    }

    return true;
}

// Create the plugin directory and .htaccess file
function create_gls_labels_directory_and_htaccess() {
	$upload_dir = wp_upload_dir();
	$base_dir = $upload_dir['basedir'];
	$gls_labels_dir = "{$base_dir}/gls-labels";

	// Check if the directory exists, if not, create it
	if ( ! file_exists( $gls_labels_dir ) ) {
		mkdir( $gls_labels_dir, 0755, true );
	}

	// Check if the .htaccess file exists, if not, create it
	$htaccess_file = "{$gls_labels_dir}/.htaccess";
		
	if ( ! file_exists( $htaccess_file ) ) {
		$htaccess_content = "deny from all";
		file_put_contents( $htaccess_file, $htaccess_content );
	}
}

function gls_labels_load_textdomain() {
	load_plugin_textdomain( 'gls-labels', false, basename( dirname( __FILE__ ) ) . '/languages/' );

	create_gls_labels_directory_and_htaccess();
}

add_action( 'init', 'gls_labels_load_textdomain' );

function gls_labels_load() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	function gls_labels_styles() {
		wp_enqueue_style('gls-labels-styles', plugins_url('styles.css', __FILE__));
	}

	add_action('admin_enqueue_scripts', 'gls_labels_styles');

	if ( ! defined( 'GLS_LABELS_DIR' ) ) {
		define( 'GLS_LABELS_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
	}

	require_once( GLS_LABELS_DIR . '/vendor/autoload.php' );


	// Add the settings page
	add_action( 'admin_menu', function () {
		add_options_page( __( "GLS Settings", "gls-labels" ), __( "GLS Settings", "gls-labels" ), 'manage_options', 'gls-labels', function () {
			?>
			<div class="wrap">
				<h1>
					<?php echo __( "GLS Settings", "gls-labels" ); ?>
				</h1>
				<form method="post" action="options.php">
					<?php
					settings_fields( 'gls-labels' );
					do_settings_sections( 'gls-labels' );
					submit_button();
					?>
				</form>
			</div>
			<?php
		} );
	} );

	// Add the settings
	add_action('admin_init', function () {
		register_setting('gls-labels', 'gls_labels_settings');

		$settings = [
			'gls_labels_auth_section' => [
				'title' => __('Auth', "gls-labels"),
				'fields' => [
					'user_id' => ['title' => __('User ID', "gls-labels"), 'type' => 'text'],
					'password' => ['title' => __('Password', "gls-labels"), 'type' => 'password'],
				],
			],
			'gls_labels_shipper_account_section' => [
				'title' => __('Shipper Number', "gls-labels"),
				'fields' => [
					'shipper_account' => ['title' => __('Shipper Number', "gls-labels"), 'type' => 'text'],
				],
			],
			'gls_labels_shipper_address_section' => [
				'title' => __('Shipper Address', "gls-labels"),
				'fields' => [
					'country' => ['title' => __('Country', "gls-labels"), 'type' => 'select', 'options' => [
						'DE' => __('Germany'),
						'AT' => __('Austria'),
						'BE' => __('Belgium'),
						'BG' => __('Bulgaria'),
						'CZ' => __('Czech Republic'),
						'DK' => __('Denmark'),
						'EE' => __('Estonia'),
						'ES' => __('Spain'),
						'FI' => __('Finland'),
						'FR' => __('France'),
						'IE' => __('Ireland'),
						'HR' => __('Croatia'),
						'HU' => __('Hungary'),
						'IT' => __('Italy'),
						'LT' => __('Lithuania'),
						'LU' => __('Luxembourg'),
						'LV' => __('Latvia'),
						'MC' => __('Monaco'),
						'NL' => __('Netherlands'),
						'PL' => __('Poland'),
						'PT' => __('Portugal'),
						'RO' => __('Romania'),
						'SE' => __('Sweden'),
						'SI' => __('Slovenia'),
						'SK' => __('Slovakia'),
					]],
					'postal_code' => ['title' => __('Postal Code', "gls-labels"), 'type' => 'text'],
					'city' => ['title' => __('City', "gls-labels"), 'type' => 'text'],
					'street' => ['title' => __('Street', "gls-labels"), 'type' => 'text'],
					'name' => ['title' => __('Name', "gls-labels"), 'type' => 'text'],
				],
			],
		];

		foreach ($settings as $section => $data) {
			add_settings_section($section, $data['title'], null, 'gls-labels');

			foreach ($data['fields'] as $field => $field_data) {
				add_settings_field('gls_labels_' . $field, $field_data['title'], function () use ($field, $field_data) {
					$options = get_option('gls_labels_settings');
					if ($field_data['type'] === 'select') {
						echo '<select id="gls_labels_' . $field . '" name="gls_labels_settings[' . $field . ']">';
						foreach ($field_data['options'] as $option_value => $option_title) {
							$selected = $options[$field] === $option_value ? ' selected' : '';
							echo '<option value="' . esc_attr($option_value) . '"' . $selected . '>' . esc_html($option_title) . '</option>';
						}
						echo '</select>';
					} else {
						echo '<input type="' . $field_data['type'] . '" id="gls_labels_' . $field . '" name="gls_labels_settings[' . $field . ']" value="' . esc_attr($options[$field] ?? "") . '">';
					}
				}, 'gls-labels', $section);
			}
		}
	});

	// Add the meta box callback function
	function gls_labels_meta_box_callback( $post ) {

		if(!isPluginConfigured()) {
			echo '<div class="gls-labels gls-labels-grid">';
			echo '<div class="form-field"><a class="button" href="' . admin_url( 'options-general.php?page=gls-labels' ) . '" id="gls-labels-configure">' . __( "Configure GLS Labels", "gls-labels" ) . '</a></div>';
			echo '</div>';
		} else {
			echo '<div class="gls-labels gls-labels-grid">';
    
			echo '<div class="form-field"><a class="button" href="' . admin_url( 'admin-post.php?action=gls_labels_download_label&order_id=' . $post->ID ) . '" id="gls-labels-create-label">' . __( "Create Shipping Label", "gls-labels" ) . '</a></div>';
			echo '<div class="form-field"><a class="button" href="' . admin_url( 'admin-post.php?action=gls_labels_download_return_label&order_id=' . $post->ID ) . '" id="gls-labels-create-return-label">' . __( "Create Return Label", "gls-labels" ) . '</a></div>';
		
			echo '</div>';
		}
	}

	// Add the meta box
	function gls_labels_order_meta_box() {
		add_meta_box(
			'gls-labels-meta-box', // ID
			__( 'GLS Shipping Label', "gls-labels" ), // Title
			'gls_labels_meta_box_callback', // Callback
			'woocommerce_page_wc-orders', // Post Type (page, post, etc.)
			'side', // Context (normal, advanced, side)
			'high' // Priority (default, low, high, core)
		);
	}

	add_action( 'add_meta_boxes', 'gls_labels_order_meta_box' );

	function handle_gls_labels_download_return_label() {
		handle_gls_labels_download(true);
	}

	add_action( 'admin_post_gls_labels_download_return_label', 'handle_gls_labels_download_return_label' );

	function handle_gls_labels_download_label() {
		handle_gls_labels_download(false);
	}

	add_action( 'admin_post_gls_labels_download_label', 'handle_gls_labels_download_label' );

	// Handle the download pdf action
	function handle_gls_labels_download_pdf() {
		// Check if the user is an admin
		if ( is_admin() ) {
			// Get the file name from the request
			$file_name = $_REQUEST['file_name'];

			// Build the file path
			$upload_dir = wp_upload_dir();
			$base_dir = $upload_dir['basedir'];
			$file_path = "{$base_dir}/gls-labels/{$file_name}";

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

	add_action( 'admin_post_download_pdf', 'handle_gls_labels_download_pdf' );
}

add_action( 'plugins_loaded', 'gls_labels_load', 10 );