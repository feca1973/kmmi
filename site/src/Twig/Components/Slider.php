<?php

namespace App\Twig\Components;

use App\Entity\Slider\Slider as SliderEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * The slider of the homepage.
 */
#[AsTwigComponent]
class Slider
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @return list<SliderEntity>
     */
    public function getSliders(): array
    {
        /** @var list<SliderEntity> $sliders */
        $sliders = $this->entityManager->getRepository(SliderEntity::class)
            ->findBy(['active' => true], ['sorting' => 'ASC']);

        return $sliders;
    }
}
