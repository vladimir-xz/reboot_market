<?php

namespace App\Twig\Components;

use App\Repository\ProductRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Metadata\UrlMapping;
use Psr\Log\LoggerInterface;

#[AsLiveComponent]
final class Search
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    #[LiveProp(writable: true, url: new UrlMapping(as: 'q'))]
    public string $query = '';

    #[LiveAction]
    public function searching()
    {
        $this->dispatchBrowserEvent('search:checkPath', [
            'query' => $this->query,
        ]);
        $this->emit('receiveQuery', [
            'query' => $this->query,
        ]);
        $this->emit('addQuery', [
            'query' => $this->query,
        ]);
    }

    #[LiveListener('removeQuery')]
    public function receiveQuery()
    {
        $this->query = '';
        $this->searching();
    }
}
