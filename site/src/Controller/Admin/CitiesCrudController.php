<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Field\FileChooserField;
use App\Controller\Admin\Field\TranslationField;
use App\Form\Type\RichTextType;
use App\Entity\Region\Cities;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends LegacyCrudController<Cities>
 */
final class CitiesCrudController extends LegacyCrudController
{
    protected const LABEL_SINGULAR = 'Település';
    protected const LABEL_PLURAL = 'Települések';
    protected const INDEX_TITLE = 'Települések';
    protected const NEW_ICON = 'fa fa-home';
    protected const PAGE_SIZE = 50;
    protected const DEFAULT_SORT = ['regions.title' => 'ASC'];
    protected const SEARCH_FIELDS = ['translations.title', 'regions.title'];

    public static function getEntityFqcn(): string
    {
        return Cities::class;
    }

    protected function indexFields(): iterable
    {
        yield TextField::new('defaultTitle', 'Cím')->setSortable(false);
        yield AssociationField::new('regions', 'Régió');
        yield $this->activeField();
    }

    protected function formFields(): iterable
    {
        yield AssociationField::new('regions', 'Régiók');
        yield TranslationField::new()
            ->addField('title', 'Név')
            ->addField('lead', 'Líd', RichTextType::class, ['required' => false])
            ->addField('content', 'Szöveg', RichTextType::class, ['required' => false]);
        yield FileChooserField::new('image', 'Főkép');
        yield $this->activeField();
    }
}
