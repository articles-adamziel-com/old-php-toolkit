<?php

use PHPUnit\Framework\TestCase;
use WordPress\ByteStream\MemoryPipe;
use WordPress\ByteStream\Writer\FileWriter;

class FileWriterTest extends TestCase {
    public function testExample() {
        // Example test case
        $this->assertTrue(true);
    }

    public function testCreateFileWriterFromPath() {
        $writer = FileWriter::from_path('test.txt');
        $this->assertInstanceOf(FileWriter::class, $writer);
    }

    public function testAppendBytesToFile() {
        $writer = FileWriter::from_path('test.txt');
        $writer->append_bytes('Hello');
        $this->assertFileExists('test.txt');
    }

    public function testCloseFileWriter() {
        $writer = FileWriter::from_path('test.txt');
        $writer->close();

        // We just want to see there are no exceptions thrown
        $this->assertTrue(true);
    }
    
} 