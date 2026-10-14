<?php
/**
 * get-value — read one field.
 * ARGS:
 *   post_id   (mixed)  required. Numeric ID, "option"/"options", "user_<id>",
 *                       "term_<id>", "comment_<id>", "block_<id>".
 *   selector  (string) required. Field name or field key.
 *   format    (bool)   optional, default true. false = raw stored value.
 *   object    (bool)   optional, default false. true = return get_field_object().
 */

$a = acf_skill_args();
if ( ! isset( $a['selector'] ) || $a['selector'] === '' ) {
	acf_skill_error( 'Missing required arg: selector' );
	return;
}
if ( ! array_key_exists( 'post_id', $a ) ) {
	acf_skill_error( 'Missing required arg: post_id' );
	return;
}
if ( ! function_exists( 'get_field' ) ) {
	acf_skill_error( 'ACF get_field() is not available on this site.' );
	return;
}

$post_id  = $a['post_id'];
$selector = (string) $a['selector'];
$format   = array_key_exists( 'format', $a ) ? (bool) $a['format'] : true;

if ( ! empty( $a['object'] ) ) {
	$obj = get_field_object( $selector, $post_id, $format );
	acf_skill_return( array( 'selector' => $selector, 'post_id' => $post_id, 'field_object' => $obj ) );
	return;
}

$value = get_field( $selector, $post_id, $format );
acf_skill_return( array(
	'selector' => $selector,
	'post_id'  => $post_id,
	'format'   => $format,
	'value'    => $value,
) );
