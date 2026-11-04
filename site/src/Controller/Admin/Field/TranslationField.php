<?php

namespace App\Controller\Admin\Field;

use App\Form\Type\TranslationFormType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Contracts\Translation\TranslatableInterface;

/**
 * The translated fields (title, lead ...) of an entity with a translation table, edited in place.
 *
 * TranslationField::new()->addField('title', 'Név')->addField('lead', 'Bevezető', TextareaType::class)
 */
final class TranslationField implements FieldInterface
{
    use FieldTrait;

    public static function new(
        string $propertyName = 'translation',
        TranslatableInterface|string|bool|null $label = null
    ): self {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label ?? false)
            ->setTemplateName('crud/field/generic')
            ->setFormType(TranslationFormType::class)
            ->setFormTypeOption('fields', [])
            ->addCssClass('field-translation')
            ->onlyOnForms();
    }

    /**
     * @param class-string $type
     * @param array<string, mixed> $options
     */
    public function addField(string $name, string $label, string $type = TextType::class, array $options = []): self
    {
        $fields = $this->dto->getFormTypeOption('fields');
        $fields[$name] = ['type' => $type, 'options' => ['label' => $label] + $options];

        return $this->setFormTypeOption('fields', $fields);
    }
}
