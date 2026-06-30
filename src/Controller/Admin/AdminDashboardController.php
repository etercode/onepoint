<?php

namespace App\Controller\Admin;

use App\Repository\CategoryRepository;
use App\Repository\CollectionRepository;
use App\Repository\ConsultationRepository;
use App\Repository\OrderItemRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Sales\SalesPresenter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Admin dashboard metrics (requires ROLE_ADMIN): headline stats, recent orders
 * and best-selling products, all computed live.
 */
#[Route('/api/admin/dashboard')]
#[IsGranted('ROLE_ADMIN')]
class AdminDashboardController extends AbstractController
{
    #[Route('', name: 'api_admin_dashboard', methods: ['GET'])]
    public function dashboard(
        OrderRepository $orders,
        OrderItemRepository $orderItems,
        ProductRepository $products,
        CategoryRepository $categories,
        CollectionRepository $collections,
        ConsultationRepository $consultations,
        SalesPresenter $presenter,
    ): JsonResponse {
        $topProducts = array_map(static fn (array $p): array => [
            'productId' => $p['productId'],
            'name' => $p['name'],
            'sales' => $p['units'],
            'revenue' => $p['revenue'],
        ], $orderItems->topProducts(5));

        return $this->json([
            'revenue' => $orders->sumRevenue(),
            'orders' => $orders->countActive(),
            'products' => $products->count(['deletedAt' => null]),
            'consultations' => $consultations->countByFilters('new'),
            'lowStock' => $products->count(['inStock' => false, 'deletedAt' => null]),
            'categories' => $categories->count(['deletedAt' => null]),
            'collections' => $collections->count(['deletedAt' => null]),
            'recentOrders' => $presenter->orders($orders->findRecent(5)),
            'topProducts' => $topProducts,
        ]);
    }
}
