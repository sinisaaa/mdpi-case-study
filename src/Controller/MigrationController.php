<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MigrationController extends AbstractController
{
    /**
     * @TODO - exceute this migration only once as soon as we have our Author entity
     * ready, then we can remove this method.
     */
    #[Route('/admin/migrate-authors', name: 'admin_migrate_authors')]
    public function migrateAuthorsInline(EntityManagerInterface $em): Response
    {
        $bookRepo = $em->getRepository(Book::class);
        $authorRepo = $em->getRepository(Author::class);

        $books = $bookRepo->findAll();
        $created = 0;
        $linked = 0;

        foreach ($books as $book) {
            $fullname = $book->getAuthor();

            if (!$fullname) {
                continue;
            }

            // Naive split into first and last name
            $parts = explode(' ', $fullname, 2);
            $firstname = $parts[0];
            $lastname = $parts[1] ?? '';

            // Check if author already exists
            $author = $authorRepo->findOneBy([
                'firstName' => $firstname,
                'lastName' => $lastname,
            ]);

            if (!$author) {
                $author = new Author();
                $author->setFirstname($firstname);
                $author->setLastname($lastname);
                $em->persist($author);
                $created++;
            }

            // Link author to book
            $book->setAuthorEntity($author);
            $book->setAuthor(null); // optionally remove old string field
            $linked++;
        }

        $em->flush();

        return new Response("Done: $created authors created, $linked books updated.");
    }
}
