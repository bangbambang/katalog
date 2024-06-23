<?php

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Book;
use DateTimeImmutable;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class BooksFixtures extends BaseFixtures implements DependentFixtureInterface
{
    private int $booksCount;
    private int $authorsCount;

    public function __construct(LoggerInterface $logger, ContainerBagInterface $parameters)
    {
        parent::__construct($logger, $parameters);
        if ($this->parameters->has('app.fixtures.book_count')) {
            $this->booksCount = $this->parameters->get('app.fixtures.book_count');
        } else {
            $this->booksCount = 200;
        }
        if ($this->parameters->has('app.fixtures.author_count')) {
            $this->authorsCount = $this->parameters->get('app.fixtures.author_count');
        } else {
            $this->authorsCount = 100;
        }
    }

    public function getDependencies()
    {
        return [
            AuthorsFixtures::class,
        ];
    }

    protected function loadData(ObjectManager $manager): void
    {
        $this->batchCreate(Book::class, $this->booksCount, function (Book $book, int $count): void {
            $book->setTitle($this->faker->sentence());
            $book->setDescription($this->faker->paragraphs(2, true));
            $book->setPublishDate(
                DateTimeImmutable::createFromMutable($this->faker->dateTimeThisCentury())
            );
            for ($i = 0; $i < random_int(1, 3); $i++) {
                $author_id = random_int(1, $this->authorsCount - 1);
                $book->addAuthor($this->getReference(Author::class . '_' . $author_id, Author::class));
            }
        });
        $manager->flush();
    }
}
