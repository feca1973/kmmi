<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Field\TranslationField;
use App\Form\Type\RichTextType;
use App\Entity\Organization\Organization;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

/**
 * @extends LegacyCrudController<Organization>
 */
final class OrganizationCrudController extends LegacyCrudController
{
    protected const LABEL_SINGULAR = 'Szervezet';
    protected const LABEL_PLURAL = 'Szervezetek';
    protected const INDEX_TITLE = 'Szervezetek';
    protected const NEW_TITLE = 'Új szervezet';
    protected const EDIT_TITLE = 'Szervezet szerkesztése';
    protected const NEW_LABEL = 'Új szervezet hozzáadása';
    protected const NEW_ICON = 'fa fa-sitemap';
    protected const DEFAULT_SORT = ['updateDate' => 'DESC'];

    public static function getEntityFqcn(): string
    {
        return Organization::class;
    }

    protected function indexFields(): iterable
    {
        yield TextField::new('defaultTitle', 'Cím')->setSortable(false);
        yield AssociationField::new('type', 'Típus');
        yield AssociationField::new('city', 'Település');
        yield TextField::new('countryId', 'Ország');
        yield $this->dateTimeField('updateDate', 'Frissítve');
        yield $this->activeField();
    }

    protected function formFields(): iterable
    {
        yield FormField::addTab('Szöveges tartalmak');
        yield TranslationField::new()
            ->addField('title', 'Név')
            ->addField('introduction', 'Szervezet bemutatása', RichTextType::class, ['required' => false])
            ->addField('goal', 'Szervezet célja', RichTextType::class, ['required' => false])
            ->addField('fieldOfActivityId', 'Működési területe', RichTextType::class, ['required' => false])
            ->addField('services', 'Szervezet szolgáltatásai', RichTextType::class, ['required' => false])
            ->addField('officialPersonShortCv', 'Vezető rövid leírása', RichTextType::class, ['required' => false])
            ->addField('placeAddress', 'Helyszín címe', options: ['required' => false])
            ->addField('officialPersonName', 'Vezető neve', options: ['required' => false])
            ->addField('officialPersonFunction', 'Vezető tisztsége', options: ['required' => false])
            ->addField('contactPersonName', 'Kapcsolattartó neve', options: ['required' => false]);

        yield FormField::addTab('Beállítások, elérhetőségek, kapcsolattartás, képek, csatolmányok');
        yield FormField::addFieldset('Beállítások', 'fa fa-wrench');
        yield $this->associationField('type', 'Típus', 'Válassz típust');
        yield $this->associationField('city', 'Kapcsolódó település', 'Válassz települést');
        yield TextField::new('countryId', 'Országkód')->setRequired(false);
        yield $this->activeField();

        yield FormField::addFieldset('Elérhetőségek', 'fa fa-phone');
        yield EmailField::new('officialEmail', 'Szervezet e-mail címe')->setRequired(false);
        yield UrlField::new('contactUrl', 'Szervezet honlapja')->setRequired(false);
        yield TextField::new('institutionYear', 'Szervezet alapításának éve')->setRequired(false);
        yield TextField::new(
            'officialPersonTelephoneHome',
            'Otthoni telefonszám (+országhívó utána egyben az összes szám)'
        )->setRequired(false);
        yield TextField::new('officialPersonTelephoneOffice', 'Munkahelyi telefonszám (+országhívó)')
            ->setRequired(false);
        yield TextField::new('officialPersonTelephoneMobile', 'Munkahelyi mobilszám (+országhívó)')
            ->setRequired(false);
        yield TextField::new('officialPersonFax', 'Fax elérhetőség (+országhívó)')->setRequired(false);
        yield EmailField::new('officialPersonEmail', 'Vezető e-mail címe')->setRequired(false);

        yield FormField::addFieldset('Kapcsolattartó adatai', 'fa fa-phone');
        yield TextField::new('contactTelephoneOffice', 'Kapcsolattartó munkahelyi telefonszáma: (+országhívó)')
            ->setRequired(false);
        yield TextField::new('contactTelephoneMobile', 'Kapcsolattartó munkahelyi mobilszám (+országhívó)')
            ->setRequired(false);
        yield TextField::new('contactFax', 'Kapcsolattartó fax elérhetőség (+országhívó)')->setRequired(false);
        yield EmailField::new('contactEmail', 'Kapcsolattartó e-mail címe')->setRequired(false);

        yield FormField::addFieldset('Galériák', 'fa fa-image');
        yield $this->associationField('gallery', 'Galéria', 'Válassz a galériák közül')->setRequired(false);

        yield FormField::addFieldset('Csatolmányok', 'fa fa-paperclip');
        yield $this->attachmentsField('Csatolmányok');
    }
}
