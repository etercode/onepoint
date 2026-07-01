<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\Collection;
use App\Entity\ProductImage;
use App\Service\ImageStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Downloads every remote (http/https) catalog image referenced in the database
 * — category images, collection images and product gallery images — into
 * public/uploads/ and rewrites the stored value to the local relative path, so
 * the storefront serves images from our own domain instead of embawood.az.
 *
 * Idempotent: already-local paths are skipped, and identical remote URLs map to
 * the same file (named by URL hash), so re-running only fetches what's missing.
 */
#[AsCommand(name: 'app:images:localize', description: 'Download remote catalog images into public/uploads and rewrite DB paths')]
class LocalizeImagesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ImageStorage $storage,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var array<string, string> $cache remote url => local relative path */
        $cache = [];
        $downloaded = 0;
        $reused = 0;
        $failed = 0;

        $process = function (string $url, string $subdir) use (&$cache, &$downloaded, &$reused, &$failed, $io): ?string {
            if (!ImageStorage::isRemote($url)) {
                return null; // already local or empty
            }
            // Key by folder so each subdir keeps its own copy — a product and a
            // collection using the same source image don't share one file.
            $key = $subdir.'|'.$url;
            if (isset($cache[$key])) {
                ++$reused;

                return $cache[$key];
            }

            $local = $this->storage->downloadRemote($url, $subdir);
            if (null === $local) {
                ++$failed;
                $io->warning('Failed: '.$url);

                return null;
            }

            $cache[$key] = $local;
            ++$downloaded;

            return $local;
        };

        // Categories
        foreach ($this->em->getRepository(Category::class)->findAll() as $category) {
            $local = $process((string) $category->getImage(), 'categories');
            if (null !== $local) {
                $category->setImage($local);
            }
        }

        // Collections
        foreach ($this->em->getRepository(Collection::class)->findAll() as $collection) {
            $local = $process((string) $collection->getImage(), 'collections');
            if (null !== $local) {
                $collection->setImage($local);
            }
        }

        // Product gallery images
        foreach ($this->em->getRepository(ProductImage::class)->findAll() as $image) {
            $local = $process((string) $image->getUrl(), 'products');
            if (null !== $local) {
                $image->setUrl($local);
            }
        }

        $this->em->flush();

        $io->success(sprintf(
            'Localized images: %d downloaded, %d reused (shared URLs), %d failed.',
            $downloaded,
            $reused,
            $failed,
        ));

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
