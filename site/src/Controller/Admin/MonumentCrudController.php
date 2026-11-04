<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Field\FileChooserField;
use App\Controller\Admin\Field\RichTextField;
use App\Entity\Monuments\Monument;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends LegacyCrudController<Monument>
 */
final class MonumentCrudController extends LegacyCrudController
{
    protected const LABEL_SINGULAR = 'Emlékhely';
    protected const LABEL_PLURAL = 'Emlékhelyek';
    protected const INDEX_TITLE = 'Emlékhelyek, emlékművek';
    protected const NEW_TITLE = 'Új tartalom';
    protected const EDIT_TITLE = 'Tartalom szerkesztése';
    protected const NEW_LABEL = 'Új tartalom hozzáadása';
    protected const NEW_ICON = 'fa fa-compress';
    protected const SEARCH_FIELDS = ['name'];

    public static function getEntityFqcn(): string
    {
        return Monument::class;
    }

    protected function indexFields(): iterable
    {
        yield TextField::new('name', 'Cím');
        yield AssociationField::new('type', 'Típus');
        yield AssociationField::new('localization', 'Besorolás');
        yield $this->dateTimeField('insertDate', 'Létrehozva');
        yield $this->activeField();
    }

    protected function formFields(): iterable
    {
        yield FormField::addTab('Név, típus');
        yield TextField::new('name', 'Az emlékhely pontos neve, egyéb neve (ha van)');
        yield $this->associationField('type', 'Az emlékhely típusa', 'Válassz típust');
        yield $this->associationField('existing', 'Létező vagy eltűnt emlék(hely)', 'Válassz a listából');
        yield $this->associationField('localization', 'Az emlékhely besorolása', 'Válassz a listából');

        yield FormField::addTab('Helyszín');
        yield TextField::new('placeCity', 'A település neve magyarul');
        yield TextField::new('placeAddress', 'Utca, házszám, településrész (magyarul és ukránul)')
            ->setRequired(false);
        yield NumberField::new('lat', 'GPS (szélességi fok)')->setNumDecimals(8)->setRequired(false);
        yield NumberField::new('lng', 'GPS (hosszúsági fok)')->setNumDecimals(8)->setRequired(false);

        yield FormField::addTab('Információk');
        yield $this->dateField('createdDate', 'Készítés időpontja')->setRequired(false);
        yield $this->associationField('submitter', 'Készíttető neve', 'Válassz a listából')->setRequired(false);
        yield TextField::new('creatorName', 'Készítő neve');
        yield TextField::new('submitterPurpose', 'Készítés céljának ismert oka')->setRequired(false);
        yield RichTextField::new('monumentCaption', 'Felirata (ha van)');
        yield TextField::new('usageDate', 'Mettől meddig volt használatban')->setRequired(false);
        yield TextField::new('caringBy', 'Gondozását felügyeli')->setRequired(false);
        yield TextField::new('momumentCondition', 'Állapota');
        yield TextField::new('relatedEvents', 'Kapcsolódó rendezvények, ünnepségek')->setRequired(false);
        yield RichTextField::new('longDescription', 'Rövid ismertető')->setRequired(true);
        yield RichTextField::new('sourceInfo', 'Forrásinformációk');

        yield FormField::addTab('Fotók');
        yield FormField::addFieldset('Képbeállítások', 'fa fa-image');
        yield TextField::new('photoBy', 'Fotókat készítette')->setRequired(false);
        yield $this->associationField('gallery', 'Galéria', 'Válassz a galériák közül')->setRequired(false);
        yield FileChooserField::new('image', 'Főkép');

        yield FormField::addTab('Összeállította');
        yield TextField::new('compiledBy', 'Összállító neve');

        yield FormField::addTab('Beállítások');
        yield $this->activeField();
    }
}
