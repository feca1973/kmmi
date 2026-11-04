<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * A text field holding the path of a file chosen in the file manager (assets/admin/file-manager.js adds the
 * "browse" button). `instance` is the file manager instance of fm_elfinder.yaml: images, gallery or attachments.
 *
 * The form type methods are fixed by the Symfony form interface.
 *
 * @SuppressWarnings("PHPMD.UnusedFormalParameter")
 */
final class FileChooserType extends AbstractType
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['attr']['data-file-chooser'] = $this->urlGenerator->generate(
            'elfinder',
            ['instance' => $options['instance']]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults(['instance' => 'images', 'required' => false])
            ->setAllowedTypes('instance', 'string');
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}
