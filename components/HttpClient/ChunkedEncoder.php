<?php

namespace WordPress\HttpClient;

use WordPress\ByteStream\Writer\ByteWriter;

class ChunkedEncoder implements ByteWriter {

    private $next_chunk_data = '';
    private $current_chunk = '';

    /**
     * Encodes $bytes using chunked encoding as:
     * 
     * <chunk-size><CRLF><chunk-data><CRLF>
     *
     * @param string $bytes The bytes to encode.
     */
    public function append_bytes(string $bytes): void {
        $this->next_chunk_data .= $bytes;
    }

    public function next_bytes(): bool {
        if(strlen($this->next_chunk_data) === 0) {
            return false;
        }
        $chunk_size = str_pad(dechex(strlen($this->next_chunk_data)), 2, '0', STR_PAD_LEFT);
        $this->current_chunk = $chunk_size . "\r\n" . $this->next_chunk_data . "\r\n";
        $this->next_chunk_data = '';
        return true;
    }

    public function get_bytes(): string {
        return $this->current_chunk;
    }

    public function close(): void {
        $this->current_chunk = "0\r\n\r\n";
        $this->next_chunk_data = '';
    }
}
