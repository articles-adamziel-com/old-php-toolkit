<?php

namespace WordPress\ByteStream\Filter;

/**
 * A reader that computes a checksum of the bytes read.
 */
class ChecksumFilter implements ByteFilter {

    private $hash_context;
    private $checksum;

    public function __construct( string $encoding = 'sha1' ) {
        $this->hash_context = hash_init($encoding);
    }

    public function filter_bytes(string $bytes): string|false {
        hash_update($this->hash_context, $bytes);
        return $bytes;
    }

    public function close(): string {
        return '';
    }

    public function get_hash(): string {
        if(null === $this->checksum) {
            $this->checksum = hash_final($this->hash_context);
            $this->hash_context = null;
        }
        return $this->checksum;
    }

}
