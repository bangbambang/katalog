<?php

namespace App\DTO;

use App\Entity\Book;
use DateTimeImmutable;
use Exception;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints;

class BookDTO implements ObjectDTO
{

    #[Groups(['book_list', 'book_detail'])]
    private ?Ulid $id = null;

    #[Groups(['book_list', 'book_detail'])]
    #[Constraints\NotBlank]
    private ?string $title = null;

    #[Groups(['book_list'])]
    #[Constraints\NotBlank]
    private ?string $description = null;

    #[Groups(['book_list', 'book_detail'])]
    #[Constraints\NotBlank]
    #[Constraints\Date]
    #[SerializedName('publish_date')]
    private ?string $publishDate = null;

    /**
     * @throws Exception
     */
    public function toEntity(): Book
    {
        $book = new Book();
        $book->setTitle($this->title);
        $book->setDescription($this->description);

        $book->setPublishDate(new DateTimeImmutable($this->publishDate));
        return $book;
    }

    public function fromEntity(Book $book): BookDTO
    {
        $this->id = $book->getId();
        $this->title = $book->getTitle();
        $this->description = $book->getDescription();
        $this->publishDate = $book->getPublishDate()->format('Y-m-d');
        return $this;
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getPublishDate(): ?string
    {
        return $this->publishDate;
    }

    /**
     * @throws Exception
     */
    public function setPublishDate(string $publishDate): void
    {
        $this->publishDate = $publishDate;
    }

}