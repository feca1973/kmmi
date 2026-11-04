<?php

namespace App\Controller\Admin;

use App\Entity\Monuments\MonumentType;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends LegacyCrudController<MonumentType>
 */
final class MonumentTypeCrudController extends LegacyCrudController
{
    protected const LABEL_SINGULAR = 'Emlékhely típus';
    protected const LABEL_PLURAL = 'Emlékhely típusok';
    protected const INDEX_TITLE = 'Emlékhely típusok';
    protected const EDIT_TITLE = 'Típus szerkesztése';
    protected const NEW_LABEL = 'Új típus hozzáadása';
    protected const NEW_ICON = 'fa fa-compress';
    protected const PAGE_SIZE = 50;

    public static function getEntityFqcn(): string
    {
        return MonumentType::class;
    }

    protected function indexFields(): iterable
    {
        yield TextField::new('name', 'Cím');
    }

    protected function formFields(): iterable
    {
        yield TextField::new('name', 'Megnevezés');
    }
}
