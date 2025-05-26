<?php

namespace WordPress\HttpClient\Layer;

use WordPress\HttpClient\ClientState;
use WordPress\HttpClient\Request;

class HttpCacheLayer implements LayerInterface {
    /** @var ClientState */
    private $state;
    /** @var LayerInterface */
    private $next;

    public function __construct(ClientState $state, LayerInterface $next) {
        $this->state = $state;
        $this->next = $next;
    }

    public function event_loop_tick(): bool {
        // No cache logic yet, pass through.
        return $this->next->event_loop_tick();
    }

    public function on_event(string $event, Request $request): void {
        // Placeholder for future cache logic.
        $this->next->on_event($event, $request);
    }
}
