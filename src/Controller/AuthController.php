<?php

namespace App\Controller;

use App\Dto\UserRegister;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'auth_')]
class AuthController extends AbstractController
{
    #[Route('/auth/register', name: 'register', methods: ['POST'])]
    public function register(UserRegister $userRegister, UserRepository $userRepository): JsonResponse
    {
        return new JsonResponse(data: $userRepository->create($userRegister), status: Response::HTTP_CREATED);
    }
}
