<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Field\FileChooserField;
use App\Controller\Admin\Field\TranslationField;
use App\Form\Type\RichTextType;
use App\Entity\Article\Article;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

/**
 * @extends LegacyCrudController<Article>
 */
final class ArticleCrudController extends LegacyCrudController
{
    protected const LABEL_SINGULAR = 'Cikk';
    protected const LABEL_PLURAL = 'Cikkek';
    protected const INDEX_TITLE = 'Cikkek';
    protected const NEW_TITLE = 'Új tartalom';
    protected const EDIT_TITLE = 'Tartalom szerkesztése';
    protected const NEW_LABEL = 'Új tartalom hozzáadása';
    protected const NEW_ICON = 'fa fa-list';
    protected const DEFAULT_SORT = ['insertDate' => 'DESC'];
    protected const SEARCH_FIELDS = ['translations.title'];

    public static function getEntityFqcn(): string
    {
        return Article::class;
    }

    /**
     * The legacy list had a drop-down of the article types next to the search box.
     */
    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add(EntityFilter::new('articleType', 'Típus szűrő'));
    }

    protected function indexFields(): iterable
    {
        yield TextField::new('defaultTitle', 'Cím')->setSortable(false);
        yield AssociationField::new('articleType', 'Rovat');
        yield $this->dateTimeField('insertDate', 'Létrehozva');
        yield $this->activeField();
    }

    protected function formFields(): iterable
    {
        yield FormField::addTab('Szöveges tartalmak');
        yield TranslationField::new()
            ->addField('title', 'Név')
            ->addField('lead', 'Bevezető', RichTextType::class, ['required' => false])
            ->addField('content', 'Szöveg', RichTextType::class, ['required' => false])
            ->addField('authorName', 'Hírekhez: cikk szerzője', options: ['required' => false])
            ->addField('authorSource', 'Hírekhez: cikk forrása', options: ['required' => false])
            ->addField('bookAuthor', 'Könyvekhez: könyv szerzője', options: ['required' => false]);

        yield FormField::addTab('Beállítások, képek, dátumok');
        yield FormField::addFieldset('Általános beállítások', 'fa fa-box-archive');
        yield $this->associationField('articleType', 'Kategória', 'Válassz kategóriát')->setRequired(true);
        yield $this->associationField('city', 'Kapcsolódó település', 'Válassz települést')->setRequired(false);
        yield $this->dateTimeField('pubDate', 'Publikáció dátuma');
        yield $this->activeField();

        yield FormField::addFieldset('Arcképhez beállítások', 'fa fa-paintbrush');
        yield $this->associationField('actorType', 'Művészet típus', 'Válassz típust')->setRequired(false);
        yield TextField::new('actorBirthday', 'Művész születésnapja')->setRequired(false);

        yield FormField::addFieldset('Oktatási intézmény beállítások', 'fa fa-building-columns');
        yield $this->associationField('educationType', 'Oktatási intézmény típus', 'Válassz típust')
            ->setRequired(false);

        yield FormField::addFieldset('Könyvtár beállítások', 'fa fa-book');
        yield $this->associationField('bookCategory', 'Kiadvány típus', 'Válassz típust')->setRequired(false);
        yield TextField::new('bookReleaseYear', 'Kiadás éve')->setRequired(false);

        yield FormField::addFieldset('Képbeállítások', 'fa fa-image');
        yield $this->associationField('gallery', 'Galéria', 'Válassz a galériák közül')->setRequired(false);
        yield FileChooserField::new('mainImage', 'Főkép');
        yield $this->attachmentsField('Csatolmányok');
    }
}
