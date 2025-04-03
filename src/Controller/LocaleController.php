<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\LocaleSwitcher;

final class LocaleController extends AbstractController
{
    private const LANG_CURRENCY = [
        'en' => 'EUR',
        'en2' => 'USD',
        'cz' => 'CZK',
        'ru' => 'RUB',
    ];

    public function __construct(private LocaleSwitcher $localeSwitcher, private LoggerInterface $log)
    {
    }

    #[Route('/lang/{lang}', name: 'lang')]
    public function setLanguage(string $lang, Request $request): Response
    {
        $route = $this->generateUrl('homepage');
        if (!array_key_exists($lang, $this::LANG_CURRENCY)) {
            return $this->redirect($route);
        }

        $this->localeSwitcher->setLocale($lang);
        $response = new RedirectResponse($this->generateUrl('homepage'));

        $session = $request->getSession();
        if ($session->get('currency', null) === null) {
            $session->set('currency', $this::LANG_CURRENCY[$lang]);
        }

        $this->log->info('Redirecting to homepage');

        return $response;
    }

    #[Route('/cur/{currency}', name: 'currency')]
    public function setCurrency(string $currency, Request $request, Response $response): Response
    {
        $route = $request->headers->get('referer') ?? $this->generateUrl('homepage');
        if (!in_array($currency, $this::LANG_CURRENCY)) {
            return $this->redirect($route);
        }

        $response = new RedirectResponse($route);
        $request->getSession()->set('currency', $currency);

        return $response;
    }
}
