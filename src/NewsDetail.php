<?php

/**
 * @Entity
 */
class NewsDetail {

    /**
     * @Id
     * @GeneratedValue(strategy="UUID")
     * @Column(type="string", length=36, unique=true, nullable=false)
     * @var string
     */
    private $id;

    /**
     * @Column(type="string", length=60)
     * @var string
     */
    private $title;

    /**
     * @Column(type="string", length=2)
     * @var string
     */
    private $language = "en";

    /**
     * @OneToOne(targetEntity="News", inversedBy="detail")
     * @var News
     */
    private $news;

    /**
     * @ManyToOne(targetEntity="NewsDetail", cascade={"all"}, fetch="LAZY", inversedBy="translations")
     * @var NewsDetail
     */
    private $base;

    /**
     * @OneToMany(targetEntity="NewsDetail", mappedBy="base", cascade={"persist"}, orphanRemoval=true)
     * @var \Doctrine\Common\Collections\Collection
     */
    private $translations;

    public function getId() {
        return $this->id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getLanguage() {
        return $this->language;
    }

    public function getNews() {
        return $this->news;
    }

    public function getBase() {
        return $this->base;
    }

    public function getTranslations() {
        return $this->translations;
    }

    public function setTitle($title) {
        $this->title = $title;

        return $this;
    }

    public function setLanguage($language) {
        $this->language = $language;

        return $this;
    }

    public function setNews(News $news) {
        $this->news = $news;

        return $this;
    }

    public function setBase(NewsDetail $base) {
        $this->base = $base;

        return $this;
    }

    public function setTranslations(\Doctrine\Common\Collections\Collection $translations) {
        $this->translations = $translations;

        return $this;
    }

}
