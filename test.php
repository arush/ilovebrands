<?php

function callback($matches) {
		return ($matches[1] . (((int)$matches[2])+1));
}
$originalUrl 	= 'mooset1';
$pattern		=	"/(.*)([0-9]+)$/";

$updatedUrl		=	preg_replace_callback(
									$pattern,
									"callback",
						    	    $originalUrl
								);
			if ($updatedUrl == $originalUrl) { $updatedUrl .= '1'; }

print_r($updatedUrl);