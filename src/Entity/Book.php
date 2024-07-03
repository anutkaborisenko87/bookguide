<?php

namespace App\Entity;

use App\Repository\BookRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validation;

/**
 * @ORM\Entity(repositoryClass=BookRepository::class)
 */
class Book
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Назва є обов'язковою")
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\ManyToMany(targetEntity=Author::class, inversedBy="books")
     * @Assert\NotBlank(message="Повинен бути принаймні один автор")
     */
    private $authors;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank(message="Дата опублікування є обов'язковою")
     */
    private $published_at;

    public function __construct()
    {
        $this->authors = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection<int, Author>
     */
    public function getAuthors(): Collection
    {
        return $this->authors;
    }

    public function addAuthor(Author $author): self
    {
        if (!$this->authors->contains($author)) {
            $this->authors[] = $author;
        }

        return $this;
    }

    public function removeAuthor(Author $author): self
    {
        $this->authors->removeElement($author);

        return $this;
    }

    public function getPublishedAt(): ?DateTime
    {
        return $this->published_at;
    }

    public function setPublishedAt(?DateTime $published_at): self
    {
        $this->published_at = $published_at;

        return $this;
    }
    /**
     * @Assert\Callback
     */
    public function validateImage(ExecutionContextInterface $context)
    {
        if (!$this->image instanceof UploadedFile && !is_string($this->image) && null !== $this->image) {
            $context->buildViolation('Invalid image.')
                ->atPath('image')
                ->addViolation();
        } elseif (!is_null($this->image) && $this->image instanceof UploadedFile) {
            $validator = Validation::createValidator();
            $violations = $validator->validate($this->image, [
                new Assert\File([
                    'maxSize' => '2M',
                    'mimeTypes' => ['image/jpeg', 'image/png'],
                    'mimeTypesMessage' => 'Будь ласка завантажте зображення у форматі jpg або png',
                ]),
            ]);

            if ($violations->count() > 0) {
                $violation = $violations[0];
                $context->buildViolation($violation->getMessage())
                    ->atPath('image')
                    ->addViolation();
            }
        }
    }
}
