<?php

namespace App\Controller;

use App\DTO\AuthorDTO;
use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

class AuthorController extends AbstractController
{
    #[Route('/authors', name: 'author_list', methods: ['GET'], format: 'json')]
    public function list(
        AuthorRepository $authorRepository,
        SerializerInterface $serializer,
        #[MapQueryParameter] int $page = 1,
        #[MapQueryParameter(name: 'page_size')] int $pageSize = 10,
    ): JsonResponse {
        $result = $authorRepository->findBy([], ['id' => 'DESC'], $pageSize, $page - 1);
        $authors = [];
        foreach ($result as $author) {
            $authors[] = (new AuthorDTO())->fromEntity($author);
        }
        $response = $serializer->serialize(
            $authors,
            'json',
            (new ObjectNormalizerContextBuilder())->withGroups(['author_list'])->toArray()
        );
        return new JsonResponse($response, Response::HTTP_OK, [], true);
    }

    #[Route('/authors', name: 'authors_create', methods: ['POST'], format: 'json')]
    public function add(
        #[MapRequestPayload] AuthorDTO $authorDTO,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
    ): JsonResponse {
        $entity = $authorDTO->toEntity();
        $entityManager->persist($entity);
        $entityManager->flush();
        $response = $serializer->serialize(
            $authorDTO->fromEntity($entity),
            'json',
            (new ObjectNormalizerContextBuilder())->withGroups(['author_detail'])->toArray()
        );

        return new JsonResponse($response, Response::HTTP_CREATED, [], true);
    }

    #[Route('/authors/{authorId}', name: 'author_detail', methods: ['GET'], format: 'json')]
    public function detail(
        AuthorRepository $authorRepository,
        SerializerInterface $serializer,
        string $authorId,
    ): JsonResponse {
        try {
            $author = (new AuthorDTO())->fromEntity($authorRepository->find($authorId));
            $result = $serializer->serialize(
                $author,
                'json',
                (new ObjectNormalizerContextBuilder())->withGroups(['author_detail'])->toArray()
            );

            return new JsonResponse($result, Response::HTTP_OK, [], true);
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, [], true);
        }
    }

    #[Route('/authors', name: 'authors_update', methods: ['PUT'], format: 'json')]
    public function edit(
        #[MapRequestPayload] AuthorDTO $authorDTO,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
    ): JsonResponse {
        $entity = $entityManager->getRepository(Author::class)->find($authorDTO->getId());
        if (!$entity) {
            throw $this->createNotFoundException('no record of author found for id ' . $authorDTO->getId());
        }
        $update = $authorDTO->toEntity();
        if ($authorDTO->getName() !== $update->getName()) {
            $entity->setName($authorDTO->getName());
        }
        if ($authorDTO->getBirthDate() !== $update->getBirthDate()) {
            $entity->setBirthDate($update->getBirthDate());
        }
        if ($authorDTO->getBio() !== $update->getBio()) {
            $entity->setBio($authorDTO->getBio());
        }
        $entityManager->persist($entity);
        $entityManager->flush();
        $response = $serializer->serialize(
            $authorDTO->fromEntity($entity),
            'json',
            (new ObjectNormalizerContextBuilder())->withGroups(['author_detail'])->toArray()
        );

        return new JsonResponse($response, Response::HTTP_CREATED, [], true);
    }

    #[Route('/authors/{authorId}', name: 'authors_delete', methods: ['DELETE'], format: 'json')]
    public function delete(
        EntityManagerInterface $entityManager,
        string $authorId,
    ): JsonResponse {
        $entity = $entityManager->getRepository(Author::class)->find($authorId);
        if (!$entity) {
            throw $this->createNotFoundException('no record of author found for id ' . $authorId);
        }
        $entityManager->remove($entity);
        $entityManager->flush();
        return new JsonResponse([], Response::HTTP_OK, [], false);
    }
}
