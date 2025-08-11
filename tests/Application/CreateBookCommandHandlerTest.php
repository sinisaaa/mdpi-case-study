<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Application\Command\CreateBook\CreateBookCommand;
use App\Application\Command\CreateBook\CreateBookCommandHandler;
use App\DTO\AuthorDto;
use App\Entity\Author;
use App\Entity\Book;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CreateBookCommandHandlerTest extends TestCase
{
    private EntityManagerInterface|MockObject $entityManager;
    private AuthorRepository|MockObject $authorRepository;
    private CreateBookCommandHandler $handler;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->authorRepository = $this->createMock(AuthorRepository::class);

        $this->entityManager->method('getRepository')
            ->with(Author::class)
            ->willReturn($this->authorRepository);

        $this->handler = new CreateBookCommandHandler($this->entityManager);
    }

    public function testHandleCreatesNewBookWithNewAuthors(): void
    {
        $authorDto = new AuthorDto();
        $authorDto->firstname = 'John';
        $authorDto->lastname = 'Doe';
        $authorDto->title = 'Mr';

        $command = new CreateBookCommand(
            'Test Book',
            [$authorDto],
            new \DateTime('2023-01-01'),
            '1234567890123',
        );

        $this->authorRepository->expects($this->once())
            ->method('findOneByName')
            ->with('John', 'Doe')
            ->willReturn(null);

        $this->entityManager->expects($this->exactly(2))
            ->method('persist')
            ->willReturnCallback(function ($entity) {
                static $callCount = 0;
                $callCount++;
                
                if ($callCount === 1) {
                    $this->assertInstanceOf(Author::class, $entity);
                } else {
                    $this->assertInstanceOf(Book::class, $entity);
                }
            });

        $this->entityManager->expects($this->once())
            ->method('flush');

        $book = ($this->handler)($command);

        $this->assertInstanceOf(Book::class, $book);
        $this->assertEquals('Test Book', $book->getTitle());
        $this->assertCount(1, $book->getAuthors());
    }

    public function testHandleCreatesBookWithExistingAuthor(): void
    {
        $authorDto = new AuthorDto();
        $authorDto->firstname = 'John';
        $authorDto->lastname = 'Doe';
        $authorDto->title = 'Mr';

        $existingAuthor = Author::create('John', 'Doe', 'Mr');
        $command = new CreateBookCommand(
            'Existing Author Book',
            [$authorDto],
            new \DateTime('2023-02-01'),
            '1234567890124',
        );

        $this->authorRepository->expects($this->once())
            ->method('findOneByName')
            ->with('John', 'Doe')
            ->willReturn($existingAuthor);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Book::class));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $book = ($this->handler)($command);

        $this->assertInstanceOf(Book::class, $book);
        $this->assertCount(1, $book->getAuthors());
        $this->assertSame($existingAuthor, $book->getAuthors()->first());
    }

    public function testHandleDeduplicatesAuthors(): void
    {
        $authorDto1 = new AuthorDto();
        $authorDto1->firstname = 'John';
        $authorDto1->lastname = 'Doe';
        $authorDto1->title = 'Mr';

        $authorDto2 = new AuthorDto();
        $authorDto2->firstname = 'John';
        $authorDto2->lastname = 'Doe';
        $authorDto2->title = 'Mr';

        $command = new CreateBookCommand(
            'Book with Duplicate Authors',
            [$authorDto1, $authorDto2],
            new \DateTime('2023-03-01'),
            '1234567890125',
        );

        $this->authorRepository->expects($this->once())
            ->method('findOneByName')
            ->with('John', 'Doe')
            ->willReturn(null);

        $this->entityManager->expects($this->exactly(2))
            ->method('persist');

        $book = ($this->handler)($command);
        $this->assertCount(1, $book->getAuthors());
    }

    public function testHandleWithMultipleAuthors(): void
    {
        $authorDto1 = new AuthorDto();
        $authorDto1->firstname = 'John';
        $authorDto1->lastname = 'Doe';
        $authorDto1->title = 'Mr';

        $authorDto2 = new AuthorDto();
        $authorDto2->firstname = 'John';
        $authorDto2->lastname = 'Smith';
        $authorDto2->title = 'Mr';

        $command = new CreateBookCommand(
            'Book with Multiple Authors',
            [$authorDto1, $authorDto2],
            new \DateTime('2023-04-01'),
            '1234567890126',
        );

        $this->authorRepository->expects($this->exactly(2))
            ->method('findOneByName')
            ->willReturnCallback(function ($firstname) {
                return null;
            });

        $this->entityManager->expects($this->exactly(3))
        ->method('persist');

        $book = ($this->handler)($command);
        $this->assertCount(2, $book->getAuthors());
    }
}
