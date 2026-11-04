<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Field\FileChooserField;
use App\Controller\Admin\Field\TranslationField;
use App\Form\Type\RichTextType;
use App\Entity\Doc\Doc;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends LegacyCrudController<Doc>
 */
final class DocCrudController extends LegacyCrudController
{
    protected const LABEL_SINGULAR = 'Dokumentum';
    protected const LABEL_PLURAL = 'Dokumentumok';
    protected const INDEX_TITLE = 'Dokumentumok';
    protected const NEW_ICON = 'fa fa-file-lines';

    public static function getEntityFqcn(): string
    {
        return Doc::class;
    }

    protected function indexFields(): iterable
    {
        yield TextField::new('defaultTitle', 'Cím')->setSortable(false);
        yield DateTimeField::new('insertDate', 'Létrehozva');
        yield DateTimeField::new('updateDate', 'Módosítva');
    }

    protected function formFields(): iterable
    {
        yield TranslationField::new()
            ->addField('title', 'Név')
            ->addField('lead', 'Líd', RichTextType::class, ['required' => false])
            ->addField('text', 'Szöveg', RichTextType::class, ['required' => false]);
        yield $this->activeField();
        yield FileChooserField::new('image', 'Főkép');
        yield $this->attachmentsField('Csatolmány');
    }
}
