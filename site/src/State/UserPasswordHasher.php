<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserPasswordHasher implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $persistProcessor,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof User) {
            $plainPassword = $data->getPlainPassword();
            if (is_string($plainPassword) && $plainPassword !== '') {
                $data->setPassword($this->passwordHasher->hashPassword($data, $plainPassword));
                $data->eraseCredentials();
            }
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
