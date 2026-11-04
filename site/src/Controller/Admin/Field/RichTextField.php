<?php

namespace App\Controller\Admin\Field;

use App\Form\Type\RichTextType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Contracts\Translation\TranslatableInterface;

/**
 * An HTML text edited with CKEditor 5.
 */
final class RichTextField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName, TranslatableInterface|string|bool|null $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setTemplateName('crud/field/generic')
            ->setFormType(RichTextType::class)
            ->addCssClass('field-rich-text')
            ->setRequired(false)
            ->onlyOnForms();
    }
}
