<?php
/**
 * Plugin Name: WooCommerce Mix and Match - Select Layout
 * Plugin URI: http://www.woocommerce.com/products/woocommerce-mix-and-match-products/
 * Description: Fill Mix and Match container by dropdown select inputs
 * Version: 2.0.1
 * Author: Kathy Darling
 * Author URI: http://kathyisawesome.com/
 * Developer: Kathy Darling
 * Developer URI: http://kathyisawesome.com/
 * Text Domain: wc-mnm-select-layout
 * Domain Path: /languages
 *
 * Copyright: © 2020 Kathy Darling
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */



/**
 * The Main WC_MNM_Select_Layout class
 **/
if ( ! class_exists( 'WC_MNM_Select_Layout' ) ) :

class WC_MNM_Select_Layout {

	/**
	 * constants
	 */
	const VERSION = '2.0.1';

	/**
	 * WC_MNM_Select_Layout Constructor
	 *
	 * @access 	public
     * @return 	WC_MNM_Select_Layout
	 */
	public static function init() {

		// Quietly quit if MNM is not active.
		if ( ! function_exists( 'wc_mix_and_match' ) ) {
			return false;
		}

		// Load translation files.
		add_action( 'init', array( __CLASS__, 'load_plugin_textdomain' ) );

		// Add extra layout.
		add_filter( 'wc_mnm_supported_layouts', array( __CLASS__, 'add_layout' ) );	
		add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'process_meta' ), 20 );

		// Display the selects on the front end.
		add_action( 'wc_mnm_content_loop', array( __CLASS__, 'switch_mnm_content_loop' ), 1 );

		// Print custom styles.
		add_action( 'wp_print_styles', array( __CLASS__, 'print_styles' ) );

		// Register Scripts.
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_scripts' ) );

		// Display Scripts.
		add_action( 'woocommerce_mix-and-match_add_to_cart', array( __CLASS__, 'load_scripts' ) );

		// QuickView support.
		add_action( 'wc_quick_view_enqueue_scripts', array( __CLASS__, 'load_scripts' ) );

		// Add to cart validation.
		add_filter( 'wc_mnm_get_posted_container_configuration', array( __CLASS__, 'get_posted_container_configuration' ), 10, 2 );
		add_filter( 'wc_mnm_get_posted_container_form_data', array( __CLASS__, 'rebuild_posted_container_form_data' ), 10, 3 );
		
    }


	/*-----------------------------------------------------------------------------------*/
	/* Localization */
	/*-----------------------------------------------------------------------------------*/


	/**
	 * Make the plugin translation ready
	 *
	 * @return void
	 */
	public static function load_plugin_textdomain() {
		load_plugin_textdomain( 'wc-mnm-select-layout' , false , dirname( plugin_basename( __FILE__ ) ) .  '/languages/' );
	}

	/*-----------------------------------------------------------------------------------*/
	/* Admin */
	/*-----------------------------------------------------------------------------------*/


	/**
	 * Adds the container max weight option writepanel options.
	 *
	 * @param int $post_id
	 * @param  WC_Product_Mix_and_Match  $mnm_product_object
	 */
	public static function add_layout( $layouts ) {
		$layouts['select'] = array(
			'label'       => esc_html__( 'Dropdown Selects', 'wc-mnm-select-layout' ),
			'description' => esc_html__( 'The number of slots are each displayed as a dropdown.', 'wc-mnm-select-layout' ),
			'image'       => self::plugin_url() . '/assets/images/layout-select.svg',
			'mb_display'  => true,
		);
		return $layouts;
	}

	/**
	 * Saves the new meta field.
	 *
	 * @param  WC_Product_Mix_and_Match  $mnm_product_object
	 */
	public static function process_meta( $product ) {

		if ( $product->is_type( 'mix-and-match' ) ) {

			// If select Layout do not support null/0 max container size.
			if ( 'select' === $product->get_layout( 'edit' ) && ! $product->get_max_container_size( 'edit' ) ) {
				WC_Admin_Meta_Boxes::add_error( __( 'The Mix and Match "select" layout requires a maximum container size. Please set a non-zero amount.', 'wc-mnm-select-layout' ) );
			}

		}

	}


	/*-----------------------------------------------------------------------------------*/
	/* Front End Display */
	/*-----------------------------------------------------------------------------------*/


	/**
	 * Remove the default content loop and replace
	 *
	 * @param WC_Mix_and_Match $product the container product
	 */
	public static function switch_mnm_content_loop( $product ) {
		if ( 'select' === $product->get_layout( 'edit' ) ) {
			remove_action( 'wc_mnm_content_loop', 'wc_mnm_content_loop' );
			add_action( 'wc_mnm_content_loop', array( __CLASS__, 'display_selects' ) );
		}
	}

	/**
	 * The child contents loop.
	 *
	 * @param WC_Mix_And_Match $product the container product
	 */
	public static function display_selects( $product ) {

		if( $product->has_child_items() ) {

			$counter = 1;
			$min     = $product->get_min_container_size();
			$max     = $product->get_max_container_size();

			if( $max > 0 ) {

				while ( $counter <= $max ) {
					
					wc_get_template(
						'single-product/mnm/select/select.php',
						array(
							'container' => $product,
							'counter'	=> $counter,
							'required'  => $counter <= $min,
							'default'   => apply_filters( 'wc_mnm_select_default', '', $counter, $product ),
						),
						'',
						self::plugin_path() . '/templates/'
					);

					$counter++;
				}

			}

		}

	}

	/**
	 * Print some very minimal styles.
	 */
	public static function print_styles() { ?>

		<style>
			.mnm_form.layout_select label {
				font-weight: bold;
				display: block;
			}
			.mnm_form.layout_select label .required {
				font-size: 0;
			}
			.mnm_form.layout_select label .required:after {
				content: '*';
				color: red;
				font-size: initial;
			}
		</style>

	<?php

	}

	/*-----------------------------------------------------------------------------------*/
	/* Cart Functions */
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Build container configuration array from posted data. Array example:
	 *
	 * @param array $posted_config
	 * 	  = array(
	 *        134 => array(                             // ID of child item.
	 *            'mnm_child_id'      => 134,           // ID of child item.
	 *            'product_id'        => 15,            // ID of child product.
	 *            'quantity'          => 2,             // Qty of child product, will fall back to min.
	 *            'variation_id'      => 43             // ID of chosen variation, if applicable.
	 *            'variation'		  => array( 'color' => 'blue' ) // Attributes of chosen variation.
	 *        )
	 *    );
	 * @param  mixed  $product
	 * @return array
	 */
	public static function get_posted_container_configuration( $posted_config, $product ) {

		if ( is_numeric( $product ) ) {
			$product = wc_get_product( $product );
		}

		if ( is_object( $product ) && $product->is_type( 'mix-and-match' ) && 'select' === $product->get_layout() ) {

			/**
			 * Choose between $_POST or $_GET for grabbing data.
			 * We will not rely on $_REQUEST because checkbox names may not exist in $_POST but they may well exist in $_GET, for instance when editing a container from the cart.
			 */
			$posted_data = $_POST;

			if ( empty( $_POST[ 'add-to-cart' ] ) && ! empty( $_GET[ 'add-to-cart' ] ) ) {
				$posted_data = $_GET;
			}

			/**
			 * Only reset $posted_config to empty array IF the _mnm_select key is posted. 
			 * get_posted_container_configuration() is now used to revalidate cart item on `woocommerce_check_cart_items`. and so we dont want to reset the configuration when the cart is being revalidated.
			 */
			if ( isset( $posted_data[ '_mnm_select' ] ) ) {

				$posted_config = array();

				if ( $product->has_child_items() ) {

					$counted = array_count_values( $posted_data[ '_mnm_select' ] );

					foreach ( $product->get_child_items() as $child_item ) {

						$child_product    = $child_item->get_product();
						$child_product_id = $child_product->get_id();

						// Check that a product has been selected.
						if ( array_key_exists( $child_product_id, $counted ) ) {
							$child_item_quantity = intval( $counted[ $child_product_id ] );
						} else {
							continue;
						}

						$posted_config[ $child_product_id ] = array();

						$parent_id = $child_product->get_parent_id();

						$posted_config[ $child_product_id ][ 'child_item_id' ] = $child_item->get_child_item_id();
						$posted_config[ $child_product_id ][ 'mnm_child_id' ]  = $child_product_id;
						$posted_config[ $child_product_id ][ 'product_id' ]    = $parent_id > 0 ? $parent_id              : $child_product->get_id();
						$posted_config[ $child_product_id ][ 'variation_id' ]  = $parent_id > 0 ? $child_product->get_id(): 0;
						$posted_config[ $child_product_id ][ 'quantity' ]      = $child_item_quantity;
						$posted_config[ $child_product_id ][ 'variation' ]     = $parent_id > 0 ? $child_product->get_variation_attributes() : array();

					}

				}
			}
		}

		return $posted_config;
	}

	/**
	 * Rebuild container configuration array from posted data. Array example:
	 *
	 * @param array $posted_config
	 * 	  = array(
	 *        134 => array(                             // ID of child item.
	 *            'mnm_child_id'      => 134,           // ID of child item.
	 *            'product_id'        => 15,            // ID of child product.
	 *            'quantity'          => 2,             // Qty of child product, will fall back to min.
	 *            'variation_id'      => 43             // ID of chosen variation, if applicable.
	 *            'variation'		  => array( 'color' => 'blue' ) // Attributes of chosen variation.
	 *        )
	 *    );
	 * @param  mixed  $product
	 * @return array
	 */
	public static function rebuild_posted_container_form_data( $form_data, $configuration, $container ) {

		// Return the array as _mnm_select = array() if $container is passed.
		if ( $container instanceof WC_Product_Mix_and_Match ) {
			
			if ( 'select' === $container->get_layout() ) {

				$select_form_data = array();
				$counter = 1;

				foreach ( $configuration as $child_id => $child_config ) {

					$quantity = $form_data[$child_id] = isset( $child_config['quantity'] ) ? intval( $child_config['quantity'] ) : 0;

					for ( $x = 1; $x <= $quantity; $x++ ) {
					    $select_form_data['_mnm_select'][$counter] = $child_id;
						$counter++;
					}

				}
				$form_data = $select_form_data;

			}

		}

		return $form_data;

	}



	/*-----------------------------------------------------------------------------------*/
	/* Scripts and Styles */
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Register scripts
	 *
	 * @return void
	 */
	public static function register_scripts() {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'wc-mnm-add-to-cart-select-layout', self::plugin_url() . '/assets/js/frontend/wc-mnm-add-to-cart-select-layout' . $suffix . '.js', array( 'wc-add-to-cart-mnm' ), WC_MNM_Select_Layout::VERSION, true );

	}

	/**
	 * Load the script anywhere the MNN add to cart button is displayed
	 * @return void
	 */
	public static function load_scripts() {
		wp_enqueue_script( 'wc-mnm-add-to-cart-select-layout' );
	}

	/*-----------------------------------------------------------------------------------*/
	/* Helpers */
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Plugin URL.
	 *
	 * @return string
	 */
	public static function plugin_url() {
		return plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename(__FILE__) );
	}

	/**
	 * Plugin path.
	 *
	 * @return string
	 */
	public static function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

} //end class: do not remove or there will be no more guacamole for you

endif; // end class_exists check

// Launch the whole plugin.
add_action( 'plugins_loaded', array( 'WC_MNM_Select_Layout', 'init' ), 20 );
