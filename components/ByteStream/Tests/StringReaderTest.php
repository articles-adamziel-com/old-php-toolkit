<?php

use PHPUnit\Framework\TestCase;
use WordPress\ByteStream\MemoryPipe;

class StringReaderTest extends TestCase {
    public function testLengthWithExpectedLength() {
        $reader = new MemoryPipe('', 1050);
        $reader->append_bytes('Initial');
        $this->assertEquals(1050, $reader->length());
        $reader->append_bytes(' Additional data.');
        $this->assertEquals(1050, $reader->length());
    }

    public function testLengthWithoutExpectedLength() {
        $reader = new MemoryPipe();
        $reader->append_bytes('Initial');
        $this->assertEquals(7, $reader->length());
        $reader->append_bytes(' Additional data.');
        $this->assertEquals(24, $reader->length());
    }

    public function testSeekAndTell() {
        $reader = new MemoryPipe();
        $reader->append_bytes('Initial');

        // Append data to the reader and run a basic seek operation
        $reader->append_bytes(' Additional data.');
        $reader->seek(8);
        $this->assertEquals(8, $reader->tell());

        // Move through 10 bytes and confirm the tell position follows
        $reader->next_bytes(10);
        $this->assertEquals('Additional', $reader->get_bytes());
        $this->assertEquals(18, $reader->tell());

        // Go back to the start and confirm the tell position follows
        $reader->seek(8);
        $this->assertEquals(8, $reader->tell());

        // Read the same 10 bytes again
        $reader->next_bytes(10);
        $this->assertEquals('Additional', $reader->get_bytes());
        $this->assertEquals(18, $reader->tell());

        // Okay, more challenge – go back and append bytes. This would
        // clear a part of the buffer and reshuffle the internal state.
        $reader->seek(8);
        $reader->append_bytes('Even more data.');
        $this->assertEquals(8, $reader->tell());

        // Let's confirm the above operations still work with the same offsets.
        $reader->next_bytes(10);
        $this->assertEquals('Additional', $reader->get_bytes());
        $this->assertEquals(18, $reader->tell());

        $reader->seek(8);
        $reader->append_bytes('Even more data.');
        $this->assertEquals(8, $reader->tell());

        $reader->next_bytes(10);
        $this->assertEquals('Additional', $reader->get_bytes());
        $this->assertEquals(18, $reader->tell());
    }

    public function testAppendBytes() {
        $reader = new MemoryPipe();
        $reader->append_bytes('Initial');
        $reader->append_bytes(' Additional data.');

        $this->assertTrue($reader->next_bytes(10));
        $this->assertEquals('Initial Ad', $reader->get_bytes());

        $reader->append_bytes(' More data.');
        $this->assertTrue($reader->next_bytes(20));
        $this->assertEquals('ditional data. More ', $reader->get_bytes());
    }
}
