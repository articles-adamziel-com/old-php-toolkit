<?php

namespace WordPress\HttpClient\Layer;

use WordPress\DataLiberation\URL\WPURL;
use WordPress\HttpClient\Client;
use WordPress\HttpClient\ClientState;
use WordPress\HttpClient\HttpError;
use WordPress\HttpClient\Request;

class RedirectionLayer implements LayerInterface {
    /** @var Client */
    private $client;
    /** @var ClientState */
    private $state;
    /** @var LayerInterface */
    private $next;

    public function __construct(Client $client, ClientState $state, LayerInterface $next) {
        $this->client = $client;
        $this->state = $state;
        $this->next = $next;
    }

    public function event_loop_tick(): bool {
        return $this->next->event_loop_tick();
    }

    public function on_event(string $event, Request $request): void {
        if ($event === Client::EVENT_GOT_HEADERS) {
            $this->handle_redirect($request);
        }
        $this->next->on_event($event, $request);
    }

    private function handle_redirect(Request $request): void {
        $response = $request->response;
        if ( ! $response ) {
            return;
        }
        $code = $response->status_code;
        if ( ! in_array($code, [301, 302, 303, 307, 308]) ) {
            return;
        }

        $location = $response->get_header('location');
        if (null === $location) {
            return;
        }

        $redirects_so_far = 0;
        $cause = $request;
        while ($cause->redirected_from) {
            ++$redirects_so_far;
            $cause = $cause->redirected_from;
        }

        if ($redirects_so_far >= $this->state->max_redirects) {
            $this->state->set_request_error($request, new HttpError('Too many redirects'));
            return;
        }

        $redirect_url = $location;
        $parsed = WPURL::parse($redirect_url, $request->url);
        if (false === $parsed) {
            $this->state->set_request_error($request, new HttpError(sprintf('Invalid redirect URL: %s', $redirect_url)));
            return;
        }
        $redirect_url = $parsed->toString();

        $this->client->enqueue(new Request($redirect_url, [
            'method'          => 'GET',
            'redirected_from' => $request,
        ]));
    }
}
