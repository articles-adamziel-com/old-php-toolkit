<?php

namespace WordPress\Filesystem\Mixin;

/**
 * Implements get_contents() using the read stream provided by the open_read_stream() method.
 */
trait GetContentsViaReadStream {

	public function get_contents($path) {
		$stream = $this->open_read_stream($path);
		try {
			$body = '';
			while($stream->next_bytes()) {
				$body .= $stream->get_bytes();
			}
		} finally {
			$stream->close();
		}
		return $body;
	}

}