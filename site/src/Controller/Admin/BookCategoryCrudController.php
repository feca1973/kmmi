<?php

namespace App\Controller\Admin;

use App\Entity\Article\BookCategory;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends LegacyCrudController<BookCategory>
 */
final class BookCategoryCrudController extends LegacyCrudController
{
    protected const LABEL_SINGULAR = 'Könyvtártípus';
    protected const LABEL_PLURAL = 'Könyvtártípusok';
    protected const INDEX_TITLE = 'Könyvtártípusok';
    protected const EDIT_TITLE = 'Típus szerkesztése';
    protected const NEW_LABEL = 'Új típus hozzáadása';
    protected const NEW_ICON = 'fa fa-book';
    protected const PAGE_SIZE = 50;

    public static function getEntityFqcn(): string
    {
        return BookCategory::class;
    }

    protected function indexFields(): iterable
    {
        yield TextField::new('name', 'Cím');
        yield AssociationField::new('parent', 'Szülő kategória');
        yield $this->activeField();
    }

    protected function formFields(): iterable
    {
        yield TextField::new('name', 'Megnevezés');
        yield AssociationField::new('parent', 'Szülő típus')->setRequired(false);
        yield $this->activeField();
    }
}
