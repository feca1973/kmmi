<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

trait TimestampableTrait
{
    /**
     * @var DateTime
     */
    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(name: 'insert_date', type: Types::DATETIME_MUTABLE, nullable: false)]
    private ?DateTime $insertDate = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(name: 'update_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $updateDate = null;

    public function setInsertDate(?DateTime $insertDate): static
    {
        $this->insertDate = $insertDate;

        return $this;
    }

    public function getInsertDate(): ?DateTime
    {
        return $this->insertDate;
    }

    public function setUpdateDate(?DateTime $updateDate): static
    {
        $this->updateDate = $updateDate;

        return $this;
    }

    public function getUpdateDate(): ?DateTime
    {
        return $this->updateDate;
    }
}
