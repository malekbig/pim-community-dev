<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Public;

use Akeneo\Connectivity\Connection\Application\Apps\Command\FlagAppContainingOutdatedScopesCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\FlagAppContainingOutdatedScopesHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByUserIdQueryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class FlagConnectedAppWithOutdatedScopesActionSpec extends ObjectBehavior
{
    public function let(
        TokenStorageInterface $tokenStorage,
        FindOneConnectedAppByUserIdQueryInterface $findOneConnectedAppByUserIdQuery,
        FlagAppContainingOutdatedScopesHandler $flagAppContainingOutdatedScopesHandler,
    ): void {
        $this->beConstructedWith(
            $tokenStorage,
            $findOneConnectedAppByUserIdQuery,
            $flagAppContainingOutdatedScopesHandler,
        );
    }

    public function it_throws_access_denied_http_exception_when_no_token_was_found(
        TokenStorageInterface $tokenStorage,
        Request $request,
    ): void {
        $tokenStorage->getToken()->willReturn(null);

        $this
            ->shouldThrow(new AccessDeniedHttpException('Not an authenticated App'))
            ->during('__invoke', [$request]);
    }

    public function it_throws_access_denied_http_exception_when_no_user_was_found(
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        Request $request,
    ): void {
        $token->getUser()->willReturn(null);
        $tokenStorage->getToken()->willReturn($token);

        $this
            ->shouldThrow(new AccessDeniedHttpException('Not an authenticated App'))
            ->during('__invoke', [$request]);
    }

    public function it_throws_logic_exception_when_user_returned_do_not_implement_user_interface(
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        Request $request,
    ): void {
        $token->getUser()->willReturn('not a user interface entity');
        $tokenStorage->getToken()->willReturn($token);

        $this
            ->shouldThrow(new \LogicException())
            ->during('__invoke', [$request]);
    }

    public function it_throws_access_denied_http_exception_when_no_connected_app_was_found(
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
        FindOneConnectedAppByUserIdQueryInterface $findOneConnectedAppByUserIdQuery,
        Request $request,
    ): void {
        $user->getId()->willReturn(42);
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);

        $findOneConnectedAppByUserIdQuery->execute(42)->willReturn(null);

        $this
            ->shouldThrow(new AccessDeniedHttpException('Not an authenticated App'))
            ->during('__invoke', [$request]);
    }

    public function it_flags_app_containing_outdated_scopes(
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
        FindOneConnectedAppByUserIdQueryInterface $findOneConnectedAppByUserIdQuery,
        FlagAppContainingOutdatedScopesHandler $flagAppContainingOutdatedScopesHandler,
        Request $request,
    ): void {
        $user->getId()->willReturn(42);
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);

        $connectedApp = new ConnectedApp(
            'id',
            'name',
            ['some', 'scopes'],
            'connection_code',
            'path/to/logo',
            'author',
            'user_group_name',
            'an_username',
        );

        $findOneConnectedAppByUserIdQuery->execute(42)->willReturn($connectedApp);

        $request->query = new InputBag(['scopes' => 'some other scopes']);

        $flagAppContainingOutdatedScopesHandler->handle(new FlagAppContainingOutdatedScopesCommand(
            $connectedApp,
            'some other scopes',
        ))->shouldBeCalled();

        $this->__invoke($request)->shouldBeLike(new JsonResponse('Ok'));
    }

    public function it_uses_empty_string_when_scopes_are_not_provided(
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
        FindOneConnectedAppByUserIdQueryInterface $findOneConnectedAppByUserIdQuery,
        FlagAppContainingOutdatedScopesHandler $flagAppContainingOutdatedScopesHandler,
        Request $request,
    ): void {
        $user->getId()->willReturn(42);
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);

        $connectedApp = new ConnectedApp(
            'id',
            'name',
            ['some', 'scopes'],
            'connection_code',
            'path/to/logo',
            'author',
            'user_group_name',
            'an_username',
        );

        $findOneConnectedAppByUserIdQuery->execute(42)->willReturn($connectedApp);

        $request->query = new InputBag();

        $flagAppContainingOutdatedScopesHandler->handle(new FlagAppContainingOutdatedScopesCommand(
            $connectedApp,
            '',
        ))->shouldBeCalled();

        $this->__invoke($request)->shouldBeLike(new JsonResponse('Ok'));
    }
}