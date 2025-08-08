<?php

namespace App\Controller;

use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;


class BookController extends AbstractController
{
    #[Route('/books', name: 'books_index')]
    public function bookList(EntityManagerInterface $em, SerializerInterface $serializer): Response
    {
        $bookRepo = $em->getRepository(Book::class);
        $books = $bookRepo->findAll();

        if (!$books) {
            return $this->json(['message' => 'No books found'], Response::HTTP_NOT_FOUND);
        }

        $json = $serializer->serialize($books, 'json', ['groups' => ['book_list']]);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }
}
