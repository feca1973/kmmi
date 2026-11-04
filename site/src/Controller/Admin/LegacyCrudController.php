<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use App\Entity\Attachment;
use App\Form\Type\AttachmentFormType;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;

/**
 * Common settings of the CRUDs that replace the legacy admin lists: the labels, the page title, the
 * "new" button, the page size, the default sort and the search fields are given by constants, so a
 * concrete controller only lists its fields. Every admin CRUD extends it, hence the many children.
 *
 * @SuppressWarnings("PHPMD.NumberOfChildren")
 *
 * @template TEntity of object
 * @extends AbstractCrudController<TEntity>
 */
abstract class LegacyCrudController extends AbstractCrudController
{
    protected const LABEL_SINGULAR = '';
    protected const LABEL_PLURAL = '';
    protected const INDEX_TITLE = '';
    protected const NEW_TITLE = '';
    protected const EDIT_TITLE = '';
    protected const NEW_LABEL = 'Új hozzáadása';
    protected const NEW_ICON = 'fa fa-plus';
    protected const PAGE_SIZE = 15;

    /**
     * @var array<string, string>
     */
    protected const DEFAULT_SORT = [];

    /**
     * Properties the search box looks in; an empty list switches the search off (as `disabled_actions: search`).
     *
     * @var list<string>
     */
    protected const SEARCH_FIELDS = [];

    /**
     * A concrete CRUD lists its form fields in formFields() and, when the list shows other columns,
     * its list columns in indexFields(). A CRUD that needs the page name overrides this method instead.
     *
     * @return iterable<FieldInterface>
     */
    public function configureFields(string $pageName): iterable
    {
        return $pageName === Crud::PAGE_INDEX ? $this->switchableIndexFields() : $this->formFields();
    }

    /**
     * The yes/no columns of the list are switches that can be changed in the list itself, as in the legacy admin (the
     * form has a plain check box).
     *
     * @return iterable<FieldInterface>
     */
    private function switchableIndexFields(): iterable
    {
        foreach ($this->indexFields() as $field) {
            yield $field instanceof BooleanField ? $field->renderAsSwitch() : $field;
        }
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud
            ->setEntityLabelInSingular(static::LABEL_SINGULAR)
            ->setEntityLabelInPlural(static::LABEL_PLURAL)
            ->setPageTitle(Crud::PAGE_INDEX, static::INDEX_TITLE)
            ->setPaginatorPageSize(static::PAGE_SIZE)
            ->showEntityActionsInlined()
            ->setSearchFields(static::SEARCH_FIELDS === [] ? null : static::SEARCH_FIELDS)
            ->setDateFormat('yyyy-MM-dd')
            ->setDateTimeFormat('yyyy-MM-dd HH:mm');

        if (static::DEFAULT_SORT !== []) {
            $crud->setDefaultSort(static::DEFAULT_SORT);
        }
        if (static::NEW_TITLE !== '') {
            $crud->setPageTitle(Crud::PAGE_NEW, static::NEW_TITLE);
        }
        if (static::EDIT_TITLE !== '') {
            $crud->setPageTitle(Crud::PAGE_EDIT, static::EDIT_TITLE);
        }

        return $crud;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::DETAIL, Action::BATCH_DELETE)
            ->update(
                Crud::PAGE_INDEX,
                Action::NEW,
                fn (Action $action) => $action->setLabel(static::NEW_LABEL)->setIcon(static::NEW_ICON)
            );
    }

    /**
     * @return iterable<FieldInterface>
     */
    protected function indexFields(): iterable
    {
        return $this->formFields();
    }

    /**
     * @return iterable<FieldInterface>
     */
    protected function formFields(): iterable
    {
        return [];
    }

    /**
     * A date and time field edited with the Hungarian date picker (a text input, not the browser's native widget).
     */
    protected function dateTimeField(string $property, string $label): DateTimeField
    {
        return DateTimeField::new($property, $label)
            ->renderAsText()
            ->setFormTypeOptions([
                'format' => 'yyyy-MM-dd HH:mm',
                'attr' => ['class' => 'datetimepicker', 'autocomplete' => 'off'],
            ]);
    }

    protected function dateField(string $property, string $label): DateField
    {
        return DateField::new($property, $label)
            ->renderAsText()
            ->setFormTypeOptions([
                'format' => 'yyyy-MM-dd',
                'attr' => ['class' => 'datepicker', 'autocomplete' => 'off'],
            ]);
    }

    /**
     * A drop-down of the related entities with a "choose" prompt, as the legacy select boxes.
     */
    protected function associationField(string $property, string $label, string $placeholder): AssociationField
    {
        return AssociationField::new($property, $label)->setFormTypeOption('placeholder', $placeholder);
    }

    /**
     * The attachments (files with a name) of an article, document, organization or media. Several files can be added
     * at once from the file manager.
     */
    protected function attachmentsField(string $label): CollectionField
    {
        return CollectionField::new('attachments', $label)
            ->setEntryType(AttachmentFormType::class)
            ->setEntryIsComplex()
            ->setEntryToStringMethod(
                static fn (?Attachment $attachment): string => $attachment?->getName()
                    ?: basename((string) $attachment?->getPath())
            )
            ->allowAdd()
            ->allowDelete()
            ->showEntryLabel()
            ->renderExpanded()
            ->setFormTypeOption(
                'attr',
                ['data-kmmi-multi-add' => $this->generateUrl('elfinder', ['instance' => 'attachments'])]
            )
            ->setRequired(false);
    }

    protected function activeField(): BooleanField
    {
        return BooleanField::new('active', 'Aktív')->renderAsSwitch(false);
    }
}
