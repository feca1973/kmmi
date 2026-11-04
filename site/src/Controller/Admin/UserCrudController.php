<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @extends LegacyCrudController<User>
 */
final class UserCrudController extends LegacyCrudController
{
    protected const LABEL_SINGULAR = 'Felhasználó';
    protected const LABEL_PLURAL = 'Felhasználók';
    protected const INDEX_TITLE = 'Felhasználók listája';
    protected const NEW_LABEL = 'Új Felhasználó létrehozása';
    protected const NEW_ICON = 'fa fa-user';

    private const ROLES = [
        'ROLE_USER' => 'ROLE_USER',
        'ROLE_ADMIN' => 'ROLE_ADMIN',
        'ROLE_SUPER_ADMIN' => 'ROLE_SUPER_ADMIN',
    ];

    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', 'Név');
        yield TextField::new('plainPassword', 'Jelszó')
            ->setFormType(PasswordType::class)
            ->setFormTypeOptions(['required' => $pageName === Crud::PAGE_NEW, 'always_empty' => true])
            ->onlyOnForms();
        yield EmailField::new('email', 'Email');
        yield BooleanField::new('enabled', 'Aktív')->renderAsSwitch($pageName === Crud::PAGE_INDEX);
        yield ChoiceField::new('roles', 'Jogok')
            ->setChoices(self::ROLES)
            ->allowMultipleChoices()
            ->renderAsBadges();
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $this->hashPlainPassword($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $this->hashPlainPassword($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    /**
     * A filled-in password field replaces the stored hash (and clears the legacy salt); an empty one keeps it.
     */
    private function hashPlainPassword(object $user): void
    {
        if (!$user instanceof User || $user->getPlainPassword() === null || $user->getPlainPassword() === '') {
            return;
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPlainPassword()))
            ->setSalt(null)
            ->setPlainPassword(null);
    }
}
