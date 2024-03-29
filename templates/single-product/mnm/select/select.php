<?php
/**
 * Mix and Match Select dropdown
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/mnm/select/select.php.
 *
 * HOWEVER, on occasion WooCommerce Mix and Match will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  Kathy Darling
 * @package WooCommerce Mix and Match/Templates
 * @since   1.0.0
 * @version 2.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ){
	exit;
}

global $product;
?>
<p>
	<label for="_mnm_select[<?php echo esc_attr( $counter );?>]">
		<?php printf( __( 'Option #%d', 'wc-mnm-select-layout' ), $counter ); ?>
			<?php if( $required ) { ?>
				<span class="required"><?php esc_html_e( 'Required', 'wc-mnm-select-layout' ); ?></span>
			<?php } ?>
	</label>
	<select name="_mnm_select[<?php echo esc_attr( $counter );?>]" class="mnm_select" <?php echo $required ? 'required' : ''; ?> data-default="<?php echo esc_attr( $default );?>" >
		<option value=""><?php esc_html_e( 'Make a selection', 'wc-mnm-select-layout' ); ?></option>

		<?php

		$default = isset( $_REQUEST[ '_mnm_select' ] ) && isset( $_REQUEST[ '_mnm_select' ][$counter] ) ? intval( $_REQUEST[ '_mnm_select'][$counter] ) : $default;

		foreach ( $product->get_child_items() as $child_item ) {

			$child_product = $child_item->get_product();
			$child_name    = $child_item->is_priced_individually() ? sprintf( esc_html__( '%1$s (%2$s)', 'wc-mnm-select-layout' ), $child_product->get_name(), wc_price( $child_product->get_price() ) ) : $child_product->get_name();
	
			printf( '<option value="%d" %s>%s</option>', esc_attr( $child_product->get_id() ), selected( $child_product->get_id(), $default, false ), $child_name );
		}
		?>
	</select>

</p>