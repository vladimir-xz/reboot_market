<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LocaleController extends AbstractController
{
    private const LANG_CURRENCY = [
        'ENG' => 'EUR',
        'ENG2' => 'USD',
        'CZ' => 'CZK',
        'RU' => 'RUB',
    ];

    #[Route('/lang/{lang}', name: 'lang')]
    public function setLanguage(string $lang, Request $request): Response
    {
        $route = $request->headers->get('referer') ?? $this->generateUrl('homepage');
        if (!array_key_exists($lang, $this::LANG_CURRENCY)) {
            return $this->redirect($route);
        }

        $response = new RedirectResponse($route);

        $response->headers->setCookie(Cookie::create('lang', $lang));
        $currency = $request->cookies->get('cur', null);
        if ($currency === null) {
            $response->headers->setCookie(Cookie::create('cur', $this::LANG_CURRENCY[$lang]));
        }

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
        $response->headers->setCookie(Cookie::create('cur', $currency));
        $response->isRedirect($route);

        return $response;
    }
}
