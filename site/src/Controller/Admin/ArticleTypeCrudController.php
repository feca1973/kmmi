<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Field\TranslationField;
use App\Entity\Article\ArticleType;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends LegacyCrudController<ArticleType>
 */
final class ArticleTypeCrudController extends LegacyCrudController
{
    protected const LABEL_SINGULAR = 'Cikkcsoport';
    protected const LABEL_PLURAL = 'Cikkcsoportok';
    protected const INDEX_TITLE = 'Cikkcsoport';
    protected const EDIT_TITLE = 'Cikkcsoportok szerkesztése';
    protected const NEW_ICON = 'fa fa-rectangle-list';

    public static function getEntityFqcn(): string
    {
        return ArticleType::class;
    }

    protected function indexFields(): iterable
    {
        yield TextField::new('defaultName', 'Név')->setSortable(false);
    }

    protected function formFields(): iterable
    {
        yield TranslationField::new()->addField('name', 'Név');
    }
}
