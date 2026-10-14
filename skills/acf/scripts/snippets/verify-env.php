<?php
/**
 * verify-env — introspect the LIVE ACF install. Run this before any operation
 * and adapt to what it reports (version, Pro, options pages, field types).
 */

if ( ! function_exists( 'acf_get_setting' ) ) {
	acf_skill_return( array(
		'acf_active' => false,
		'message'    => 'ACF is not active on this site (acf_get_setting() undefined).',
	) );
	return;
}

$is_pro = function_exists( 'acf_is_pro' ) ? (bool) acf_is_pro() : ( defined( 'ACF_PRO' ) && ACF_PRO );

$types = array();
if ( function_exists( 'acf_get_field_types_info' ) ) {
	foreach ( (array) acf_get_field_types_info() as $name => $info ) {
		$types[] = array(
			'name'     => is_array( $info ) && isset( $info['name'] ) ? $info['name'] : $name,
			'label'    => is_array( $info ) && isset( $info['label'] ) ? $info['label'] : '',
			'category' => is_array( $info ) && isset( $info['category'] ) ? $info['category'] : '',
			'pro'      => is_array( $info ) && ! empty( $info['pro'] ),
		);
	}
}

acf_skill_return( array(
	'acf_active'              => true,
	'version'                 => acf_get_setting( 'version' ),
	'major_version'           => defined( 'ACF_MAJOR_VERSION' ) ? ACF_MAJOR_VERSION : null,
	'field_api_version'       => defined( 'ACF_FIELD_API_VERSION' ) ? ACF_FIELD_API_VERSION : null,
	'is_pro'                  => $is_pro,
	'options_pages_available' => function_exists( 'acf_add_options_page' ),
	'blocks_available'        => function_exists( 'acf_register_block_type' ),
	'local_json_save_path'    => acf_get_setting( 'save_json' ),
	'field_types'             => $types,
	'wp_version'              => get_bloginfo( 'version' ),
) );
