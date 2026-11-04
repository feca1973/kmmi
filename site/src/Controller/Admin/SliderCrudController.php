<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Field\FileChooserField;
use App\Controller\Admin\Field\TranslationField;
use App\Entity\Slider\Slider;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends LegacyCrudController<Slider>
 */
final class SliderCrudController extends LegacyCrudController
{
    protected const LABEL_SINGULAR = 'Slider';
    protected const LABEL_PLURAL = 'Slider';
    protected const INDEX_TITLE = 'Slide-ok';
    protected const NEW_TITLE = 'Új slider';
    protected const EDIT_TITLE = 'Slider szerkesztése';
    protected const NEW_LABEL = 'Új slider hozzáadása';
    protected const NEW_ICON = 'fa fa-sliders';
    protected const DEFAULT_SORT = ['sorting' => 'ASC'];

    public static function getEntityFqcn(): string
    {
        return Slider::class;
    }

    protected function indexFields(): iterable
    {
        yield TextField::new('defaultTitle', 'Megnevezés')->setSortable(false);
        yield IntegerField::new('sorting', 'Sorrend');
        yield ImageField::new('image', 'Kép')->setBasePath('');
        yield DateTimeField::new('updateDate', 'Frissítve');
        yield $this->activeField();
    }

    protected function formFields(): iterable
    {
        yield TranslationField::new()
            ->addField('title', 'Cím')
            ->addField('subtitle', 'Alcím', options: ['required' => false])
            ->addField('url', 'URL', options: ['required' => false]);
        yield IntegerField::new('sorting', 'Sorrend (egy számmal jelezve)')->setRequired(false);
        yield $this->activeField();
        yield FileChooserField::new('image', 'Kép');
    }
}
