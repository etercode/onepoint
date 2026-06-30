<?php

namespace App\Controller\Admin;

use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Admin customer list (requires ROLE_ADMIN). Customers are aggregated from
 * orders (grouped by email): order count, total spent and first-order date.
 */
#[Route('/api/admin/customers')]
#[IsGranted('ROLE_ADMIN')]
class AdminCustomerController extends AbstractController
{
    #[Route('', name: 'api_admin_customers_list', methods: ['GET'])]
    public function list(OrderRepository $orders): JsonResponse
    {
        return $this->json(['items' => $orders->aggregateCustomers()]);
    }
}
