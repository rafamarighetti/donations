<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\DBAL\Types\DecimalType;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Float_;
date_default_timezone_set('America/Sao_Paulo');
/**
 *
 * @ORM\Entity(repositoryClass=ProjectRepository::class)
 *
 */
class Project 
{

        /**
     * @var int $id
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=50)
     */
    private $name;

     /**
     * @ORM\Column(type="json")
     */
    private $author = [];

    /**
     * @var string $description
     *
     * @ORM\Column(name="description", type="string", length=5000)
     */
    private $description;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $donationExpectation;

    /**
     * @ORM\Column(type="json")
     */
    private $categories = [];

      /**
     * @ORM\Column(type="json")
     */
    private $donations = [];

    /**
     * @var datetime $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var datetime $updatedAt
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable = true)
     */
    protected $updatedAt;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2,  nullable = true)
     */
    private $total;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }
    

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAuthor(): ?array
    {
        return $this->author;
    }

    public function setAuthor(array $author): self
    {
        $this->author = $author;

        return $this;
    }


    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }


    public function getDonationExpectation(): ?float
    {
        return $this->donationExpectation;
    }

    public function setDonationExpectation(float $donationExpectation): self
    {
        $this->donationExpectation = $donationExpectation;

        return $this;
    }


    public function getTotal(): ?float
    {   
        return $this->total;
    }

    public function setTotal(float $total): self
    {
        $this->total = $total;

        return $this;
    }

    
    public function getDate()
    {   
        return $this->createdAt;
    }

    public function setDate($createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    
    /**
     * @see UserInterface
     */
    public function getCategories(): array
    {
        $categories = $this->categories;
        // guarantee every user at least has GENERAL_PROJECT
        return array_unique($categories);
    }

    public function setCategories(array $categories): self
    {
        $this->categories = $categories;

        return $this;
    }

    public function getDonations(): array
    {
        $donations = $this->donations;

        return array($donations);
    }

    public function setDonations(array $donations): self
    {
        $this->donations[] = $donations;
        
        return $this;
    }
    
    public function onPrePersist()
    {
        $this->createdAt = new \DateTime("now");
    }

    /**
     * Gets triggered every time on update
     *
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updatedAt = new \DateTime("now");
    }


}