<?php

namespace App\Controller\Admin;

use App\Entity\Region\Regions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends LegacyCrudController<Regions>
 */
final class RegionsCrudController extends LegacyCrudController
{
    protected const LABEL_SINGULAR = 'Régió';
    protected const LABEL_PLURAL = 'Régiók';
    protected const INDEX_TITLE = 'Régiók';
    protected const NEW_ICON = 'fa fa-road';
    protected const PAGE_SIZE = 20;
    protected const DEFAULT_SORT = ['title' => 'ASC'];
    protected const SEARCH_FIELDS = ['title'];

    public static function getEntityFqcn(): string
    {
        return Regions::class;
    }

    protected function formFields(): iterable
    {
        yield TextField::new('title', 'Név');
    }
}
