<?php

namespace App\Entity;

use App\Repository\TransferRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=TransferRepository::class)
 */
class Transfer
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidV4Generator::class)
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Email(
     *      message = "Cette adresse '{{ value }}' n\'est pas une adresse email valide."
     * )
     */
    private $recipient;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $filename;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     */
    private $uploaded;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     */
    private $sended;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getRecipient(): ?string
    {
        return $this->recipient;
    }

    public function setRecipient(string $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getUploaded(): ?bool
    {
        return $this->uploaded;
    }

    public function setUploaded(bool $uploaded): self
    {
        $this->uploaded = $uploaded;

        return $this;
    }

    public function getSended(): ?bool
    {
        return $this->sended;
    }

    public function setSended(bool $sended): self
    {
        $this->sended = $sended;

        return $this;
    }
}
