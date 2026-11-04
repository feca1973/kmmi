<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Field\FileChooserField;
use App\Controller\Admin\Field\TranslationField;
use App\Form\Type\RichTextType;
use App\Entity\Event\Events;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

/**
 * @extends LegacyCrudController<Events>
 */
final class EventCrudController extends LegacyCrudController
{
    protected const LABEL_SINGULAR = 'Esemény';
    protected const LABEL_PLURAL = 'Események';
    protected const INDEX_TITLE = 'Események';
    protected const NEW_TITLE = 'Új esemény';
    protected const EDIT_TITLE = 'Esemény szerkesztése';
    protected const NEW_LABEL = 'Új esemény hozzáadása';
    protected const NEW_ICON = 'fa fa-calendar';
    protected const DEFAULT_SORT = ['dateFrom' => 'DESC'];

    public static function getEntityFqcn(): string
    {
        return Events::class;
    }

    protected function indexFields(): iterable
    {
        yield TextField::new('defaultTitle', 'Cím')->setSortable(false);
        yield AssociationField::new('type', 'Típus');
        yield AssociationField::new('city', 'Település');
        yield $this->dateTimeField('dateFrom', '-tól');
        yield $this->dateTimeField('dateTo', '-ig');
        yield $this->activeField();
    }

    protected function formFields(): iterable
    {
        yield FormField::addTab('Szöveges tartalmak');
        yield TranslationField::new()
            ->addField('title', 'Név')
            ->addField('lead', 'Bevezető', RichTextType::class, ['required' => false])
            ->addField('content', 'Szöveg', RichTextType::class, ['required' => false])
            ->addField('placeName', 'Helyszín neve', options: ['required' => false])
            ->addField('placeAddress', 'Helyszín címe', options: ['required' => false])
            ->addField('contactPerson', 'Kapcsolattartó személy(ek)', options: ['required' => false]);

        yield FormField::addTab('Beállítások, képek, dátumok');
        yield $this->associationField('type', 'Típus', 'Válassz típust');
        yield $this->associationField('city', 'Kapcsolódó település', 'Válassz települést');
        yield $this->associationField('place', 'Kapcsolódó hely', 'Válassz a meglévő helyek közül');
        yield AssociationField::new('organizations', 'Szervezetek (több is kiválasztható)')
            ->setRequired(false);
        yield $this->dateTimeField('dateFrom', 'Dátum (-tól)');
        yield $this->dateTimeField('dateTo', 'Dátum (-ig)');
        yield TextField::new('countryId', 'Országkód');
        yield $this->activeField();

        yield FormField::addFieldset('Kapcsolat adatok', 'fa fa-user');
        yield TextField::new('telephone', 'Telefonszám')->setRequired(false);
        yield EmailField::new('email', 'E-mail')->setRequired(false);
        yield TextField::new('fax', 'Fax')->setRequired(false);
        yield UrlField::new('url', 'Webcím')->setRequired(false);
        yield UrlField::new('placeUrl', 'A hely webcíme')->setRequired(false);

        yield FormField::addFieldset('Képbeállítások', 'fa fa-image');
        yield FileChooserField::new('image', 'Esemény képe, plakátja');
    }
}
