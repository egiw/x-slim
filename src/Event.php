<?php

/**
 * @Entity
 */
class Event {

    /**
     * @Id
     * @GeneratedValue(strategy="UUID")
     * @Column(type="string", length=36, unique=true, nullable=false)
     * @var string
     */
    private $id;

    /**
     * @Column(type="datetime", nullable=false)
     * @var DateTime
     */
    private $start;

    /**
     *
     * @Column(type="datetime", nullable=false)
     * @var DateTime
     */
    private $end;

    /**
     * @OneToMany(targetEntity="Eventi18n", mappedBy="event", cascade={"persist"}, orphanRemoval=true)
     * @var \Doctrine\Common\Collections\Collection
     */
    private $i18n;

    /**
     * @ManyToMany(targetEntity="Article", mappedBy="relatedEvents")
     * @var \Doctrine\Common\Collections\Collection
     */
    private $relatedArticles;

    function __construct() {
        $this->i18n = new \Doctrine\Common\Collections\ArrayCollection();
        $this->relatedArticles = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getStart() {
        return $this->start;
    }

    public function getEnd() {
        return $this->end;
    }

    public function getI18n() {
        return $this->i18n;
    }

    public function getRelatedArticles() {
        return $this->relatedArticles;
    }

    public function setStart(DateTime $start) {
        $this->start = $start;

        return $this;
    }

    public function setEnd(DateTime $end) {
        $this->end = $end;

        return $this;
    }

    public function setI18n(\Doctrine\Common\Collections\Collection $i18n) {
        $this->i18n = $i18n;

        return $this;
    }

    public function addi18n(Eventi18n $i18n) {
        $this->i18n->add($i18n);

        return $this;
    }

    public function setRelatedArticles(\Doctrine\Common\Collections\Collection $relatedArticles) {
        $this->relatedArticles = $relatedArticles;

        return $this;
    }

    public function addRelatedArticle(Article $article) {
        $this->relatedArticles->add($article);

        return $this;
    }

}
