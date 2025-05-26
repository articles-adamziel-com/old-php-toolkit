<?php

namespace WordPress\HttpClient\Layer;

use WordPress\HttpClient\Request;

interface LayerInterface {
    public function event_loop_tick(): bool;

    public function on_event(string $event, Request $request): void;
}
