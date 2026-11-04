<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * The admin login is an AJAX form: the answer is JSON, and the page redirects on success (as in the legacy system).
 * Some parameters are required by the handler interfaces but not needed here.
 *
 * @SuppressWarnings("PHPMD.UnusedFormalParameter")
 */
final class AjaxAuthenticationHandler implements
    AuthenticationSuccessHandlerInterface,
    AuthenticationFailureHandlerInterface
{
    use TargetPathTrait;

    private const FIREWALL = 'main';

    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
    {
        $session = $request->getSession();
        $targetPath = $this->getTargetPath($session, self::FIREWALL);
        $this->removeTargetPath($session, self::FIREWALL);

        return new JsonResponse([
            'success' => true,
            'forwardUrl' => $targetPath ?? $this->urlGenerator->generate('admin'),
        ]);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
    {
        return new JsonResponse(
            ['message' => strtr($exception->getMessageKey(), $exception->getMessageData())],
            Response::HTTP_FORBIDDEN
        );
    }
}
