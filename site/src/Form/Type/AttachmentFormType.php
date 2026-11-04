<?php

namespace App\Form\Type;

use App\Entity\Attachment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * One attachment of an article, document, organization or media: a file chosen in the file manager and its name.
 *
 * The form type methods are fixed by the Symfony form interface.
 *
 * @SuppressWarnings("PHPMD.UnusedFormalParameter")
 */
final class AttachmentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('path', FileChooserType::class, ['label' => 'Fájl', 'instance' => 'attachments'])
            ->add('name', TextType::class, ['label' => 'Megnevezés', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Attachment::class]);
    }
}
