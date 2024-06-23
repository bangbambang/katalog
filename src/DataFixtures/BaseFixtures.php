<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

abstract class BaseFixtures extends Fixture
{
    protected Generator $faker;
    protected LoggerInterface $logger;
    protected ContainerBagInterface $parameters;
    private ObjectManager $manager;

    public function __construct(
        LoggerInterface $logger,
        ContainerBagInterface $parameters,
    ) {
        $this->logger = $logger;
        $this->parameters = $parameters;
    }

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;
        $this->faker = Factory::create();
        $this->loadData($manager);
    }

    abstract protected function loadData(ObjectManager $manager): void;

    protected function batchCreate(string $className, int $count, callable $factory): void
    {
        for ($i = 0; $i < $count; $i++) {
            $entity = new $className();
            $factory($entity, $i);
            $this->manager->persist($entity);
            $this->addReference("{$className}_{$i}", $entity);
        }
        $this->logger->info("Created {$count} records for {$className}");
    }
}
