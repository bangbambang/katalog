<?php

namespace App\Controller;

use App\DTO\BookDTO;
use App\Repository\AuthorRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

class AuthorBooksController extends AbstractController
{
    #[Route('/authors/{authorId}/books', name: 'author_books', methods: ['GET'], format: 'json')]
    public function authorBooks(
        AuthorRepository $authorRepository,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        string $authorId,
    ): JsonResponse {
        $result = $authorRepository->find($authorId)->getBooks();
        $logger->info(json_encode($result, JSON_PRETTY_PRINT));
        $books = [];
        foreach ($result as $book) {
            $books[] = (new BookDTO())->fromEntity($book);
        }
        $logger->info(json_encode($books, JSON_PRETTY_PRINT));
        $response = $serializer->serialize(
            $books,
            'json',
            (new ObjectNormalizerContextBuilder())->withGroups(['book_detail', 'author_list'])->toArray()
        );
        return new JsonResponse($response, Response::HTTP_OK, [], true);
    }
}
