<?php

namespace App\Entity\Article;

use App\Repository\Article\ArticleRepository;
use App\Enum\ArticleStatus;
use Doctrine\DBAL\Types\Types;
use DateTimeInterface;
use App\Entity\Traits\TranslatableTrait;
use App\Entity\Attachment;
use App\Entity\Gallery\Gallery;
use App\Entity\Region\Cities;
use App\Entity\Traits\IdTrait;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Traits\ActivableTrait;
use App\Entity\Traits\BlameableTrait;
use App\Entity\Traits\TimestampableTrait;

/**
 * Articles
 *
 * @method string|null getTitle()
 * @method string|null getSlug()
 * @method string|null getLead()
 * @method string|null getContent()
 * @method string|null getAuthorName()
 * @method string|null getAuthorSource()
 * @method string|null getBookAuthor()
 */
#[ORM\Table(name: 'articles')]
#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    use TranslatableTrait;
    use IdTrait;
    use BlameableTrait;
    use TimestampableTrait;
    use ActivableTrait;

    #[ORM\Column(name: 'main_image', type: Types::STRING, length: 255, nullable: true)]
    private ?string $mainImage = null;

    #[ORM\Column(name: 'sub_image', type: Types::STRING, length: 255, nullable: true)]
    private ?string $subImage = null;

    /**
     * @var DateTime
     */
    #[ORM\Column(name: 'pub_date', type: Types::DATETIME_MUTABLE, nullable: false)]
    private DateTimeInterface $pubDate;

    // The column comment is a legacy (Doctrine 2 type hint) leftover; kept so the schema diff stays empty.
    #[ORM\Column(
        name: 'status',
        type: Types::ENUM,
        nullable: true,
        enumType: ArticleStatus::class,
        options: ['comment' => '(DC2Type:ArticleStatusType)'],
    )]
    private ?ArticleStatus $status = null;

    #[ORM\JoinColumn(name: 'gallery_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Gallery::class, inversedBy: 'articles')]
    private ?Gallery $gallery = null;

    #[ORM\JoinColumn(name: 'articles_type_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: ArticleType::class, inversedBy: 'articles')]
    private ?ArticleType $articleType = null;

    /**
     * @var Collection<string, ArticleTranslation>
     */
    #[Assert\Valid]
    #[ORM\OneToMany(
        targetEntity: ArticleTranslation::class,
        mappedBy: 'translatable',
        indexBy: 'locale',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    protected Collection $translations;

    #[ORM\JoinColumn(name: 'city_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Cities::class, inversedBy: 'articles')]
    private ?Cities $city = null;

    #[ORM\JoinColumn(name: 'actor_type_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: ActorType::class, inversedBy: 'articles')]
    private ?ActorType $actorType = null;

    #[ORM\Column(
        name: 'actor_birthday',
        type: Types::STRING,
        length: 127,
        nullable: true,
        options: ['charset' => 'utf8mb3', 'collation' => 'utf8mb3_general_ci'],
    )]
    private ?string $actorBirthday = null;

    #[ORM\JoinColumn(name: 'education_type_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: EducationType::class, inversedBy: 'articles')]
    private ?EducationType $educationType = null;

    #[ORM\JoinColumn(name: 'book_category_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: BookCategory::class, inversedBy: 'articles')]
    private ?BookCategory $bookCategory = null;

    #[ORM\Column(name: 'book_release_year', type: Types::STRING, length: 10, nullable: true)]
    private ?string $bookReleaseYear = null;

    /**
     * @var Collection<int, Attachment>
     */
    #[ORM\OneToMany(
        targetEntity: Attachment::class,
        mappedBy: 'article',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    private Collection $attachments;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->attachments = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->getDefaultTitle();
    }

    public function getDefaultTitle(): ?string
    {
        return $this->getTitle();
    }

    public function setMainImage(?string $mainImage): Article
    {
        $this->mainImage = $mainImage;

        return $this;
    }

    public function getMainImage(): ?string
    {
        return $this->mainImage;
    }

    public function setSubImage(?string $subImage): Article
    {
        $this->subImage = $subImage;

        return $this;
    }


    public function getSubImage(): ?string
    {
        return $this->subImage;
    }

    public function setPubDate(?DateTime $pubDate): Article
    {
        $this->pubDate = $pubDate;

        return $this;
    }


    public function getPubDate(): ?DateTime
    {
        return $this->pubDate;
    }


    public function setGallery(?Gallery $gallery): Article
    {
        $this->gallery = $gallery;

        return $this;
    }

    public function getGallery(): ?Gallery
    {
        return $this->gallery;
    }

    public function setStatus(?ArticleStatus $status): void
    {
        $this->status = $status;
    }

    public function getStatus(): ?ArticleStatus
    {
        return $this->status;
    }

    public function setArticleType(?ArticleType $articleType): Article
    {
        $this->articleType = $articleType;

        return $this;
    }

    public function getArticleType(): ?ArticleType
    {
        return $this->articleType;
    }

    public function getCity(): ?Cities
    {
        return $this->city;
    }

    public function setCity(?Cities $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getActorType(): ?ActorType
    {
        return $this->actorType;
    }

    public function setActorType(?ActorType $actorType): self
    {
        $this->actorType = $actorType;

        return $this;
    }

    public function getActorBirthday(): ?string
    {
        return $this->actorBirthday;
    }

    public function setActorBirthday(?string $actorBirthday): self
    {
        $this->actorBirthday = $actorBirthday;

        return $this;
    }

    public function getEducationType(): ?EducationType
    {
        return $this->educationType;
    }

    public function setEducationType(?EducationType $educationType): self
    {
        $this->educationType = $educationType;

        return $this;
    }

    public function getBookCategory(): ?BookCategory
    {
        return $this->bookCategory;
    }

    public function setBookCategory(?BookCategory $bookCategory): self
    {
        $this->bookCategory = $bookCategory;

        return $this;
    }

    public function getBookReleaseYear(): ?string
    {
        return $this->bookReleaseYear;
    }

    public function setBookReleaseYear(?string $bookReleaseYear): self
    {
        $this->bookReleaseYear = $bookReleaseYear;

        return $this;
    }

    /**
     * @return Collection<int, Attachment>
     */
    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    public function addAttachment(Attachment $attachment): self
    {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments->add($attachment);
            $attachment->setArticle($this);
        }

        return $this;
    }

    public function removeAttachment(Attachment $attachment): self
    {
        if ($this->attachments->contains($attachment)) {
            $this->attachments->removeElement($attachment);
            // set the owning side to null (unless already changed)
            if ($attachment->getArticle() === $this) {
                $attachment->setArticle(null);
            }
        }

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->mainImage;
    }
}
