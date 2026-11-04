<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Edits the fields of a translation object (see TranslatableTrait::getTranslation()) inside the form of
 * the translated entity. The fields are given as `name => ['type' => FormType class, 'options' => [...]]`.
 */
final class TranslationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['fields'] as $name => $field) {
            // Explicit property path: without a data class the children would be read as array keys.
            $builder->add(
                $name,
                $field['type'] ?? TextType::class,
                ['property_path' => $name] + ($field['options'] ?? [])
            );
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('fields')
            ->setAllowedTypes('fields', 'array')
            ->setDefaults(['data_class' => null, 'label' => false]);
    }
}
