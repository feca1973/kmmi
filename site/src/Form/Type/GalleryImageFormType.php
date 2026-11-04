<?php

namespace App\Form\Type;

use App\Entity\Gallery\GalleryImage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * One image of a gallery: the file, its name and its position (set by the drag and drop sorting in the admin).
 *
 * The form type methods are fixed by the Symfony form interface.
 *
 * @SuppressWarnings("PHPMD.UnusedFormalParameter")
 */
final class GalleryImageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('path', FileChooserType::class, ['label' => 'Kép', 'instance' => 'images'])
            ->add('name', TextType::class, ['label' => 'Megnevezés', 'required' => false])
            ->add('position', HiddenType::class, ['attr' => ['data-position' => '1']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => GalleryImage::class]);
    }
}
