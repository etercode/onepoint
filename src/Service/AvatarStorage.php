<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Stores profile photos on the local filesystem under public/uploads/avatars/
 * (served directly by nginx). Keeps filesystem details out of the controller.
 *
 * Returned/stored paths are relative to the public uploads root, e.g.
 * "avatars/john-65f0c1ab2d3e4.webp", served at "/uploads/avatars/...".
 */
class AvatarStorage
{
    private const SUBDIR = 'avatars';

    public function __construct(
        #[Autowire('%kernel.project_dir%/public/uploads')]
        private readonly string $uploadsDir,
        private readonly SluggerInterface $slugger,
    ) {
    }

    /**
     * Move an uploaded image into the avatars directory under a safe, unique
     * name. Returns the path relative to the uploads root (stored on the user).
     */
    public function store(UploadedFile $file): string
    {
        $original = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        // Never trust the client extension; guess it from the file contents.
        $filename = sprintf(
            '%s-%s.%s',
            $this->slugger->slug($original)->lower(),
            uniqid(),
            $file->guessExtension() ?: 'bin',
        );

        $file->move($this->uploadsDir.'/'.self::SUBDIR, $filename);

        return self::SUBDIR.'/'.$filename;
    }

    /**
     * Delete a previously stored avatar (no-op if null/missing). Best-effort:
     * a failed delete must not break the request.
     */
    public function remove(?string $relativePath): void
    {
        if (null === $relativePath) {
            return;
        }

        $path = $this->uploadsDir.'/'.$relativePath;

        if (is_file($path)) {
            // Best-effort: an orphaned file is not worth failing the request.
            @unlink($path);
        }
    }
}
