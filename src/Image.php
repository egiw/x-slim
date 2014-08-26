<?php

/**
 * @Entity
 */
class Image {

    /**
     * @Id
     * @GeneratedValue(strategy="UUID")
     * @Column(type="string", length=36)
     * @var string
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Article", inversedBy="images")
     * @var Article
     */
    protected $article;

    /**
     * @Column(type="string", length=255, unique=true)
     * @var string
     */
    protected $filename;

    /**
     * @OneToMany(targetEntity="Imagei18n", mappedBy="image", cascade={"persist", "remove"})
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $i18n;

    function __construct() {
        $this->i18n = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getArticle() {
        return $this->article;
    }

    public function getFilename() {
        return $this->filename;
    }

    public function getI18n() {
        return $this->i18n;
    }

    public function setArticle(Article $article) {
        $this->article = $article;

        return $this;
    }

    public function setFilename($filename) {
        $this->filename = $filename;

        return $this;
    }

    public function setI18n(\Doctrine\Common\Collections\Collection $i18n) {
        $this->i18n = $i18n;

        return $this;
    }

    public function addi18n(Imagei18n $i18n) {
        $this->i18n->add($i18n);

        return $this;
    }

}
