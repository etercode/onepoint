<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Adds permissive CORS headers to every response and short-circuits
 * preflight (OPTIONS) requests. Open to all origins for now.
 *
 * @see https://symfony.com/doc/current/event_dispatcher.html
 */
final class CorsSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            // Run before the router so preflight requests don't 404/405.
            KernelEvents::REQUEST => ['onKernelRequest', 250],
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        // Answer CORS preflight immediately with a 204.
        if ($event->getRequest()->isMethod('OPTIONS')) {
            $event->setResponse(new Response('', Response::HTTP_NO_CONTENT));
        }
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $headers = $event->getResponse()->headers;

        $headers->set('Access-Control-Allow-Origin', $request->headers->get('Origin', '*'));
        $headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $headers->set('Access-Control-Allow-Headers', $request->headers->get('Access-Control-Request-Headers', 'Content-Type, Authorization'));
        $headers->set('Access-Control-Allow-Credentials', 'true');
        $headers->set('Access-Control-Max-Age', '3600');
        $headers->set('Vary', 'Origin', false);
    }
}
