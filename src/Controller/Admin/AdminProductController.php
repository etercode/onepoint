<?php

namespace App\Controller\Admin;

use App\Catalog\CatalogPresenter;
use App\Catalog\Slugger;
use App\Dto\ProductImageOrderRequest;
use App\Dto\ProductImageUrlRequest;
use App\Dto\ProductWriteRequest;
use App\Entity\Product;
use App\Entity\ProductImage;
use App\Repository\CategoryRepository;
use App\Repository\CollectionRepository;
use App\Repository\ProductImageRepository;
use App\Repository\ProductRepository;
use App\Service\ProductImageStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Admin product management (requires ROLE_ADMIN, also enforced in security.yaml).
 * Listing/reads reuse the public catalog endpoints. Scalar updates are full
 * replacements (PUT); deletes are soft. The image gallery is managed through
 * the /images sub-resource.
 */
#[Route('/api/admin/products')]
#[IsGranted('ROLE_ADMIN')]
class AdminProductController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly ProductImageRepository $images,
        private readonly CategoryRepository $categories,
        private readonly CollectionRepository $collections,
        private readonly EntityManagerInterface $em,
        private readonly CatalogPresenter $presenter,
    ) {
    }

    #[Route('', name: 'api_admin_products_create', methods: ['POST'], format: 'json')]
    public function create(#[MapRequestPayload] ProductWriteRequest $payload): JsonResponse
    {
        $product = new Product();
        $error = $this->apply($product, $payload, null);
        if (null !== $error) {
            return $error;
        }

        $this->em->persist($product);
        $this->em->flush();

        return $this->json($this->presenter->product($product), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_admin_products_update', methods: ['PUT'], requirements: ['id' => '\d+'], format: 'json')]
    public function update(int $id, #[MapRequestPayload] ProductWriteRequest $payload): JsonResponse
    {
        $product = $this->products->findOneActiveById($id);
        if (null === $product) {
            return $this->json(['error' => 'product_not_found'], Response::HTTP_NOT_FOUND);
        }

        $error = $this->apply($product, $payload, $id);
        if (null !== $error) {
            return $error;
        }

        $this->em->flush();

        return $this->json($this->presenter->product($product, $this->images->findForProduct($product)));
    }

    #[Route('/{id}', name: 'api_admin_products_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $product = $this->products->findOneActiveById($id);
        if (null === $product) {
            return $this->json(['error' => 'product_not_found'], Response::HTTP_NOT_FOUND);
        }

        $product->softDelete();
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Append a gallery image by URL (external/CDN, or an existing stored path).
     */
    #[Route('/{id}/images', name: 'api_admin_products_image_add', methods: ['POST'], requirements: ['id' => '\d+'], format: 'json')]
    public function addImageByUrl(int $id, #[MapRequestPayload] ProductImageUrlRequest $payload): JsonResponse
    {
        $product = $this->products->findOneActiveById($id);
        if (null === $product) {
            return $this->json(['error' => 'product_not_found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($this->appendImage($product, $payload->url), Response::HTTP_CREATED);
    }

    /**
     * Append a gallery image from an uploaded file.
     */
    #[Route('/{id}/images/upload', name: 'api_admin_products_image_upload', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function uploadImage(
        int $id,
        #[MapUploadedFile(new Assert\Image(
            maxSize: '5M',
            mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
            mimeTypesMessage: 'Please upload a JPEG, PNG or WebP image.',
        ))]
        UploadedFile $image,
        ProductImageStorage $storage,
    ): JsonResponse {
        $product = $this->products->findOneActiveById($id);
        if (null === $product) {
            return $this->json(['error' => 'product_not_found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($this->appendImage($product, $storage->store($image)), Response::HTTP_CREATED);
    }

    #[Route('/{id}/images/order', name: 'api_admin_products_image_order', methods: ['PUT'], requirements: ['id' => '\d+'], format: 'json')]
    public function reorderImages(int $id, #[MapRequestPayload] ProductImageOrderRequest $payload): JsonResponse
    {
        $product = $this->products->findOneActiveById($id);
        if (null === $product) {
            return $this->json(['error' => 'product_not_found'], Response::HTTP_NOT_FOUND);
        }

        $current = $this->images->findForProduct($product);
        $byId = [];
        foreach ($current as $image) {
            $byId[$image->getId()] = $image;
        }

        // The payload must be exactly the product's image ids, reordered (no
        // duplicates, no missing/extra ids).
        $wanted = $payload->ids;
        $actual = array_keys($byId);
        sort($wanted);
        sort($actual);
        if ($wanted !== $actual) {
            return $this->json(['error' => 'invalid_image_set'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        foreach ($payload->ids as $position => $imageId) {
            $byId[$imageId]->setSortOrder($position);
        }
        $this->em->flush();

        return $this->json($this->presenter->product($product, $this->images->findForProduct($product)));
    }

    #[Route('/{id}/images/{imageId}', name: 'api_admin_products_image_delete', methods: ['DELETE'], requirements: ['id' => '\d+', 'imageId' => '\d+'])]
    public function deleteImage(int $id, int $imageId, ProductImageStorage $storage): JsonResponse
    {
        $product = $this->products->findOneActiveById($id);
        if (null === $product) {
            return $this->json(['error' => 'product_not_found'], Response::HTTP_NOT_FOUND);
        }

        $image = $this->em->getRepository(ProductImage::class)->find($imageId);
        if (null === $image || $image->getProduct()?->getId() !== $product->getId()) {
            return $this->json(['error' => 'image_not_found'], Response::HTTP_NOT_FOUND);
        }

        $url = $image->getUrl();
        $this->em->remove($image);
        $this->em->flush();

        // Best-effort: removes the file only for locally stored uploads.
        $storage->remove($url);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Creates a ProductImage at the end of the gallery and returns its shape.
     *
     * @return array{id: int|null, url: string, sortOrder: int}
     */
    private function appendImage(Product $product, string $url): array
    {
        $image = (new ProductImage())
            ->setProduct($product)
            ->setUrl($url)
            ->setSortOrder($this->images->maxSortOrder($product) + 1);

        $this->em->persist($image);
        $this->em->flush();

        return ['id' => $image->getId(), 'url' => $image->getUrl(), 'sortOrder' => $image->getSortOrder()];
    }

    /**
     * Applies the payload to the product, resolving and validating the category
     * and collection and enforcing slug uniqueness. Returns an error response on
     * failure, or null on success.
     */
    private function apply(Product $product, ProductWriteRequest $payload, ?int $excludeId): ?JsonResponse
    {
        $category = $this->categories->findOneActiveById($payload->categoryId);
        if (null === $category) {
            return $this->json(['error' => 'invalid_category'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $collection = $this->collections->findOneActiveById($payload->collectionId);
        if (null === $collection) {
            return $this->json(['error' => 'invalid_collection'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $slug = Slugger::slugify($payload->name);
        if ('' === $slug) {
            return $this->json(['error' => 'invalid_name'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        if ($this->products->existsActiveBySlugExcludingId($slug, $excludeId)) {
            return $this->json(['error' => 'slug_already_used'], Response::HTTP_CONFLICT);
        }

        $product
            ->setName($payload->name)
            ->setSlug($slug)
            ->setPrice($payload->price)
            ->setOldPrice($payload->oldPrice)
            ->setOnSale($payload->onSale)
            ->setIsNew($payload->isNew)
            ->setInStock($payload->inStock)
            ->setFreeDelivery($payload->freeDelivery)
            ->setWarrantyYears($payload->warrantyYears)
            ->setMaterial($payload->material)
            ->setColor($payload->color)
            ->setDimensions($payload->dimensions)
            ->setDescription($payload->description)
            ->setCategory($category)
            ->setCollection($collection);

        return null;
    }
}
