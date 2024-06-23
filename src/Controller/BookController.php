<?php

namespace App\Controller;

use App\DTO\BookDTO;
use App\Entity\Book;
use App\Repository\BookRepository;
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

class BookController extends AbstractController
{
    #[Route('/books', name: 'book_list', methods: ['GET'], format: 'json')]
    public function list(
        BookRepository $bookRepository,
        SerializerInterface $serializer,
        #[MapQueryParameter] int $page = 1,
        #[MapQueryParameter] int $pageSize = 10,
    ): JsonResponse {
        $result = $bookRepository->findBy([], ['id' => 'DESC'], $pageSize, $page - 1);
        $books = [];
        foreach ($result as $book) {
            $books[] = (new BookDTO())->fromEntity($book);
        }
        $response = $serializer->serialize(
            $books,
            'json',
            (new ObjectNormalizerContextBuilder())->withGroups(['book_list'])->toArray()
        );
        return new JsonResponse($response, Response::HTTP_OK, [], true);
    }

    #[Route('/books', name: 'book_create', methods: ['POST'], format: 'json')]
    public function add(
        #[MapRequestPayload] BookDTO $bookDTO,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
    ): JsonResponse {
        $entity = $bookDTO->toEntity();
        $entityManager->persist($entity);
        $entityManager->flush();
        $response = $serializer->serialize(
            $bookDTO->fromEntity($entity),
            'json',
            (new ObjectNormalizerContextBuilder())->withGroups(['book_detail'])->toArray()
        );

        return new JsonResponse($response, Response::HTTP_CREATED, [], true);
    }

    #[Route('/books/{bookId}', name: 'book_detail', methods: ['GET'], format: 'json')]
    public function detail(
        BookRepository $bookRepository,
        SerializerInterface $serializer,
        string $bookId,
    ): JsonResponse {
        try {
            $book = (new BookDTO())->fromEntity($bookRepository->find($bookId));
            $result = $serializer->serialize(
                $book,
                'json',
                (new ObjectNormalizerContextBuilder())->withGroups(['book_detail'])->toArray()
            );

            return new JsonResponse($result, Response::HTTP_OK, [], true);
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, [], true);
        }
    }

    #[Route('/books', name: 'book_update', methods: ['PUT'], format: 'json')]
    public function edit(
        #[MapRequestPayload] BookDTO $bookDTO,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
    ): JsonResponse {
        $entity = $entityManager->getRepository(Book::class)->find($bookDTO->getId());
        if (!$entity) {
            throw $this->createNotFoundException('no record of book with id ' . $bookDTO->getId());
        }
        $update = $bookDTO->toEntity();
        if ($bookDTO->getTitle() !== $update->getTitle()) {
            $entity->setName($bookDTO->getTitle());
        }
        if ($bookDTO->getPublishDate() !== $update->getPublishDate()) {
            $entity->setBirthDate($update->getPublishDate());
        }
        if ($bookDTO->getDescription() !== $update->getDescription()) {
            $entity->setBio($bookDTO->getDescription());
        }
        $entityManager->persist($entity);
        $entityManager->flush();
        $response = $serializer->serialize(
            $bookDTO->fromEntity($entity),
            'json',
            (new ObjectNormalizerContextBuilder())->withGroups(['book_detail'])->toArray()
        );

        return new JsonResponse($response, Response::HTTP_CREATED, [], true);
    }

    #[Route('/books/{bookId}', name: 'book_delete', methods: ['DELETE'], format: 'json')]
    public function delete(
        EntityManagerInterface $entityManager,
        string $bookId,
    ): JsonResponse {
        $entity = $entityManager->getRepository(Book::class)->find($bookId);
        if (!$entity) {
            throw $this->createNotFoundException('no record of book with id ' . $bookId);
        }
        $entityManager->remove($entity);
        $entityManager->flush();
        return new JsonResponse([], Response::HTTP_OK, [], false);
    }
}
