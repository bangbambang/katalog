<?php

namespace App\DataFixtures;

use App\Entity\Author;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class AuthorsFixtures extends BaseFixtures
{
    private int $authorsCount = 2000;

    public function __construct(LoggerInterface $logger, ContainerBagInterface $parameters)
    {
        parent::__construct($logger, $parameters);
        if ($this->parameters->has('app.fixtures.author_count')) {
            $this->authorsCount = $this->parameters->get('app.fixtures.author_count');
        } else {
            $this->authorsCount = 100;
        }
    }

    protected function loadData(ObjectManager $manager): void
    {
        $this->batchCreate(Author::class, $this->authorsCount, function (Author $author, int $count) {
            $author->setName($this->faker->name());
            $author->setBio($this->faker->text());
            $author->setBirthDate(
                DateTimeImmutable::createFromMutable($this->faker->dateTimeThisCentury())
            );
        });
        $manager->flush();
    }
}
