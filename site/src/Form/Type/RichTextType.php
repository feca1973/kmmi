<?php

namespace App\Form\Type;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * A rich text (HTML) field edited with CKEditor 5 (assets/admin/ckeditor.js). Without a license key the field stays
 * a plain text area.
 *
 * The form type methods are fixed by the Symfony form interface.
 *
 * @SuppressWarnings("PHPMD.UnusedFormalParameter")
 */
final class RichTextType extends AbstractType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        #[Autowire(env: 'default::CKEDITOR_LICENSE_KEY')]
        private readonly ?string $licenseKey = null,
    ) {
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['attr'] += [
            'data-ckeditor' => '1',
            'data-license-key' => (string) $this->licenseKey,
            'data-images-url' => $this->urlGenerator->generate('elfinder', ['instance' => 'images']),
            'data-files-url' => $this->urlGenerator->generate('elfinder', ['instance' => 'attachments']),
            'data-upload-url' => $this->urlGenerator->generate(
                'ef_upload',
                ['instance' => 'images', 'response_format' => 'ckeditor5']
            ),
        ];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['required' => false, 'attr' => ['rows' => 8]]);
    }

    public function getParent(): string
    {
        return TextareaType::class;
    }
}
