<?php

namespace App\Controller\Admin;

use App\Entity\Monuments\MonumentSubmitter;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends LegacyCrudController<MonumentSubmitter>
 */
final class MonumentSubmitterCrudController extends LegacyCrudController
{
    protected const LABEL_SINGULAR = 'Emlékhely készíttető';
    protected const LABEL_PLURAL = 'Emlékhely készíttetők';
    protected const INDEX_TITLE = 'Emlékhely készíttetők';
    protected const EDIT_TITLE = 'Szerkesztés';
    protected const NEW_LABEL = 'Új elem hozzáadása';
    protected const NEW_ICON = 'fa fa-user-xmark';
    protected const PAGE_SIZE = 50;
    protected const SEARCH_FIELDS = ['name'];

    public static function getEntityFqcn(): string
    {
        return MonumentSubmitter::class;
    }

    protected function formFields(): iterable
    {
        yield TextField::new('name', 'Megnevezés');
    }
}
