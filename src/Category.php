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
     * @OneToMany(targetEntity="Categoryi18n", mappedBy="parent", cascade={"persist"})
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
     * @OneToMany(targetEntity="Category", mappedBy="parent")
     * @var Doctrine\Common\Collections\Collection
     */
    private $subcategories;

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

    public function setTranslations(Doctrine\Common\Collections\Collection $translations) {
        $this->translations = $translations;
    }

    public function setParent(Category $parent) {
        $this->parent = $parent;
    }

    public function setSubcategories(Doctrine\Common\Collections\Collection $subcategories) {
        $this->subcategories = $subcategories;
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
