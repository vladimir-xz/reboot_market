<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $log)
    {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!$request->hasPreviousSession()) {
            return;
        }

        if ($locale = $request->attributes->get('_locale')) {
            $this->log->info($locale . ' is current locale');
            $request->getSession()->set('_locale', $locale);
        } else {
            $locale = $request->getPreferredLanguage(['en', 'cz']);

            $this->log->info($locale . ' is preferred locale');
            $this->log->info('Locale stored in session is ' . $request->getSession()->get('_locale', 'empty'));
            // if no explicit locale has been set on this request, use one from the session
            $this->log->info('Locale in attributes is ' . $request->attributes->get('_locale', ''));
            $request->setLocale($request->getSession()->get('_locale', $locale));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // must be registered before (i.e. with a higher priority than) the default Locale listener
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}
