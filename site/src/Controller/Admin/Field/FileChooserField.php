<?php

namespace App\Controller\Admin\Field;

use App\Form\Type\FileChooserType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Contracts\Translation\TranslatableInterface;

/**
 * The path of a file chosen in the file manager (images by default).
 */
final class FileChooserField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName, TranslatableInterface|string|bool|null $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setTemplateName('crud/field/generic')
            ->setFormType(FileChooserType::class)
            ->addCssClass('field-file-chooser')
            ->setRequired(false)
            ->onlyOnForms();
    }

    /**
     * @param string $instance a file manager instance of fm_elfinder.yaml: images, gallery or attachments
     */
    public function setInstance(string $instance): self
    {
        return $this->setFormTypeOption('instance', $instance);
    }
}
