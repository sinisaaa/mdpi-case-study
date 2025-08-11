<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BookRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Book
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups(['book_list', 'search', 'book_detail'])]
    private ?Uuid $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['book_list', 'search', 'book_detail'])]
    private string $title;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Groups(['search', 'book_detail'])]
    private ?string $author = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['book_list', 'search', 'book_detail'])]
    private ?\DateTimeInterface $publishedAt = null;

    #[ORM\Column(type: 'string', length: 20, unique: true, nullable: true)]
    #[Assert\Length(max: 20)]
    #[Groups(['book_list', 'search', 'book_detail'])]
    private ?string $isbn = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['book_detail'])]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['book_detail'])]
    private \DateTimeInterface $updatedAt;

    /**
     * @var Collection<int, Author>
     */
    #[ORM\ManyToMany(targetEntity: Author::class, mappedBy: 'books')]
    #[Groups(['book_detail', 'search'])]
    private Collection $authors;

    /**
     * @param string $title
     * @param \DateTimeInterface|null $publishedAt
     * @param string|null $isbn
     */
    private function __construct(string $title, ?\DateTimeInterface $publishedAt = null, ?string $isbn = null)
    {
        $this->title = $title;
        $this->publishedAt = $publishedAt;
        $this->isbn = $isbn;
        $this->authors = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeInterface $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getIsbn(): ?string
    {
        return $this->isbn;
    }

    public function setIsbn(?string $isbn): self
    {
        $this->isbn = $isbn;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @return Collection<int, Author>
     */
    public function getAuthors(): Collection
    {
        return $this->authors;
    }

    public function addAuthor(Author $author): static
    {
        if (!$this->authors->contains($author)) {
            $this->authors->add($author);
            $author->addBook($this);
        }

        return $this;
    }

    public function removeAuthor(Author $author): static
    {
        if ($this->authors->removeElement($author)) {
            $author->removeBook($this);
        }

        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $now = new \DateTime();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * @param string $title
     * @param \DateTimeInterface|null $publishedAt
     * @param string|null $isbn
     * @return self
     */
    public static function create(string $title, ?\DateTimeInterface $publishedAt = null, ?string $isbn = null): self
    {
        return new self($title, $publishedAt, $isbn);
    }

}
