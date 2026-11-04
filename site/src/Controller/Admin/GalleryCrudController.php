<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use App\Entity\Gallery\GalleryImage;
use App\Form\Type\GalleryImageFormType;
use App\Controller\Admin\Field\FileChooserField;
use App\Entity\Gallery\Gallery;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends LegacyCrudController<Gallery>
 */
final class GalleryCrudController extends LegacyCrudController
{
    protected const LABEL_SINGULAR = 'Galéria';
    protected const LABEL_PLURAL = 'Galériák';
    protected const INDEX_TITLE = 'Galériák';
    protected const EDIT_TITLE = 'Galéria szerkesztése';
    protected const NEW_LABEL = 'Új média hozzáadása';
    protected const NEW_ICON = 'fa fa-image';
    protected const SEARCH_FIELDS = ['name'];

    public static function getEntityFqcn(): string
    {
        return Gallery::class;
    }

    protected function indexFields(): iterable
    {
        yield TextField::new('name', 'Név');
        yield $this->activeField();
        yield ImageField::new('coverImage', 'Kép')->setBasePath('');
        yield AssociationField::new('galleryImages', 'Képek');
    }

    protected function formFields(): iterable
    {
        yield TextField::new('name', 'Név');
        yield $this->activeField();
        yield FileChooserField::new('coverImage', 'Bortítókép');
        yield CollectionField::new('galleryImages', 'Képek')
            ->setEntryType(GalleryImageFormType::class)
            ->setEntryIsComplex()
            ->setEntryToStringMethod(
                static fn (?GalleryImage $image): string => $image?->getName() ?: basename((string) $image?->getPath())
            )
            ->allowAdd()
            ->allowDelete()
            ->showEntryLabel()
            ->renderExpanded()
            ->addCssClass('kmmi-sortable')
            ->setFormTypeOption(
                'attr',
                ['data-kmmi-multi-add' => $this->generateUrl('elfinder', ['instance' => 'gallery'])]
            )
            ->setRequired(false);
    }
}
