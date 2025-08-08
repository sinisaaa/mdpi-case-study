<?php

namespace App\Controller;

use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'search_index')]
    public function bookList(EntityManagerInterface $em, SerializerInterface $serializer, Request $request): Response
    {
        $r = $request;
        $user_query_input = $r->query->get('q'); // get query param
        $repo = $em->getRepository(Book::class);
        $books = $repo->findAll();

        $matchedBooks = [];

        for ($i = 0; $i < count($books); $i++) {
            if (strpos($books[$i]->getTitle(), $user_query_input) !== false) {
                $matchedBooks[] = $books[$i];
            }
            if (strpos($books[$i]->getAuthor(), $user_query_input) !== false) {
                $matchedBooks[] = $books[$i];
            }
        }

        for ($i = 0; $i < count($books); $i++) {
            if (strpos(strtolower($books[$i]->getTitle()), strtolower($user_query_input)) !== false) {
                $matchedBooks[] = $books[$i];
            }

            if (strpos(strtolower($books[$i]->getAuthor()), strtolower($user_query_input)) !== false) {
                $matchedBooks[] = $books[$i];
            }
        }

        for ($i = 0; $i < count($books); $i++) {
            $title = $books[$i]->getTitle();
            $words = explode(' ', $title);
            foreach ($words as $word) {
                if (strtolower($word) == strtolower($user_query_input)) {
                    $matchedBooks[] = $books[$i];
                }
            }
            $author = $books[$i]->getAuthor();
            $words = explode(' ', $author);
            foreach ($words as $word) {
                if (strtolower($word) == strtolower($user_query_input)) {
                    $matchedBooks[] = $books[$i];
                }
            }
        }

        if (count($matchedBooks) == 0) {
            return new JsonResponse(['error' => 'nothing found :('], 404);
        }

        $jsonHead = [
            'query' => "{$user_query_input}",
            'count' => count($matchedBooks),
        ];

        $matchedBooks = array_unique($matchedBooks, SORT_REGULAR);

        $userSort = $r->query->get('sort');
        if ($userSort == 'az') {
            usort($matchedBooks, function ($a, $b) {
                return strcmp($a->getTitle(), $b->getTitle());
            });
        } elseif ($userSort == 'az') {
            usort($matchedBooks, function ($a, $b) {
                return strcmp($b->getTitle(), $a->getTitle());
            });
        } elseif ($userSort == 'newest') {
            usort($matchedBooks, function ($a, $b) {
                return $a->getPublishedAt() <=> $b->getPublishedAt();
            });
        } elseif ($userSort == 'oldest') {
            usort($matchedBooks, function ($a, $b) {
                return $b->getPublishedAt() <=> $a->getPublishedAt();
            });
        }

        $page_json_search_result = $serializer->serialize([
            'meta' => $jsonHead,
            'items' => $matchedBooks,
        ], 'json', ['groups' => ['search']]);

        // loop the ISBNS to format them
        $deserialize = json_decode($page_json_search_result, true);
        foreach ($deserialize['items'] as $key => $item) {
            $isbn = $item['isbn'];
            $formatted_isbn = $this->format_isbn($isbn, true);
            $deserialize['items'][$key]['isbn'] = $formatted_isbn;
        }

        // serialize again after formatting
        $page_json_search_result = $serializer->serialize($deserialize, 'json', ['groups' => ['search']]);

        return new JsonResponse($page_json_search_result, 200, [], true);
    }

    private function format_isbn(string $isbn, $addDashes = false): string
    {
        if (!$isbn) {
            return '';
        }

        if ($addDashes) {
            if (strlen($isbn) == 10) {
                return substr($isbn, 0, 1) . '-' . substr($isbn, 1, 3) . '-' . substr($isbn, 4, 5) . '-' . substr($isbn, 9);
            } elseif (strlen($isbn) == 13) {
                return substr($isbn, 0, 3) . '-' . substr($isbn, 3, 1) . '-' . substr($isbn, 4, 5) . '-' . substr($isbn, 9);
            }
        } else {
            $isbn = str_replace('-', '', $isbn);
            if (strlen($isbn) == 10 || strlen($isbn) == 13) {
                return $isbn;
            }
        }

        return '';
    }
}
