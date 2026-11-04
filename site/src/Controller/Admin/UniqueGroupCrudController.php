<?php

namespace App\Controller\Admin;

use App\Entity\Unique\UniqueGroup;
use App\Form\Type\UniqueItemFormType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends LegacyCrudController<UniqueGroup>
 */
final class UniqueGroupCrudController extends LegacyCrudController
{
    protected const LABEL_SINGULAR = 'Egyedi tartalom';
    protected const LABEL_PLURAL = 'Egyedi tartalmak';
    protected const INDEX_TITLE = 'Egyedi tartalmak';
    protected const EDIT_TITLE = 'Egyedi tartalmak szerkesztése';

    public static function getEntityFqcn(): string
    {
        return UniqueGroup::class;
    }

    /**
     * The groups and their items are created in the database; the admin only edits the values (as in the legacy admin).
     */
    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)->disable(Action::NEW, Action::DELETE, Action::BATCH_DELETE);
    }

    protected function indexFields(): iterable
    {
        yield TextField::new('title', 'Csoport neve');
    }

    protected function formFields(): iterable
    {
        yield TextField::new('title', 'Csoport neve')->setFormTypeOption('attr', ['readonly' => 'readonly']);
        yield CollectionField::new('uniqueItems', 'Mezők')
            ->setEntryType(UniqueItemFormType::class)
            ->setEntryIsComplex()
            ->allowAdd(false)
            ->allowDelete(false)
            ->showEntryLabel(false)
            ->renderExpanded();
    }
}
