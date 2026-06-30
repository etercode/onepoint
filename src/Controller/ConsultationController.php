<?php

namespace App\Controller;

use App\Dto\ConsultationRequest;
use App\Entity\Consultation;
use App\Sales\SalesPresenter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Public submission of design-consultation requests from the storefront.
 * Admins read/triage these via /api/admin/consultations.
 */
class ConsultationController extends AbstractController
{
    #[Route('/api/consultations', name: 'api_consultations_create', methods: ['POST'], format: 'json')]
    public function create(
        #[MapRequestPayload] ConsultationRequest $payload,
        EntityManagerInterface $em,
        SalesPresenter $presenter,
    ): JsonResponse {
        $consultation = (new Consultation())
            ->setName($payload->name)
            ->setPhone($payload->phone)
            ->setRoom($payload->room)
            ->setMessage($payload->message);

        $em->persist($consultation);
        $em->flush();

        return $this->json($presenter->consultation($consultation), Response::HTTP_CREATED);
    }
}
