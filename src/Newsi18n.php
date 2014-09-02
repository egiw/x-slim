<?php

/**
 * @Entity
 */
class Newsi18n {

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
     * @Column(type="text")
     * @var string
     */
    private $content;

    /**
     * @Column(type="string", length=2)
     * @var string
     */
    private $language = "id";

    /**
     * @OneToOne(targetEntity="News", mappedBy="detail")
     * @var News
     */
    private $news;

    /**
     * @ManyToOne(targetEntity="Newsi18n", cascade={"all"}, fetch="LAZY", inversedBy="translations")
     * @var NewsDetail
     */
    private $base;

    /**
     * @OneToMany(targetEntity="Newsi18n", mappedBy="base", cascade={"persist"}, orphanRemoval=true)
     * @var \Doctrine\Common\Collections\Collection
     */
    private $i18n;

    /**
     * @Column(type="string", columnDefinition="ENUM('publish', 'archive', 'draft', 'pending')")
     * @var string
     */
    private $status;

    /**
     * @ManyToOne(targetEntity="User")
     * @var User
     */
    private $createdBy;

    /**
     * @Column(type="datetime")
     * @var DateTime
     */
    private $createdAt;

    /**
     * @ManyToOne(targetEntity="User")
     * @var User
     */
    private $updatedBy;

    /**
     * @Column(type="datetime", nullable=true)
     * @var DateTime
     */
    private $updatedAt;

    /**
     * @ManyToMany(targetEntity="Stat", cascade={"persist", "remove", "detach"})
     * @JoinTable(
     *  name="newsi18n_stat", 
     *  joinColumns={@JoinColumn(name="newsi18n_id", referencedColumnName="id", onDelete="cascade")}, 
     *  inverseJoinColumns={@JoinColumn(name="stat_id", referencedColumnName="id", unique=true, onDelete="cascade")}
     * )
     * @var \Doctrine\Common\Collections\Collection 
     */
    private $stats;

    function __construct() {
        $this->stats = new Doctrine\Common\Collections\ArrayCollection();
        $this->i18n = new Doctrine\Common\Collections\ArrayCollection();
    }

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

    public function getContent() {
        return $this->content;
    }

    public function getI18n() {
        return $this->i18n;
    }

    public function getCreatedBy() {
        return $this->createdBy;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function getUpdatedBy() {
        return $this->updatedBy;
    }

    public function getUpdatedAt() {
        return $this->updatedAt;
    }

    public function getStats() {
        return $this->stats;
    }

    public function getStatus() {
        return $this->status;
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

    public function setBase(Newsi18n $base) {
        $this->base = $base;

        return $this;
    }

    public function setTranslations(\Doctrine\Common\Collections\Collection $translations) {
        $this->translations = $translations;

        return $this;
    }

    public function setContent($content) {
        $this->content = $content;

        return $this;
    }

    public function setI18n(\Doctrine\Common\Collections\Collection $i18n) {
        $this->i18n = $i18n;

        return $this;
    }

    public function setCreatedBy(User $createdBy) {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function setCreatedAt(DateTime $createdAt) {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setUpdatedBy(User $updatedBy) {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function setUpdatedAt(DateTime $updatedAt) {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function setStats(\Doctrine\Common\Collections\Collection $stats) {
        $this->stats = $stats;

        return $this;
    }

    public function setStatus($status) {
        $this->status = $status;

        return $this;
    }

}
