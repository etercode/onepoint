<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiLoginController extends AbstractController
{
    /**
     * This is the json_login `check_path`. Authentication is performed by the
     * security firewall before the controller runs; on success the configured
     * LoginSuccessHandler returns the token and this body is never reached.
     */
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        return $this->json(
            ['error' => 'authentication_error', 'message' => 'Authentication failed.'],
            Response::HTTP_UNAUTHORIZED,
        );
    }
}
