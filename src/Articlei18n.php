<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * @Entity
 * Articlei18n
 */
class Articlei18n {

    /**
     * @Id
     * @GeneratedValue(strategy="UUID")
     * @Column(type="string", length=36)
     * @var string
     */
    private $id;

    /**
     * @Column(type="string", length=2)
     * @var string
     */
    private $language = "id";

    /**
     * @Column(type="string", length=60)
     * @var string
     */
    private $title;

    /**
     * @Column(type="string", length=80)
     * @var string
     */
    private $slug;

    /**
     * @Column(type="text")
     * @var string
     */
    private $content;

    /**
     * @Column(type="string", length=8, columnDefinition="ENUM('publish', 'draft', 'pending', 'archive')")
     * @var string
     */
    private $status;

    /**
     * @Column(type="datetime", name="created_at")
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @Column(type="datetime", name="updated_at", nullable=true)
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @ManyToOne(targetEntity="User", inversedBy="articles")
     * @JoinColumn(name="created_by", referencedColumnName="id", onDelete="SET NULL")
     * @var \User
     */
    private $author;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="updated_by", referencedColumnName="id")
     * @var \User
     */
    private $updatedBy;

    /**
     * @ManyToOne(targetEntity="Articlei18n", cascade={"persist"}, fetch="LAZY", inversedBy="translations")
     * @var Articlei18n
     */
    private $base;

    /**
     * @OneToMany(targetEntity="Articlei18n", mappedBy="base", cascade={"persist"}, orphanRemoval=true)
     * @var \Doctrine\Common\Collections\Collection
     */
    private $i18n;

    /**
     * @OneToOne(targetEntity="Article", mappedBy="detail", cascade={"persist"})
     * @var Article 
     */
    private $article;

    /**
     *
     * @ManyToMany(targetEntity="Stat", cascade={"persist", "remove", "detach"})
     * @JoinTable(
     *  name="articlei18n_stat", 
     *  joinColumns={@JoinColumn(name="articlei18n_id", referencedColumnName="id", onDelete="cascade")}, 
     *  inverseJoinColumns={@JoinColumn(name="stat_id", referencedColumnName="id", unique=true, onDelete="cascade")}
     * )
     * @var \Doctrine\Common\Collections\Collection 
     */
    private $stats;

    public function __construct() {
        $this->stats = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getLanguage() {
        return $this->language;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getSlug() {
        return $this->slug;
    }

    public function getContent() {
        return $this->content;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function getUpdatedAt() {
        return $this->updatedAt;
    }

    public function getAuthor() {
        return $this->author;
    }

    public function getUpdatedBy() {
        return $this->updatedBy;
    }

    public function getArticle() {
        return $this->article;
    }

    public function getBase() {
        return $this->base;
    }

    public function getI18n() {
        return $this->i18n;
    }

    public function getStats() {
        return $this->stats;
    }

    public function setLanguage($language) {
        $this->language = $language;

        return $this;
    }

    public function setTitle($title) {
        $this->title = $title;

        return $this;
    }

    public function setSlug($slug) {
        $this->slug = $slug;

        return $this;
    }

    public function setContent($content) {
        $this->content = $content;

        return $this;
    }

    public function setStatus($status) {
        $this->status = $status;

        return $this;
    }

    public function setCreatedAt(\DateTime $createdAt) {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setUpdatedAt(\DateTime $updatedAt) {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function setAuthor(\User $author) {
        $this->author = $author;

        return $this;
    }

    public function setUpdatedBy(\User $updatedBy) {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function setArticle(Article $article) {
        $this->article = $article;

        return $this;
    }

    public function setBase(Articlei18n $base) {
        $this->base = $base;

        return $this;
    }

    public function setI18n(\Doctrine\Common\Collections\Collection $i18n) {
        $this->i18n = $i18n;

        return $this;
    }

    public function setStats(\Doctrine\Common\Collections\Collection $stats) {
        $this->stats = $stats;

        return $this;
    }

    /**
     * Check if article is published
     * @return bool
     */
    public function isPublish() {
        return $this->getStatus() === STATUS_PUBLISH;
    }

    /**
     * Check if article is draft
     * @return bool
     */
    public function isDraft() {
        return $this->getStatus() === STATUS_DRAFT;
    }

    /**
     * Check if article is pending
     * 
     * @return bool
     */
    public function isPending() {
        return $this->getStatus() === STATUS_PENDING;
    }

    public function isArchive() {
        return $this->getStatus() === STATUS_ARCHIVE;
    }

    /**
     * Check if this article is belongs to user
     * 
     * @param User $user
     * @return bool
     */
    public function belongsTo(User $user) {
        return $this->author === $user;
    }

    /**
     * Check if user is permitted to modify this article
     * @param User $user
     * @return bool
     */
    public function isPermitted(User $user) {
        return !(($user->isAuthor() || $user->isContributor()) && !$this->belongsTo($user));
    }

}
