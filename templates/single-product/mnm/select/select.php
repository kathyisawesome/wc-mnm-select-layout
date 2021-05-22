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
 * @since   1.0.0-beta
 * @version 1.0.0-beta-1
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
				<span class="required"><?php _e( 'Required', 'wc-mnm-select-layout' ); ?></span>
			<?php } ?>
	</label>
	<select name="_mnm_select[<?php echo esc_attr( $counter );?>]" class="mnm_select" <?php echo $required ? 'required' : ''; ?> data-default="<?php echo esc_attr( $default );?>" >
		<option value=""><?php _e( 'Make a selection', 'wc-mnm-select-layout' ); ?></option>

		<?php

		$default = isset( $_REQUEST[ '_mnm_select' ] ) && isset( $_REQUEST[ '_mnm_select' ][$counter] ) ? intval( $_REQUEST[ '_mnm_select'][$counter] ) : $default;

		foreach ( $product->get_available_children() as $child ) {
			printf( '<option value="%d" %s>%s</option>', esc_attr( $child->get_id() ), selected( $child->get_id(), $default, false ), $child->get_name() );
		}
		?>
	</select>

</p>