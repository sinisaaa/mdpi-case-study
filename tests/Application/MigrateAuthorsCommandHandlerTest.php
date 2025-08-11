<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Application\Command\MigrateAuthors\MigrateAuthorsCommand;
use App\Application\Command\MigrateAuthors\MigrateAuthorsCommandHandler;
use App\Entity\Author;
use App\Entity\Book;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;

final class MigrateAuthorsCommandHandlerTest extends \PHPUnit\Framework\TestCase
{
    private EntityManagerInterface|MockObject $entityManager;
    private BookRepository|MockObject $bookRepository;
    private AuthorRepository|MockObject $authorRepository;
    private MigrateAuthorsCommandHandler $handler;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->bookRepository = $this->createMock(BookRepository::class);
        $this->authorRepository = $this->createMock(AuthorRepository::class);

        $this->entityManager->method('getRepository')
            ->willReturnMap([
                [Book::class, $this->bookRepository],
                [Author::class, $this->authorRepository],
            ]);

        $this->handler = new MigrateAuthorsCommandHandler($this->entityManager);
    }

    public function testInvokeCreatesAndLinksAuthors(): void
    {
        $book1 = $this->createMock(Book::class);
        $book1->method('getAuthor')->willReturn('John Doe');
        $book2 = $this->createMock(Book::class);
        $book2->method('getAuthor')->willReturn('Jane Smith');

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('where')->willReturnSelf();

        $query = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['toIterable', 'getSQL', '_doExecute'])
            ->getMock();
        $query->method('toIterable')->willReturn([$book1, $book2]);

        $qb->method('getQuery')->willReturn($query);
        $this->bookRepository->method('createQueryBuilder')->willReturn($qb);

        $this->authorRepository->method('findOneBy')->willReturn(null);

        $this->entityManager->expects($this->exactly(2))->method('persist')->with($this->isInstanceOf(Author::class));
        $this->entityManager->expects($this->atLeastOnce())->method('flush');

        $book1->expects($this->once())->method('addAuthor')->with($this->isInstanceOf(Author::class));
        $book2->expects($this->once())->method('addAuthor')->with($this->isInstanceOf(Author::class));
        $book1->expects($this->once())->method('setAuthor')->with(null);
        $book2->expects($this->once())->method('setAuthor')->with(null);

        $result = ($this->handler)(new MigrateAuthorsCommand());

        $this->assertEquals(['created' => 2, 'linked' => 2], $result);
    }

    public function testInvokeSkipsBooksWithNullAuthor(): void
    {
        $book = $this->createMock(Book::class);
        $book->method('getAuthor')->willReturn(null);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('where')->willReturnSelf();

        $query = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['toIterable', 'getSQL', '_doExecute'])
            ->getMock();
        $query->method('toIterable')->willReturn([$book]);

        $qb->method('getQuery')->willReturn($query);
        $this->bookRepository->method('createQueryBuilder')->willReturn($qb);

        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $result = ($this->handler)(new MigrateAuthorsCommand());

        $this->assertEquals(['created' => 0, 'linked' => 0], $result);
    }
}
