<?php

namespace App\Twig;

use App\Dto\CartDto;
use App\Dto\ProductCartDto;
use Symfony\UX\LiveComponent\Hydration\HydrationExtensionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class CartHydrationExtension implements HydrationExtensionInterface
{
    public function __construct(
        private DenormalizerInterface&NormalizerInterface $serializer
    ) {
    }

    public function supports(string $className): bool
    {
        return $className === CartDto::class;
    }

    public function dehydrate(object $object): mixed
    {
        $products = $object->getProducts()->toArray();
        return [
            'totalWeight' => $object->getTotalWeight(),
            'totalPrice' => $object->getTotalPrice(),
            'products' => $this->serializer->normalize($products),
        ];
    }

    public function hydrate(mixed $data, string $className): ?object
    {
        $products = $this->serializer->denormalize($data['products'], ProductCartDto::class . '[]');
        return new CartDto($data['totalWeight'], $data['totalPrice'], $products);
    }
}
