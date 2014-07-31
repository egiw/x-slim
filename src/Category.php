<?php

/**
 * @Entity
 * @author Egiw
 */
class Category {

    /**
     * @Id
     * @Column(type="string", length=36)
     * @GeneratedValue(strategy="UUID")
     * @var string
     */
    private $id;

    /**
     * @Column(type="string", length=255)
     * @var string
     */
    private $image;

    /**
     * @OneToMany(targetEntity="Categoryi18n", mappedBy="parent", cascade={"persist", "remove", "detach"})
     * @var Doctrine\Common\Collections\Collection
     */
    private $translations;

    /**
     * @ManyToOne(targetEntity="Category", inversedBy="subcategories")
     * @JoinColumn(name="parent_id", referencedColumnName="id")
     * @var Category
     */
    private $parent;

    /**
     * @OneToMany(targetEntity="Category", mappedBy="parent", cascade={"remove"})
     * @var Doctrine\Common\Collections\Collection
     */
    private $subcategories;

    /**
     * @ManyToMany(targetEntity="Article", mappedBy="categories")
     * @var Doctrine\Common\Collections\Collection
     */
    private $articles;

    public function __construct() {
        $this->translations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getTranslations() {
        return $this->translations;
    }

    public function getParent() {
        return $this->parent;
    }

    public function getSubcategories() {
        return $this->subcategories;
    }

    public function getImage() {
        return $this->image;
    }

    public function setTranslations(Doctrine\Common\Collections\Collection $translations) {
        $this->translations = $translations;

        return $this;
    }

    public function setParent(Category $parent) {
        $this->parent = $parent;

        return $this;
    }

    public function setSubcategories(Doctrine\Common\Collections\Collection $subcategories) {
        $this->subcategories = $subcategories;

        return $this;
    }

    public function setImage($image) {
        $this->image = $image;

        return $this;
    }

    /**
     * Add i18n
     *
     * @param Categoryi18n $i18n
     * @return Category
     */
    public function addTranslation(Categoryi18n $i18n) {
        $this->translations[] = $i18n;

        return $this;
    }

}
