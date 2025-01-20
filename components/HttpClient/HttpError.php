<?php

namespace WordPress\HttpClient;

class HttpError {
	public $message;

	public function __construct( $message ) {
		$this->message = $message;
	}

	public function __toString() {
		return $this->message;
	}
}
