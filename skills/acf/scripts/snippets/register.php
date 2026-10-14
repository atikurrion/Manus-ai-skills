<?php
/**
 * register — validate and emit code to register a field group, options page,
 * or ACF Block. Registration in ACF is a CODE concern (hooked on acf/init), so
 * the primary output is a ready-to-paste PHP block for the user's theme/plugin.
 * A field group can additionally be persisted into the DB.
 *
 * ARGS (any combination):
 *   field_group  (object) array accepted by acf_add_local_field_group()
 *   options_page (object) array accepted by acf_add_options_page() (Pro)
 *   block        (object) array accepted by acf_register_block_type() (Pro)
 *   persist      (string) "db" -> also acf_update_field_group($field_group) (DESTRUCTIVE)
 *                         "none" (default) -> validate + emit code only
 */

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
	acf_skill_error( 'ACF is not available on this site (acf_add_local_field_group() undefined). Run verify-env first.' );
	return;
}

$a       = acf_skill_args();
$persist = isset( $a['persist'] ) ? (string) $a['persist'] : 'none';
$report  = array();

$export = function ( $v ) {
	$s = var_export( $v, true );
	$s = preg_replace( '/=>\s*\n\s*array \(/', '=> array(', $s );
	return str_replace( 'array (', 'array(', $s );
};

$lines = array( "add_action( 'acf/init', function () {" );

if ( ! empty( $a['field_group'] ) && is_array( $a['field_group'] ) ) {
	$fg = $a['field_group'];

	// acf_validate_field_group() returns the NORMALIZED field group array
	// (or false on hard failure). Resolve it into a real validity boolean
	// plus any structured errors ACF accumulated.
	if ( function_exists( 'acf_validate_field_group' ) ) {
		$normalized = acf_validate_field_group( $fg );
		$report['field_group_normalized'] = is_array( $normalized ) ? $normalized : null;
		$report['field_group_valid']      = is_array( $normalized );
	}
	if ( function_exists( 'acf_get_validation_errors' ) ) {
		$errors = acf_get_validation_errors();
		if ( ! empty( $errors ) ) {
			$report['field_group_errors'] = $errors;
			$report['field_group_valid']  = false;
		}
		// Reset so we don't leak errors into a later op in the same request.
		if ( function_exists( 'acf_reset_validation_errors' ) ) acf_reset_validation_errors();
	}

	$lines[] = "\tacf_add_local_field_group( " . $export( $fg ) . " );";

	if ( $persist === 'db' ) {
		$validation_attempted = array_key_exists( 'field_group_valid', $report );
		$validation_failed    = $validation_attempted && empty( $report['field_group_valid'] );
		if ( $validation_failed ) {
			$report['persisted_field_group']  = false;
			$report['persist_skipped_reason'] = 'Validation failed; refusing to persist.';
		} elseif ( ! function_exists( 'acf_update_field_group' ) ) {
			$report['persisted_field_group']  = false;
			$report['persist_skipped_reason'] = 'acf_update_field_group() unavailable on this ACF version.';
		} else {
			$saved = acf_update_field_group( $fg );
			$report['persisted_field_group'] = is_array( $saved ) ? ( isset( $saved['ID'] ) ? $saved['ID'] : true ) : false;
		}
	}
}

if ( ! empty( $a['options_page'] ) && is_array( $a['options_page'] ) ) {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		$report['options_page_warning'] = 'acf_add_options_page() unavailable (requires ACF Pro).';
	}
	$lines[] = "\tif ( function_exists( 'acf_add_options_page' ) ) {";
	$lines[] = "\t\tacf_add_options_page( " . $export( $a['options_page'] ) . " );";
	$lines[] = "\t}";
}

if ( ! empty( $a['block'] ) && is_array( $a['block'] ) ) {
	if ( ! function_exists( 'acf_register_block_type' ) ) {
		$report['block_warning'] = 'acf_register_block_type() unavailable (requires ACF Pro).';
	}
	$lines[] = "\tif ( function_exists( 'acf_register_block_type' ) ) {";
	$lines[] = "\t\tacf_register_block_type( " . $export( $a['block'] ) . " );";
	$lines[] = "\t}";
}

$lines[] = "} );";

acf_skill_return( array(
	'validation'    => $report,
	'persist'       => $persist,
	'generated_php' => "<?php\n" . implode( "\n", $lines ) . "\n",
	'note'          => 'Place generated_php in a theme functions.php or a plugin. Local field groups are in-memory per request; "persist":"db" writes the field group to the database (destructive — see SKILL.md §Step 4).',
) );
