<?php

namespace App\Command;

use App\Catalog\CatalogData;
use App\Catalog\Slugger;
use App\Entity\Category;
use App\Entity\Collection;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\ProductImage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Seeds the storefront catalog (categories, collections, products) from
 * CatalogData. Idempotent: refuses to run if products already exist unless
 * --fresh is passed, which wipes the catalog tables first.
 */
#[AsCommand(name: 'app:catalog:seed', description: 'Seed the storefront catalog with the reference product data')]
class SeedCatalogCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('fresh', null, InputOption::VALUE_NONE, 'Delete existing catalog rows before seeding');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fresh = (bool) $input->getOption('fresh');

        $existing = $this->em->getRepository(Product::class)->count([]);
        if ($existing > 0 && !$fresh) {
            $io->warning(sprintf('Catalog already has %d product(s). Re-run with --fresh to wipe and reseed.', $existing));

            return Command::SUCCESS;
        }

        if ($fresh) {
            // Orders reference products, so a full catalog wipe must clear them
            // first (re-seed sample data afterwards with app:sample:seed).
            $hadOrders = $this->em->getRepository(Order::class)->count([]) > 0;
            $this->em->createQuery('DELETE FROM '.OrderItem::class.' i')->execute();
            $this->em->createQuery('DELETE FROM '.Order::class.' o')->execute();

            // Respect FK order: images -> products -> collections/categories.
            $this->em->createQuery('DELETE FROM '.ProductImage::class.' pi')->execute();
            $this->em->createQuery('DELETE FROM '.Product::class.' p')->execute();
            $this->em->createQuery('DELETE FROM '.Collection::class.' c')->execute();
            $this->em->createQuery('DELETE FROM '.Category::class.' c')->execute();
            $io->note('Existing catalog rows deleted.');
            if ($hadOrders) {
                $io->warning('Orders were also deleted (they reference products). Re-run app:sample:seed to restore them.');
            }
        }

        $categories = $this->seedCategories();
        $collections = $this->seedFeaturedCollections();
        $this->seedProducts($categories, $collections);

        $this->em->flush();

        $io->success(sprintf(
            'Seeded %d categories, %d collections, %d products.',
            \count($categories),
            \count($collections),
            \count(CatalogData::products()),
        ));

        return Command::SUCCESS;
    }

    /**
     * @return array<string, Category>
     */
    private function seedCategories(): array
    {
        $map = [];
        $order = 0;
        foreach (CatalogData::categories() as $name => $image) {
            $category = (new Category())
                ->setName($name)
                ->setSlug(Slugger::slugify($name))
                ->setImage($image)
                ->setSortOrder($order++);
            $this->em->persist($category);
            $map[$name] = $category;
        }

        return $map;
    }

    /**
     * Creates the curated, featured collections. Other collections are created
     * on demand while seeding products.
     *
     * @return array<string, Collection>
     */
    private function seedFeaturedCollections(): array
    {
        $map = [];
        $order = 0;
        foreach (CatalogData::featuredCollections() as $data) {
            $collection = (new Collection())
                ->setName($data['name'])
                ->setSlug(Slugger::slugify($data['name']))
                ->setTagline($data['tagline'])
                ->setImage($data['image'])
                ->setFeatured(true)
                ->setSortOrder($order++);
            $this->em->persist($collection);
            $map[$data['name']] = $collection;
        }

        return $map;
    }

    /**
     * @param array<string, Category>   $categories
     * @param array<string, Collection> $collections keyed by name; grows as
     *                                               non-curated collections are seen
     */
    private function seedProducts(array $categories, array &$collections): void
    {
        $galleries = CatalogData::galleries();
        $position = 0;
        foreach (CatalogData::products() as $index => $data) {
            ++$position;
            $flags = CatalogData::derivedFlags($position, $data['price']);

            $product = (new Product())
                ->setName($data['name'])
                ->setSlug(Slugger::slugify($data['name']))
                ->setPrice($data['price'])
                ->setOldPrice($flags['oldPrice'])
                ->setOnSale($flags['onSale'])
                ->setIsNew($flags['isNew'])
                ->setInStock($flags['inStock'])
                ->setFreeDelivery(true)
                ->setWarrantyYears(2)
                ->setMaterial($data['material'])
                ->setColor($data['color'])
                ->setDimensions($data['dimensions'])
                ->setDescription($data['description'])
                ->setCategory($categories[$data['category']])
                ->setCollection($this->resolveCollection($data['collection'], $data['image'], $collections));

            $this->em->persist($product);

            // Seed the product's image gallery (first entry is the primary).
            foreach ($galleries[$index] as $sortOrder => $url) {
                $this->em->persist(
                    (new ProductImage())
                        ->setProduct($product)
                        ->setUrl($url)
                        ->setSortOrder($sortOrder),
                );
            }
        }
    }

    /**
     * Returns the collection for the given name, creating a non-featured one
     * (using the product image) the first time an uncurated name is seen.
     *
     * @param array<string, Collection> $collections
     */
    private function resolveCollection(string $name, string $image, array &$collections): Collection
    {
        if (isset($collections[$name])) {
            return $collections[$name];
        }

        $collection = (new Collection())
            ->setName($name)
            ->setSlug(Slugger::slugify($name))
            ->setImage($image)
            ->setFeatured(false)
            ->setSortOrder(100 + \count($collections));
        $this->em->persist($collection);
        $collections[$name] = $collection;

        return $collection;
    }
}
