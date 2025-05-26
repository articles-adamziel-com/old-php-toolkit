<?php

namespace WordPress\HttpClient\Layer;

use WordPress\HttpClient\Request;
use WordPress\HttpClient\Transport\TransportInterface;

class TransportLayer implements LayerInterface {
    /** @var TransportInterface */
    private $transport;

    public function __construct(TransportInterface $transport) {
        $this->transport = $transport;
    }

    public function event_loop_tick(): bool {
        return $this->transport->event_loop_tick();
    }

    public function on_event(string $event, Request $request): void {
        // Base transport layer has nothing to do on events.
    }
}
