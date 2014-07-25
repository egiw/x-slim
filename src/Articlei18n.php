<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * @Entity
 * Articlei18n
 */
class Articlei18n {

    const STATUS_PUBLISH = 'publish';
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';

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
    private $language = "en";

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
     * @Column(type="string", length=8, columnDefinition="ENUM('publish', 'draft', 'pending')")
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
     * @ManyToOne(targetEntity="Article", inversedBy="i18n", cascade={"persist"})
     * @var \Article
     */
    private $article;

    /**
     *
     * @ManyToMany(targetEntity="Stat", cascade={"persist", "remove", "detach"})
     * @JoinTable(
     *  name="Articlei18n_Stat", 
     *  joinColumns={@JoinColumn(name="articlei18n_id", referencedColumnName="id", onDelete="cascade")}, 
     *  inverseJoinColumns={@JoinColumn(name="stat_id", referencedColumnName="id", unique=true, onDelete="cascade")}
     * )
     * @var \Doctrine\Common\Collections\Collection 
     */
    private $stats;

    public function __construct() {
        $this->stats = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add article statistic
     * @param Stat $stat
     * @return \Articlei18n
     */
    public function addStat(Stat $stat) {
        $this->stats[] = $stat;

        return $this;
    }

    /**
     * get article statistics
     * @return Doctrine\Common\Collections\ArrayCollection
     */
    public function getStats() {
        return $this->stats;
    }

    /**
     * Set language
     *
     * @param string $language
     * @return Articlei18n
     */
    public function setLanguage($language) {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return string 
     */
    public function getLanguage() {
        return $this->language;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Articlei18n
     */
    public function setTitle($title) {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Articlei18n
     */
    public function setSlug($slug) {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug() {
        return $this->slug;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return Articlei18n
     */
    public function setContent($content) {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Articlei18n
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Articlei18n
     */
    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Articlei18n
     */
    public function setUpdatedAt($updatedAt) {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt() {
        return $this->updatedAt;
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
     * Set author
     *
     * @param \User $author
     * @return Articlei18n
     */
    public function setAuthor(\User $author = null) {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return \User 
     */
    public function getAuthor() {
        return $this->author;
    }

    /**
     * Set updatedBy
     *
     * @param \User $updatedBy
     * @return Articlei18n
     */
    public function setUpdatedBy(\User $updatedBy = null) {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return \User 
     */
    public function getUpdatedBy() {
        return $this->updatedBy;
    }

    /**
     * Set article
     *
     * @param \Article $article
     * @return Articlei18n
     */
    public function setArticle(\Article $article = null) {
        $this->article = $article;

        return $this;
    }

    /**
     * Get article
     *
     * @return \Article 
     */
    public function getArticle() {
        return $this->article;
    }

    /**
     * Check if article is published
     * @return bool
     */
    public function isPublish() {
        return $this->getStatus() === self::STATUS_PUBLISH;
    }

    /**
     * Check if article is draft
     * @return bool
     */
    public function isDraft() {
        return $this->getStatus() === self::STATUS_DRAFT;
    }

    /**
     * Check if article is pending
     * 
     * @return bool
     */
    public function isPending() {
        return $this->getStatus() === self::STATUS_PENDING;
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
        return !(($user->isAuthor() || $user->isContributor()) && !($this->belongsTo($user) || $this->article->belongsTo($user)));
    }

    public static function slugify($text) {
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

        // trim
        $text = trim($text, '-');

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

}
