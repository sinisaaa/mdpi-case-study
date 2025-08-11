<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\ApiKey;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiKeyAuthenticator extends AbstractAuthenticator
{

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    /**
     * @param Request $request
     * @return bool|null
     */
    public function supports(Request $request): ?bool
    {
        return true;
    }

    /**
     * @param Request $request
     * @return Passport
     */
    public function authenticate(Request $request): Passport
    {
        $apiToken = $request->headers->get('X-API-KEY');
        if (null === $apiToken) {
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }

        $token = $this->em->getRepository(ApiKey::class)->findByToken($apiToken);

        if (null === $token || $token->isExpired()) {
            throw new CustomUserMessageAuthenticationException('Invalid API token');
        }

        return new SelfValidatingPassport(
            new UserBadge($apiToken, function (string $identifier) {
                return new InMemoryUser($identifier, null, []);
            })
        );
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $firewallName
     * @return Response|null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return Response|null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
