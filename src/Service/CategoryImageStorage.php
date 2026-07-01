<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Stores category images on the local filesystem under public/uploads/categories/
 * (served directly by nginx). Mirrors ProductImageStorage.
 *
 * Returned/stored paths are relative to the public uploads root, e.g.
 * "categories/yumsaq-mebel-65f0c1ab2d3e4.webp", served at "/uploads/categories/...".
 */
class CategoryImageStorage
{
    private const SUBDIR = 'categories';

    public function __construct(
        #[Autowire('%kernel.project_dir%/public/uploads')]
        private readonly string $uploadsDir,
        private readonly SluggerInterface $slugger,
    ) {
    }

    public function store(UploadedFile $file): string
    {
        $original = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $filename = sprintf(
            '%s-%s.%s',
            $this->slugger->slug($original)->lower() ?: 'category',
            uniqid(),
            $file->guessExtension() ?: 'bin',
        );

        $file->move($this->uploadsDir.'/'.self::SUBDIR, $filename);

        return self::SUBDIR.'/'.$filename;
    }

    /**
     * Delete a previously stored image. No-op for null, missing files, or
     * external URLs (seeded categories store absolute URLs, not local paths).
     */
    public function remove(?string $relativePath): void
    {
        if (null === $relativePath || str_starts_with($relativePath, 'http')) {
            return;
        }

        $path = $this->uploadsDir.'/'.$relativePath;

        if (is_file($path)) {
            @unlink($path);
        }
    }
}
