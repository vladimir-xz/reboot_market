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
        'en' => 'eur',
        'en2' => 'usd',
        'cz' => 'czk',
        'ru' => 'rub',
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

        return $response;
    }

    #[Route('/cur/{currency}', name: 'currency')]
    public function setCurrency(string $currency, Request $request): Response
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
