<?php

namespace App\Controller\Admin;

use App\Dto\OrderStatusRequest;
use App\Entity\Order;
use App\Repository\OrderItemRepository;
use App\Repository\OrderRepository;
use App\Sales\SalesPresenter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Admin order management (requires ROLE_ADMIN). Orders are seeded; this exposes
 * listing, detail (with line items) and the status workflow.
 */
#[Route('/api/admin/orders')]
#[IsGranted('ROLE_ADMIN')]
class AdminOrderController extends AbstractController
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly OrderItemRepository $orderItems,
        private readonly EntityManagerInterface $em,
        private readonly SalesPresenter $presenter,
    ) {
    }

    #[Route('', name: 'api_admin_orders_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $status = $request->query->get('status');
        if (null !== $status && !\in_array($status, Order::STATUSES, true)) {
            return $this->json(['error' => 'invalid_status'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $q = $request->query->get('q');
        $limit = $request->query->has('limit') ? max(1, min(100, $request->query->getInt('limit'))) : null;
        $offset = max(0, $request->query->getInt('offset'));

        return $this->json([
            'items' => $this->presenter->orders($this->orders->findByFilters($status, $q, $limit, $offset)),
            'total' => $this->orders->countByFilters($status, $q),
        ]);
    }

    #[Route('/{id}', name: 'api_admin_orders_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $order = $this->orders->findOneActiveById($id);
        if (null === $order) {
            return $this->json(['error' => 'order_not_found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($this->presenter->orderDetail($order, $this->orderItems->findByOrder($order)));
    }

    #[Route('/{id}', name: 'api_admin_orders_update', methods: ['PATCH'], requirements: ['id' => '\d+'], format: 'json')]
    public function updateStatus(int $id, #[MapRequestPayload] OrderStatusRequest $payload): JsonResponse
    {
        $order = $this->orders->findOneActiveById($id);
        if (null === $order) {
            return $this->json(['error' => 'order_not_found'], Response::HTTP_NOT_FOUND);
        }

        $order->setStatus($payload->status);
        $this->em->flush();

        return $this->json($this->presenter->order($order));
    }
}
