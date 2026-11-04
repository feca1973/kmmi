<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Enum\MediaType;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends LegacyCrudController<Media>
 */
final class MediaCrudController extends LegacyCrudController
{
    protected const LABEL_SINGULAR = 'Média';
    protected const LABEL_PLURAL = 'Média';
    protected const INDEX_TITLE = 'Média';
    protected const EDIT_TITLE = 'Média szerkesztése';
    protected const NEW_LABEL = 'Új média hozzáadása';
    protected const NEW_ICON = 'fa fa-play';
    protected const PAGE_SIZE = 50;

    public static function getEntityFqcn(): string
    {
        return Media::class;
    }

    protected function indexFields(): iterable
    {
        yield TextField::new('title', 'Cím');
        yield $this->activeField();
    }

    protected function formFields(): iterable
    {
        yield TextField::new('title', 'Cím');
        yield ChoiceField::new('type', 'Típus')->setChoices(MediaType::choices());
        yield $this->activeField();
        yield $this->attachmentsField('Csatolmány');
    }
}
