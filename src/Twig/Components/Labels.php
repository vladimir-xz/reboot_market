<?php

namespace App\Twig\Components;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveArg;

#[AsLiveComponent]
final class Labels
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    #[LiveProp]
    public string $query = '';
    #[LiveProp]
    public bool $hasIncluded = false;
    #[LiveProp]
    public bool $hasExcluded = false;
    #[LiveProp]
    public bool $hasFilters = false;

    #[LiveAction]
    public function removeQuery()
    {
        $this->query = '';
        $this->emit('removeQuery');
    }

    #[LiveAction]
    public function removeIncluded()
    {
        $this->hasIncluded = false;
        $this->emit('removeIncluded');
    }

    #[LiveAction]
    public function removeExcluded()
    {
        $this->hasExcluded = false;
        $this->emit('removeExcluded');
    }

    #[LiveAction]
    public function removeFilters()
    {
        $this->hasFilters = false;
        $this->emit('removeFilters');
    }

    #[LiveListener('addQuery')]
    public function addQuery(#[LiveArg] string $query)
    {
        $this->query = $query;
    }

    #[LiveListener('changeIfIncluded')]
    public function changeIfIncluded(#[LiveArg] bool $newValue)
    {
        $this->hasIncluded = $newValue;
    }

    #[LiveListener('changeIfExcluded')]
    public function changeIfExcluded(#[LiveArg] bool $newValue)
    {
        $this->hasExcluded = $newValue;
    }

    #[LiveListener('makeFiltered')]
    public function makeFiltered(#[LiveArg] bool $newValue)
    {
        $this->hasFilters = $newValue;
    }
}
