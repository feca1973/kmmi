<?php

namespace App\Entity\Article;

use App\Repository\Article\ArticleTypeRepository;
use App\Entity\Traits\BlameableTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Entity\Traits\TranslatableTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ArticleType
 *
 * @method getSlug()
 *
 * @method string|null getName()
 */
#[ORM\Table(name: 'articles_type')]
#[ORM\Entity(repositoryClass: ArticleTypeRepository::class)]
class ArticleType
{
    use TranslatableTrait;
    use IdTrait;
    use BlameableTrait;
    use TimestampableTrait;

    /**
     * The article types the public pages know by their ids (the ids of the live data).
     */
    public const NEWS_ID = 6;
    public const FOLK_ART_ID = 10;
    public const CHURCHES_ID = 12;

    /**
     * @var Collection<int, Article>
     */
    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'articleType', cascade: ['persist'])]
    protected Collection $articles;

    /**
     * @var Collection<string, ArticleTypeTranslation>
     */
    #[Assert\Valid]
    #[ORM\OneToMany(
        targetEntity: ArticleTypeTranslation::class,
        mappedBy: 'translatable',
        indexBy: 'locale',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    protected Collection $translations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->articles = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->getDefaultName();
    }

    public function getDefaultName(): ?string
    {
        return $this->getName();
    }

    public function addArticle(Article $article): ArticleType
    {
        $this->articles->add($article);

        return $this;
    }

    public function removeArticle(Article $article): ArticleType
    {
        $article->setArticleType(null);
        $this->articles->removeElement($article);

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }
}
