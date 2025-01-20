<?php

use PHPUnit\Framework\TestCase;
use WordPress\ByteStream\MemoryPipe;
use WordPress\ByteStream\Reader\ByteReader;
use WordPress\ByteStream\Reader\DeflateReader;
use WordPress\ByteStream\Reader\ResourceReader;
use WordPress\ByteStream\Reader\InflateReader;
use WordPress\ByteStream\Reader\ReaderUtils;
use WordPress\ByteStream\ReadStream;

class ReadStreamTest extends TestCase {

    public function test_basic_data_streaming() {
        $reference = file_get_contents(dirname(__FILE__) . '/fixtures/preface-to-pygmalion.txt');
        $reader = new ResourceReader(fopen(dirname(__FILE__) . '/fixtures/preface-to-pygmalion.txt', 'r'));
        $stream = new ReadStream($reader);
        $accumulated = '';
        $stream->next_bytes(100);
        $accumulated .= $stream->get_bytes();
        $this->assertEquals(100, strlen($stream->get_bytes()));

        $stream->next_bytes(100);
        $accumulated .= $stream->get_bytes();
        $this->assertEquals(100, strlen($stream->get_bytes()));

        $accumulated .= ReaderUtils::read_all_remaining_bytes($stream);
        $this->assertEquals(8704, strlen($accumulated));

        $this->assertEquals($reference, $accumulated);
    }

}
