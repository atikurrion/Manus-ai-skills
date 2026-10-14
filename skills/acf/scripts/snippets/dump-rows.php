<?php
/**
 * dump-rows — return the full nested structure of a repeater / flexible
 * content / clone / group field, so it can be traversed from JSON.
 * ARGS:
 *   post_id  (mixed)  required.
 *   selector (string) required. Top-level field name or key.
 *
 * Returns both the formatted value and the raw stored value. For Flexible
 * Content, each row carries its "acf_fc_layout". get_field() recursively
 * resolves nested repeaters/clones, so the returned tree is complete.
 */

$a = acf_skill_args();
if ( ! isset( $a['selector'] ) || $a['selector'] === '' || ! array_key_exists( 'post_id', $a ) ) {
	acf_skill_error( 'Required args: post_id, selector' );
	return;
}
if ( ! function_exists( 'get_field' ) || ! function_exists( 'get_field_object' ) ) {
	acf_skill_error( 'ACF is not available on this site.' );
	return;
}

$post_id  = $a['post_id'];
$selector = (string) $a['selector'];

$obj = get_field_object( $selector, $post_id, false, false );

acf_skill_return( array(
	'selector'       => $selector,
	'post_id'        => $post_id,
	'field_type'     => is_array( $obj ) && isset( $obj['type'] ) ? $obj['type'] : null,
	'formatted'      => get_field( $selector, $post_id, true ),
	'raw'            => get_field( $selector, $post_id, false ),
) );
