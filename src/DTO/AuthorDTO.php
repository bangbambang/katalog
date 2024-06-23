<?php

namespace App\DTO;

use App\Entity\Author;
use DateTimeImmutable;
use Exception;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints;

class AuthorDTO implements ObjectDTO
{

    #[Groups(['author_list', 'author_detail'])]
    private ?Ulid $id = null;

    #[Groups(['author_list', 'author_detail'])]
    #[Constraints\NotBlank]
    private ?string $name = null;

    #[Groups(['author_detail'])]
    #[Constraints\NotBlank]
    private ?string $bio = null;

    #[Groups(['author_detail'])]
    #[Constraints\NotBlank]
    #[Constraints\Date]
    #[SerializedName('birth_date')]
    private ?string $birthDate = null;

    public function toEntity(): Author
    {
        $author = new Author();
        $author->setName($this->name);
        $author->setBio($this->bio);

        $author->setBirthDate(new DateTimeImmutable($this->birthDate));
        return $author;
    }

    public function fromEntity(Author $author): AuthorDTO
    {
        $this->id = $author->getId();
        $this->name = $author->getName();
        $this->bio = $author->getBio();
        $this->birthDate = $author->getBirthDate()->format('Y-m-d');
        return $this;
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): void
    {
        $this->bio = $bio;
    }

    public function getBirthDate(): ?string
    {
        return $this->birthDate;
    }

    /**
     * @throws Exception
     */
    public function setBirthDate(string $birthDate): void
    {
        $this->birthDate = $birthDate;
    }
}