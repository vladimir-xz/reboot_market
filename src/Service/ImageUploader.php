<?php

namespace App\Service;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImageUploader
{
    public function __construct(
        private string $targetDirectory,
        private SluggerInterface $slugger,
    ) {
    }

    public function upload(UploadedFile $file, Product $product): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        // this is needed to safely include the file name as part of the URL
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
        $imageDirectory = $this->getTargetDirectory()
            . strtoupper($product->getType())
            . DIRECTORY_SEPARATOR
            . strtoupper($product->getCategory()->getName())
        ;

        // Move the file to the directory where brochures are stored
        try {
            $file->move($imageDirectory, $newFilename);
        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
        }

        return $imageDirectory . DIRECTORY_SEPARATOR . $newFilename;
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}
