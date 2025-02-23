<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SecureUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    private string $username;
    private string $password;
    private array $roles;

    public function __construct(string $username, string $password, array $roles)
    {
        $this->username = $username;
        $this->password = $password;
        $this->roles = $roles;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function eraseCredentials(): void
    {
        // Очищаем чувствительные данные, если нужно
    }
}
