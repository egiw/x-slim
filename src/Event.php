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
     * @OneToOne(targetEntity="Eventi18n", inversedBy="event", cascade={"persist", "remove"})
     * @var Eventi18n
     */
    private $detail;

    /**
     * @ManyToMany(targetEntity="Article", inversedBy="relatedEvents")
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

    public function getRelatedArticles() {
        return $this->relatedArticles;
    }

    public function getDetail() {
        return $this->detail;
    }

    public function setStart(DateTime $start) {
        $this->start = $start;

        return $this;
    }

    public function setEnd(DateTime $end) {
        $this->end = $end;

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

    public function setDetail(Eventi18n $detail) {
        $this->detail = $detail;

        return $this;
    }

}
