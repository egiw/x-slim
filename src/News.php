<?php

/**
 * @Entity
 */
class News {

    /**
     * @Id
     * @GeneratedValue(strategy="UUID")
     * @Column(type="string", length=36, unique=true, nullable=false)
     * @var string
     */
    private $id;

    /**
     * @OneToOne(targetEntity="Newsi18n", inversedBy="news", cascade={"persist", "remove"})
     * @var Newsi18n
     */
    private $detail;

    /**
     * @ManyToMany(targetEntity="Article", inversedBy="relatedNews")
     * @var \Doctrine\Common\Collections\Collection
     */
    private $relatedArticles;

    function __construct() {
        $this->relatedArticles = new Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getDetail() {
        return $this->detail;
    }

    public function getRelatedArticles() {
        return $this->relatedArticles;
    }

    public function setDetail(Newsi18n $detail) {
        $this->detail = $detail;

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
