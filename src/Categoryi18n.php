<?php

/**
 * @Entity
 * @author Egiw
 */
class Categoryi18n {

    /**
     * @Id
     * @Column(type="string", length=36)
     * @GeneratedValue(strategy="UUID")
     * @var string
     */
    private $id;

    /**
     * @ManyToOne(targetEntity="Category", inversedBy="translations")
     * @var Category
     */
    private $parent;

    /**
     * @Column(type="string", length=2)
     * @var string
     */
    private $language = 'en';

    /**
     * @Column(type="string", length=64, unique=false, nullable=false)
     * @var string
     */
    private $name;

    /**
     * @Column(type="string", length=255, unique=false, nullable=false)
     * @var string
     */
    private $slug;

    /**
     * @Column(type="text", nullable=true)
     * @var string
     */
    private $description;

    public function getId() {
        return $this->id;
    }

    public function getParent() {
        return $this->parent;
    }

    public function getLanguage() {
        return $this->language;
    }

    public function getName() {
        return $this->name;
    }

    public function getSlug() {
        return $this->slug;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setParent(Category $parent) {
        $this->parent = $parent;

        return $this;
    }

    public function setLanguage($language) {
        $this->language = $language;

        return $this;
    }

    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    public function setSlug($slug) {
        $this->slug = $slug;

        return $this;
    }

    public function setDescription($description) {
        $this->description = $description;

        return $this;
    }

}
