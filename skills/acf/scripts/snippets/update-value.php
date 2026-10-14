<?php
/**
 * update-value — write a field value or mutate repeater/flexible rows.
 *
 * ALL ops here mutate the live database. Default to a dry-run preview and
 * confirm with the user before executing for real (see SKILL.md §Step 4).
 *
 * ARGS:
 *   post_id  (mixed)   required.
 *   op       (string)  required, one of:
 *     update_field      : selector, value
 *     delete_field      : selector
 *     add_row           : selector, row(object)            -> appends, returns new count
 *     update_row        : selector, index(int,1-based), row
 *     delete_row        : selector, index
 *     add_sub_row       : selector(array [parent,row_i,sub]), row
 *     update_sub_row    : selector(array [parent,row_i,sub]), index, row
 *     delete_sub_row    : selector(array [parent,row_i,sub]), index
 *     update_sub_field  : selector(array [parent,row_i,sub]), value
 *     delete_sub_field  : selector(array [parent,row_i,sub])
 *   dry_run  (bool)    optional, default false. When true: NO mutation; returns
 *                       { current_value, would_call: { fn, args... } } so the
 *                       caller can preview and confirm before re-running.
 *
 * Notes: for a never-saved field, pass its field KEY (field_xxx) as selector so
 * ACF stores the field reference. Flexible-content rows must include
 * "acf_fc_layout". Sub-row ops use the ARRAY selector form to avoid needing an
 * active have_rows() loop.
 */

if ( ! function_exists( 'update_field' ) ) {
	acf_skill_error( 'ACF is not available on this site (update_field() undefined). Run verify-env first.' );
	return;
}

$a       = acf_skill_args();
$op      = isset( $a['op'] ) ? (string) $a['op'] : '';
$dry_run = ! empty( $a['dry_run'] );

if ( ! array_key_exists( 'post_id', $a ) || $op === '' ) {
	acf_skill_error( 'Required args: post_id, op (see SKILL.md for ops list).' );
	return;
}

$pid = $a['post_id'];
$sel = isset( $a['selector'] ) ? $a['selector'] : null;
$idx = isset( $a['index'] ) ? (int) $a['index'] : 1;
$row = isset( $a['row'] ) ? $a['row'] : false;
$val = array_key_exists( 'value', $a ) ? $a['value'] : null;

/**
 * Validate required args for an op. Returns true on success; on failure emits
 * a single error block and returns false — the caller MUST short-circuit to
 * avoid double-emitting result blocks.
 */
$need = function ( $cond, $msg = 'Missing args for op.' ) {
	if ( ! $cond ) { acf_skill_error( $msg ); return false; }
	return true;
};

/**
 * For a single-selector op, fetch the current value cheaply so dry_run can
 * show a real before/after. For array selectors (sub-row ops) we skip the
 * fetch (it would need an active loop); the preview reports the call shape.
 */
$peek_current = function ( $selector, $post_id ) {
	if ( ! is_string( $selector ) || $selector === '' ) return null;
	if ( ! function_exists( 'get_field' ) ) return null;
	return get_field( $selector, $post_id, false );
};

$ops = array(
	'update_field'     => array( 'needs' => array( 'sel_string' ), 'fn' => 'update_field' ),
	'delete_field'     => array( 'needs' => array( 'sel_string' ), 'fn' => 'delete_field' ),
	'add_row'          => array( 'needs' => array( 'sel_string', 'row' ), 'fn' => 'add_row' ),
	'update_row'       => array( 'needs' => array( 'sel_string', 'row' ), 'fn' => 'update_row' ),
	'delete_row'       => array( 'needs' => array( 'sel_string' ), 'fn' => 'delete_row' ),
	'add_sub_row'      => array( 'needs' => array( 'sel_array', 'row' ), 'fn' => 'add_sub_row' ),
	'update_sub_row'   => array( 'needs' => array( 'sel_array', 'row' ), 'fn' => 'update_sub_row' ),
	'delete_sub_row'   => array( 'needs' => array( 'sel_array' ), 'fn' => 'delete_sub_row' ),
	'update_sub_field' => array( 'needs' => array( 'sel_array' ), 'fn' => 'update_sub_field' ),
	'delete_sub_field' => array( 'needs' => array( 'sel_array' ), 'fn' => 'delete_sub_field' ),
);

if ( ! isset( $ops[ $op ] ) ) {
	acf_skill_error( "Unknown op: {$op}" );
	return;
}

$check = $ops[ $op ]['needs'];
if ( in_array( 'sel_string', $check, true ) && ! $need( $sel !== null && $sel !== '', "op '{$op}' requires a non-empty string selector." ) ) return;
if ( in_array( 'sel_array',  $check, true ) && ! $need( is_array( $sel ), "op '{$op}' requires an array selector [parent, row_i, sub]." ) ) return;
if ( in_array( 'row',        $check, true ) && ! $need( is_array( $row ), "op '{$op}' requires a 'row' object." ) ) return;

// Build the call shape we would invoke (for both dry_run and real execution).
$would_call = array( 'fn' => $ops[ $op ]['fn'], 'selector' => $sel, 'post_id' => $pid );
switch ( $op ) {
	case 'update_field':
	case 'update_sub_field':
		$would_call['value'] = $val; break;
	case 'add_row':
	case 'add_sub_row':
		$would_call['row'] = $row; break;
	case 'update_row':
	case 'update_sub_row':
		$would_call['index'] = $idx; $would_call['row'] = $row; break;
	case 'delete_row':
	case 'delete_sub_row':
		$would_call['index'] = $idx; break;
}

if ( $dry_run ) {
	acf_skill_return( array(
		'op'            => $op,
		'post_id'       => $pid,
		'dry_run'       => true,
		'current_value' => $peek_current( $sel, $pid ),
		'would_call'    => $would_call,
		'note'          => 'No mutation performed. Re-run with dry_run:false (and after user confirmation) to apply.',
	) );
	return;
}

switch ( $op ) {
	case 'update_field':     $r = update_field( $sel, $val, $pid ); break;
	case 'delete_field':     $r = delete_field( $sel, $pid ); break;
	case 'add_row':          $r = add_row( $sel, $row, $pid ); break;
	case 'update_row':       $r = update_row( $sel, $idx, $row, $pid ); break;
	case 'delete_row':       $r = delete_row( $sel, $idx, $pid ); break;
	case 'add_sub_row':      $r = add_sub_row( $sel, $row, $pid ); break;
	case 'update_sub_row':   $r = update_sub_row( $sel, $idx, $row, $pid ); break;
	case 'delete_sub_row':   $r = delete_sub_row( $sel, $idx, $pid ); break;
	case 'update_sub_field': $r = update_sub_field( $sel, $val, $pid ); break;
	case 'delete_sub_field': $r = delete_sub_field( $sel, $pid ); break;
}

acf_skill_return( array( 'op' => $op, 'post_id' => $pid, 'result' => $r ) );
