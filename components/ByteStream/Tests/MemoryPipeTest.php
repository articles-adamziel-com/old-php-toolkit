<?php

use PHPUnit\Framework\TestCase;
use WordPress\ByteStream\MemoryPipe;

class MemoryPipeTest extends TestCase {

    public function test_piping_data() {
        $text = file_get_contents(dirname(__FILE__) . '/fixtures/preface-to-pygmalion.txt');
        $pipe = new MemoryPipe();
        $chunk1 = substr($text, 0, 1371);
        $chunk2 = substr($text, 1371);

        $pipe->append_bytes($chunk1);
        $pipe->next_bytes(strlen($chunk1));
        $this->assertEquals($chunk1, $pipe->get_bytes());

        $pipe->append_bytes($chunk2);
        $pipe->next_bytes(strlen($chunk2));
        $this->assertEquals($chunk2, $pipe->get_bytes());
    }

    public function test_reached_end_of_data_assumes_false_if_length_is_not_known() {
        $pipe = new MemoryPipe();
        $pipe->append_bytes('Hello');
        $pipe->next_bytes(5);
        $this->assertFalse($pipe->next_bytes(5));
        $this->assertFalse($pipe->reached_end_of_data());
    }

}
