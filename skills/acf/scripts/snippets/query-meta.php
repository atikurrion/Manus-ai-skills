<?php
/**
 * query-meta — find posts by ACF field values (ACF stores values as post meta,
 * so this is a standard WP_Query meta_query).
 * ARGS:
 *   post_type        (string|array) default "post"
 *   posts_per_page   (int)          default 20 (use -1 for all)
 *   meta_query       (array)        WP meta_query, e.g.
 *       [{"key":"featured","value":"1","compare":"="}]
 *     or nested with {"relation":"AND", ...}. For repeater sub-fields, ACF
 *     stores keys like "items_0_title"; use compare "LIKE" or "REGEXP".
 *   return_fields    (array)        optional ACF field names to include per post
 */

if ( ! class_exists( 'WP_Query' ) ) {
	acf_skill_error( 'WordPress core (WP_Query) is not loaded. Run this through WP-CLI or Novamira against a WP install.' );
	return;
}

$a = acf_skill_args();
if ( empty( $a['meta_query'] ) || ! is_array( $a['meta_query'] ) ) {
	acf_skill_error( 'Required arg: meta_query (array).' );
	return;
}

$args = array(
	'post_type'              => isset( $a['post_type'] ) ? $a['post_type'] : 'post',
	'posts_per_page'         => isset( $a['posts_per_page'] ) ? (int) $a['posts_per_page'] : 20,
	'post_status'            => isset( $a['post_status'] ) ? $a['post_status'] : 'any',
	'meta_query'             => $a['meta_query'],
	'no_found_rows'          => true,
	'update_post_term_cache' => false,
	'fields'                 => 'ids',
);

$q   = new WP_Query( $args );
$out = array();
foreach ( $q->posts as $id ) {
	$row = array( 'ID' => (int) $id, 'post_title' => get_the_title( $id ), 'post_type' => get_post_type( $id ) );
	if ( ! empty( $a['return_fields'] ) && is_array( $a['return_fields'] ) && function_exists( 'get_field' ) ) {
		$row['fields'] = array();
		foreach ( $a['return_fields'] as $fname ) {
			$row['fields'][ (string) $fname ] = get_field( (string) $fname, $id );
		}
	}
	$out[] = $row;
}

acf_skill_return( array( 'count' => count( $out ), 'posts' => $out ) );
