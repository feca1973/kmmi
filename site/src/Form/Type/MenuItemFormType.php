<?php

namespace App\Form\Type;

use App\Entity\Menu\MenuItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * One item of a menu: its name, its target (a path of the site, `#` or an address; the editor's page finder helps to
 * find the pages of the site) and its sub items. The menu has up to MAX_LEVEL levels, as in the legacy admin.
 *
 * The form type methods are fixed by the Symfony form interface.
 *
 * @SuppressWarnings("PHPMD.UnusedFormalParameter")
 */
final class MenuItemFormType extends AbstractType
{
    public const MAX_LEVEL = 3;

    public function __construct(private readonly UrlGeneratorInterface $router)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Menüelem neve',
                // An empty value is "" (the setters do not take null), the NotBlank constraint reports it.
                'empty_data' => '',
                'attr' => ['placeholder' => 'Menüelem neve', 'data-menu-field' => 'title'],
                'constraints' => [new NotBlank(), new Length(max: 255)],
            ])
            ->add('url', TextType::class, [
                'label' => 'Oldal',
                'empty_data' => '',
                'attr' => [
                    'placeholder' => 'Keresés vagy URL',
                    'autocomplete' => 'off',
                    'data-menu-field' => 'url',
                    'data-kmmi-page-search' => $this->router->generate('admin_pages_for_menu'),
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(max: 255),
                    new Regex(
                        pattern: '~^(#|/(?!/)|https?://)~',
                        message: 'A cél a # jel (nincs hivatkozás), egy / jellel kezdődő útvonal vagy http(s):// cím.'
                    ),
                ],
            ]);

        if ($options['level'] < self::MAX_LEVEL) {
            $builder->add('children', MenuItemsFormType::class, ['level' => $options['level'] + 1]);
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['level'] = $options['level'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => MenuItem::class, 'level' => 1]);
        $resolver->setAllowedTypes('level', 'int');
    }

    public function getBlockPrefix(): string
    {
        return 'kmmi_menu_item';
    }
}
