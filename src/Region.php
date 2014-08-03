<?php

/**
 * @Entity
 */
class Region {

    /**
     * @Id
     * @Column(type="integer")
     * @var int
     */
    private $id;

    /**
     * @Column(type="float")
     * @var float
     */
    private $pointX;

    /**
     * @Column(type="float")
     * @var float
     */
    private $pointY;

    /**
     * @Column(type="string")
     * @var string
     */
    private $name;

    /**
     * @ManyToOne(targetEntity="Region", inversedBy="children")
     * @var Region
     */
    private $parent;

    /**
     * @OneToMany(targetEntity="Region", mappedBy="parent")
     * @var Doctrine\Common\Collections\Collection
     */
    private $children;

    /**
     * @ManyToMany(targetEntity="Article", mappedBy="regions")
     * @var Doctrine\Common\Collections\Collection
     */
    private $articles;

    function __construct() {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->articles = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getPointX() {
        return $this->pointX;
    }

    public function getPointY() {
        return $this->pointY;
    }

    public function getName() {
        return $this->name;
    }

    public function getParent() {
        return $this->parent;
    }

    public function getChildren() {
        return $this->children;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setPointX($pointX) {
        $this->pointX = $pointX;
    }

    public function setPointY($pointY) {
        $this->pointY = $pointY;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setParent(Region $parent) {
        $this->parent = $parent;
    }

    public function setChildren(Doctrine\Common\Collections\Collection $children) {
        $this->children = $children;
    }

}
