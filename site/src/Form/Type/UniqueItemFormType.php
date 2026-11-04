<?php

namespace App\Form\Type;

use App\Entity\Unique\UniqueItem;
use App\Enum\UniqueType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The value of a site wide editable item ("unique item"); the field depends on the type of the item.
 */
final class UniqueItemFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $item = $event->getData();
            // The items themselves are created in the database, the admin only edits their values.
            if (!$item instanceof UniqueItem || $item->getId() === null) {
                return;
            }

            [$type, $options] = $this->contentField($item);
            $event->getForm()->add('content', $type, ['label' => $item->getTitle(), 'required' => false] + $options);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => UniqueItem::class, 'label' => false]);
    }

    /**
     * @return array{class-string, array<string, mixed>}
     */
    private function contentField(UniqueItem $item): array
    {
        return match ($item->getType()) {
            UniqueType::Text => [TextareaType::class, []],
            UniqueType::Elfinder => [FileChooserType::class, ['instance' => 'images']],
            UniqueType::Number => [IntegerType::class, []],
            UniqueType::Money => [MoneyType::class, ['currency' => 'HUF', 'scale' => 0]],
            UniqueType::Percent => [PercentType::class, []],
            UniqueType::Ckeditor => [RichTextType::class, []],
            default => [TextType::class, []],
        };
    }
}
