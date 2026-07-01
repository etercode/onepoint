<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Central image storage under public/uploads/<subdir>/ (served by nginx).
 * Handles uploaded files and downloading remote URLs so images are always
 * self-hosted. Stored/returned paths are relative to the uploads root
 * (e.g. "collections/foo-abc123.webp") and resolve at "/uploads/...".
 */
class ImageStorage
{
    private const ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public function __construct(
        #[Autowire('%kernel.project_dir%/public/uploads')]
        private readonly string $uploadsDir,
        private readonly SluggerInterface $slugger,
    ) {
    }

    public static function isRemote(?string $value): bool
    {
        return null !== $value && 1 === preg_match('#^https?://#i', $value);
    }

    /**
     * Stores an uploaded file under <subdir> and returns its relative path.
     */
    public function storeUpload(UploadedFile $file, string $subdir): string
    {
        $original = pathinfo($file->getClientOriginalName(), \PATHINFO_FILENAME);
        $filename = sprintf(
            '%s-%s.%s',
            $this->slugger->slug($original)->lower() ?: $subdir,
            uniqid(),
            $file->guessExtension() ?: 'bin',
        );

        $file->move($this->uploadsDir.'/'.$subdir, $filename);

        return $subdir.'/'.$filename;
    }

    /**
     * Downloads a remote http(s) image into <subdir> and returns its relative
     * path, or null on failure. Named by URL hash so identical URLs share one
     * file within the subdir and re-downloads are avoided.
     */
    public function downloadRemote(string $url, string $subdir): ?string
    {
        if (!self::isRemote($url)) {
            return null;
        }

        $ext = strtolower(pathinfo(parse_url($url, \PHP_URL_PATH) ?: '', \PATHINFO_EXTENSION));
        if (!\in_array($ext, self::ALLOWED_EXT, true)) {
            $ext = 'png';
        }

        $relative = $subdir.'/'.md5($url).'.'.$ext;
        $target = $this->uploadsDir.'/'.$relative;

        if (is_file($target) && filesize($target) > 0) {
            return $relative;
        }

        $dir = \dirname($target);
        if (!is_dir($dir) && !@mkdir($dir, 0o777, true) && !is_dir($dir)) {
            return null;
        }

        $context = stream_context_create([
            'http' => ['timeout' => 20, 'follow_location' => 1, 'user_agent' => 'onepoint-image/1.0'],
            'https' => ['timeout' => 20, 'follow_location' => 1, 'user_agent' => 'onepoint-image/1.0'],
        ]);

        $bytes = @file_get_contents($url, false, $context);
        if (false === $bytes || '' === $bytes) {
            return null;
        }

        return false !== @file_put_contents($target, $bytes) ? $relative : null;
    }

    /**
     * If the value is a remote URL, download it locally and return the local
     * path; on failure (or for already-local values) return the value unchanged.
     */
    public function localize(?string $value, string $subdir): ?string
    {
        if (!self::isRemote($value)) {
            return $value;
        }

        return $this->downloadRemote((string) $value, $subdir) ?? $value;
    }

    /**
     * Deletes a previously stored local file. No-op for null / remote URLs.
     */
    public function remove(?string $relativePath): void
    {
        if (null === $relativePath || self::isRemote($relativePath)) {
            return;
        }

        $path = $this->uploadsDir.'/'.$relativePath;
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
