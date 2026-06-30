<?php

namespace App\Controller\Admin;

use App\Dto\ConsultationStatusRequest;
use App\Entity\Consultation;
use App\Repository\ConsultationRepository;
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
 * Admin triage of storefront consultation requests (requires ROLE_ADMIN).
 */
#[Route('/api/admin/consultations')]
#[IsGranted('ROLE_ADMIN')]
class AdminConsultationController extends AbstractController
{
    public function __construct(
        private readonly ConsultationRepository $consultations,
        private readonly EntityManagerInterface $em,
        private readonly SalesPresenter $presenter,
    ) {
    }

    #[Route('', name: 'api_admin_consultations_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $status = $request->query->get('status');
        if (null !== $status && !\in_array($status, Consultation::STATUSES, true)) {
            return $this->json(['error' => 'invalid_status'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $limit = $request->query->has('limit') ? max(1, min(100, $request->query->getInt('limit'))) : null;
        $offset = max(0, $request->query->getInt('offset'));

        return $this->json([
            'items' => $this->presenter->consultations($this->consultations->findByFilters($status, $limit, $offset)),
            'total' => $this->consultations->countByFilters($status),
        ]);
    }

    #[Route('/{id}', name: 'api_admin_consultations_update', methods: ['PATCH'], requirements: ['id' => '\d+'], format: 'json')]
    public function updateStatus(int $id, #[MapRequestPayload] ConsultationStatusRequest $payload): JsonResponse
    {
        $consultation = $this->consultations->findOneActiveById($id);
        if (null === $consultation) {
            return $this->json(['error' => 'consultation_not_found'], Response::HTTP_NOT_FOUND);
        }

        $consultation->setStatus($payload->status);
        $this->em->flush();

        return $this->json($this->presenter->consultation($consultation));
    }
}
