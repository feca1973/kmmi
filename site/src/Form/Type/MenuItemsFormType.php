<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The items of one level of a menu. The editor (assets/admin/menu-tree.js) numbers the rows of every list by their
 * place on the screen, so the first row is saved to the first item of the list and so on: the public menu lists the
 * items in the order of their ids, and the legacy table cannot get a position column.
 *
 * The form type methods are fixed by the Symfony form interface.
 *
 * @SuppressWarnings("PHPMD.UnusedFormalParameter")
 */
final class MenuItemsFormType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['level'] = $options['level'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'level' => 1,
            'entry_type' => MenuItemFormType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            // A row without a name and a target is an error, not a row to drop silently.
            'delete_empty' => false,
            'label' => false,
        ]);
        $resolver->setAllowedTypes('level', 'int');
        $resolver->setNormalizer(
            'entry_options',
            static fn (Options $options, array $entryOptions): array => $entryOptions + [
                'label' => false,
                'level' => $options['level'],
            ]
        );
    }

    public function getParent(): string
    {
        return CollectionType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'kmmi_menu_items';
    }
}
