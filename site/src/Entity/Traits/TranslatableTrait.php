<?php

namespace App\Entity\Traits;

use BadMethodCallException;
use Doctrine\Common\Collections\Collection;

/**
 * For entities with a `<table>_translation` table (translatable_id + locale).
 *
 * The site is single language (see AGENTS.md), so the translation of the current locale is the Hungarian one.
 * The using entity declares the `translations` association (target class and mapping differ per entity)
 * and initializes it in its constructor.
 */
trait TranslatableTrait
{
    public const LOCALE = 'hu';

    /**
     * @return Collection<string, covariant object>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(object $translation): static
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->set($translation->getLocale(), $translation);
            $translation->setTranslatable($this);
        }

        return $this;
    }

    public function removeTranslation(object $translation): static
    {
        $this->translations->removeElement($translation);

        return $this;
    }

    /**
     * The translation of the site language; created (and attached) when it does not exist yet. The admin form edits
     * its fields as if they were fields of this entity. The translation class is `<Entity>Translation`.
     */
    public function getTranslation(): object
    {
        $translation = $this->translations->get(self::LOCALE);

        if ($translation === null) {
            $class = static::class . 'Translation';
            $translation = (new $class())->setLocale(self::LOCALE);
            $this->addTranslation($translation);
        }

        return $translation;
    }

    public function translate(): ?object
    {
        return $this->translations->get(self::LOCALE) ?? $this->translations->first() ?: null;
    }

    /**
     * Proxies unknown getters (getTitle(), getLead() ...) to the translation, so templates can use `item.title`
     * (Twig calls title() for it, so the getter of the same name is tried too).
     *
     * @param array<int, mixed> $arguments
     */
    public function __call(string $method, array $arguments): mixed
    {
        $translation = $this->translate();

        foreach ([$method, 'get' . ucfirst($method)] as $name) {
            if ($translation !== null && method_exists($translation, $name)) {
                return $translation->$name(...$arguments);
            }
        }

        throw new BadMethodCallException(sprintf('Method "%s::%s()" does not exist.', static::class, $method));
    }
}
