<?php
/**
 * kb-loop.php
 *
 * Contains the primary class for generating a loop with pagination.
 * Separately namespaced to allow for reuse in other plugins.
 *
 * @package EventPress
 * @version 0.1
 * @author Kunal Bhalla 
 */

/**
 * Stores all the loops' instances by ID.
 *
 * @global Array kb_loops
 * @since 0.1
 */
global $kb_loops; 

/**
 * Stores all the loops' current result object by ID.
 *
 * @global Array kb_loops
 * @since 0.1
 */
global $kb_result;

/**
 * A general loop class, does all the heavy lifting.
 * Only the query function should be specifed for initiating.
 *
 * @since 0.1
 */ 
class KB_Loop {
	
	/**
	 * The current result index.
	 *
	 * @var int
	 * @since 0.1
	 */
	var $current = -1;

	/**
	 * The total results available.
	 *
	 * @var int
	 * @since 0.1
	 */
	var $total = 0;

	/**
	 * The data recovered from the database.
	 * 
	 * @var mixed
	 * @since 0.1
	 */
	var $results;

	/**
	 * The current result.
	 *
	 * @var mixed
	 * @since 0.1
	 */
	var $result;

	/**
	 * Whether the loop is running.
	 * 
	 * @var boolean
	 * @since 0.1
	 */
	var $in_the_loop;

	/**
	 * The current page number.
	 *
	 * @var int
	 * @since 0.1
	 */
	var $page;

	/**
	 * The number of results to be displayed per page.
	 *
	 * @var int
	 * @since 0.1
	 */
	var $per_page;

	/**
	 * The max number of results to be returned.
	 *
	 * @var int
	 * @since 0.1
	 */
	var $max;

	/**
	 * Initializing the loop. Should be over-ridden by classes extending it.
	 * 
	 * @param Array $args Any arguments provided to the loop for extending it.
	 */
	function kb_loop( $args ) {
	}

	/**
	 * Check if results exist for the current query.
	 *
	 * @return boolean
	 * @since 0.1
	 */
	function has_results() {
		if( $this->total ) 
			return true;

		return false;
	}

	/**
	 * Move to the next result in the loop.
	 * Move {@link $this->current} to the next value.
	 *
	 * @return mixed The current result.
	 * @since 0.1
	 */
	function next_result() {
		$this->current++;
		$this->result = $this->results[ $this->current ];

		return $this->result;
	}

	/**
	 * Rewind all results to the initial level;
	 * {@link $this->current} and {@link @this->result}
	 * are reset to the initial possible values.
	 * @since 0.1
	 */
	function rewind_results() {
		$this->current = -1;
		$this->result = NULL;
	}

	/**
	 * Check for the current position, rewind on reaching the end.
	 * @since 0.1
	 */
	function results() {
		if( $this->current + 1 < $this->max )
			return true;
		else if( $this->current + 1 == $this->max )
			$this->rewind_results();

		$this->in_the_loop = false;
		return false;
	}

	/**
	 * Move one step forward.
	 * @since 0.1
	 */
	function the_result() {
		$this->in_the_loop = true;
		$this->result = $this->next_result();

		return $this->result;
	}

}

/**
 * Creates the loop object and checks if any items exist.
 * The args array is directly passed on the class constructor.
 *
 * @uses $kb_loops Stores all instances of loops by id
 * @uses $kb_loops_data Stores all data for the loops by id
 * @param mixed $args The arguments to pass on to the loop constructor
 * @param string $id The identifier for the loop that's going on. Defaults to the current loop.
 *
 * @since 0.1
 */
function kb_has_results( $args, $class = 'KB_Loop', $id = 'current' ) {
	global $kb_loops;	

	$kb_loops[ $id ] = new $class( $args );

	return $kb_loops[$id]->has_results();
}

/**
 * Checks if results are there, cleanup otherwise.
 *
 * @uses $kb_loops
 * @uses $kb_loops_data
 * @uses $kb_result
 *
 * @since 0.1
 */
function kb_results( $id = 'current' ) {
	global $kb_loops, $kb_loops_data, $kb_result;
	
	if( $kb_loops[ $id ]->results() ) 
		return true;
	else {
		unset( $kb_result[ $id ] );
		unset( $kb_loops_data[ $id ] );
		return false;
	}
}

/**
 * Moves the pointer once step forward, 
 * populates the globals properly.
 *
 * @uses kb_loops
 * @uses kb_loops_data
 * @uses kb_result
 *
 * @since 0.1
 */
function kb_the_result( $id = 'current' ) {
	global $kb_loops, $kb_result;

	$kb_result[ $id ] = $kb_loops[ $id ]->the_result();
}

/**
 * Returns the data for the current result from the corresponding global.
 * Returns false if the data could not be found.
 *
 * @param string $data Identify exactly what bit of data you want: a function 
 *                     is called based on that
 * @param array $args An array of arguments to be passed to generation function.
 * @param string $id And for which loop you want it
 *
 * @since 0.1
 *
 * @return mixed The value corresponding to the array's result.
 */
function kb_get_data( $data, $args = Array(), $id = 'current' ) {
	global $kb_loops;

	if ( method_exists( $kb_loops[ $id ], $data ) )
		return call_user_func( Array( &$kb_loops[ $id ], $data ), $args );

	return false;
}

/**
 * Echo the data.
 *
 * @param string $data Identify exactly what bit of data you want
 * @param string $id And for which loop you want it
 *
 * @uses kb_get_data
 * @since 0.1
 */
function kb_the_data( $data, $args = Array(), $id = 'current' ) {
	echo kb_get_data( $data, $args, $id );
}
