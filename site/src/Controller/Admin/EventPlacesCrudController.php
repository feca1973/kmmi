<?php

namespace App\Controller\Admin;

use App\Entity\Event\EventPlaces;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends LegacyCrudController<EventPlaces>
 */
final class EventPlacesCrudController extends LegacyCrudController
{
    protected const LABEL_SINGULAR = 'Esemény helyszín';
    protected const LABEL_PLURAL = 'Esemény helyszínek';
    protected const INDEX_TITLE = 'Esemény helyszínek';
    protected const EDIT_TITLE = 'Esemény helyszín szerkesztése';
    protected const NEW_LABEL = 'Új Esemény helyszín létrehozása';
    protected const NEW_ICON = 'fa fa-calendar';

    public static function getEntityFqcn(): string
    {
        return EventPlaces::class;
    }

    protected function formFields(): iterable
    {
        yield TextField::new('name', 'Név');
        yield TextField::new('address', 'Cím')->setRequired(false);
        yield TextField::new('url', 'Webcím (URL)')->setRequired(false)->onlyOnForms();
    }
}
