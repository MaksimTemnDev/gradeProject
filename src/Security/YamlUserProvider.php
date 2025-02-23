<?php
namespace App\Security;

use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Yaml\Yaml;

class YamlUserProvider implements UserProviderInterface
{
    private array $users;

    public function __construct()
    {
        $this->users = Yaml::parseFile(__DIR__ . '/../../config/users.yaml')['users'];
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        if (!isset($this->users[$identifier])) {
            throw new UserNotFoundException();
        }

        return new SecureUser($identifier, $this->users[$identifier]['password'], $this->users[$identifier]['roles']);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return $class === SecureUser::class;
    }
}
