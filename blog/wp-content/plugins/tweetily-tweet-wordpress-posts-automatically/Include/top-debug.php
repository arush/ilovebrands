<?php

function top_DEBUG( $str ) {
	global $top_debug;
        $top_enable_log = get_option('top_enable_log');
        if($top_enable_log)
        {
	$top_debug->enable(true);
        }
        
	$top_debug->add_to_log( $str );
}

function top_is_debug_enabled() {
	global $top_debug;
	
	return $top_debug->is_enabled();
}

class topDebug {
	var $debug_file;
	var $log_messages;

	function topDebug() {
		$this->debug_file = false;
	}
	
	function is_enabled() {
		return ( $this->debug_file );	
	}

	function enable( $enable_or_disable ) {
		if ( $enable_or_disable ) {
			$this->debug_file = fopen( WP_CONTENT_DIR . '/plugins/tweetily-tweet-wordpress-posts-automatically/log.txt', 'a+t' );
			$this->log_messages = 0;
		} else if ( $this->debug_file ) {
			fclose( $this->debug_file );
			$this->debug_file = false;		
		}
	}

	function add_to_log( $str ) {
		if ( $this->debug_file ) {
			
			$log_string = $str;
			$log_string .='The last tweet in : '.date("F j, Y, g:i a");
			// Write the data to the log file
			fwrite( $this->debug_file, sprintf( "%12s %s\n", time(), $log_string ) );
			fflush( $this->debug_file );
			
			$this->log_messages++;
		}
	}
}

global $top_debug;
$top_debug = &new topDebug;


?>
