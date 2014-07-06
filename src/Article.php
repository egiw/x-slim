<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * @Entity
 * Article
 */
class Article {

    /**
     * @Id
     * @GeneratedValue(strategy="UUID")
     * @Column(type="string", length=36)
     * @var string
     */
    private $id;

    /**
     * @Column(type="string", columnDefinition="ENUM('publish', 'archive', 'draft')")
     * @var string
     */
    private $status;

    /**
     * @OneToMany(
     *      targetEntity="Articlei18n", 
     *      mappedBy="article", 
     *      cascade={"persist", "remove", "detach"}
     * )
     * @var \Doctrine\Common\Collections\Collection
     */
    private $i18n;

    /**
     * Constructor
     */
    public function __construct() {
        $this->i18n = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Article
     */
    public function setStatus($status) {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Get id
     *
     * @return string 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Add i18n
     *
     * @param \Articlei18n $i18n
     * @return Article
     */
    public function addI18n(\Articlei18n $i18n) {
        $this->i18n[] = $i18n;

        return $this;
    }

    /**
     * Remove i18n
     *
     * @param \Articlei18n $i18n
     */
    public function removeI18n(\Articlei18n $i18n) {
        $this->i18n->removeElement($i18n);
    }

    /**
     * Get i18n
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getI18n() {
        return $this->i18n;
    }

}
