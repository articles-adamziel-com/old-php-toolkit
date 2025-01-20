<?php

namespace WordPress\HttpServer\ResponseWriter;

use WordPress\ByteStream\Writer\ByteWriter;

interface ResponseWriter extends ByteWriter {

    public function send_http_code($code);
    public function send_header($name, $value);

}