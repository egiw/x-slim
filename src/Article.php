<?php

/**
 * @Entity
 * Article
 */
class Article {

    const STATUS_PUBLISH = 'publish';
    const STATUS_ARCHIVE = 'archive';
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
     * @Column(type="string", columnDefinition="ENUM('publish', 'archive', 'draft', 'pending')")
     * @var string
     */
    private $status;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="created_by", referencedColumnName="id", onDelete="SET NULL")
     * @var \User
     */
    private $author;

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
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="updated_by", referencedColumnName="id")
     * @var \User
     */
    private $updatedBy;

    /**
     * @OneToMany(
     *      targetEntity="Articlei18n", 
     *      mappedBy="article", 
     *      cascade={"persist", "remove", "detach"},
     *      orphanRemoval=true
     * )
     * @var \Doctrine\Common\Collections\Collection
     */
    private $i18n;

    /**
     * @ManyToMany(targetEntity="Category", inversedBy="articles")
     * @var Doctrine\Common\Collections\Collection
     */
    private $categories;

    /**
     * @ManyToMany(targetEntity="Region", inversedBy="articles")
     * @var Doctrine\Common\Collections\Collection
     */
    private $regions;

    /**
     * @OneToMany(targetEntity="Image", mappedBy="article", cascade={"remove"})
     * @var \Doctrine\Common\Collections\Collection
     */
    private $images;

    /**
     * @ManyToMany(targetEntity="Article")
     * @var Doctrine\Common\Collections\Collection 
     */
    private $related;

    /**
     * @Column(type="string", length=255, unique=false, nullable=false)
     * @var string
     */
    private $featuredImage;

    /**
     * @ManyToMany(targetEntity="Event", mappedBy="relatedArticles")
     * @var \Doctrine\Common\Collections\Collection
     */
    private $relatedEvents;

    /**
     * Constructor
     */
    public function __construct() {
        $this->i18n = new \Doctrine\Common\Collections\ArrayCollection();
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
        $this->regions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->related = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set author
     *
     * @param \User $author
     * @return Article
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

    /**
     * Check if article is publish
     * 
     * @return bool
     */
    public function isPublish() {
        return $this->getStatus() === self::STATUS_PUBLISH;
    }

    /**
     * Check if article is draft
     * 
     * @return bool
     */
    public function isDraft() {
        return $this->getStatus() === self::STATUS_DRAFT;
    }

    /**
     * Check ifarticle is archive
     * 
     * @return bool
     */
    public function isArchive() {
        return $this->getStatus() === self::STATUS_ARCHIVE;
    }

    /**
     * Check if article is pending
     * @return bool
     */
    public function isPending() {
        return $this->getStatus() == self::STATUS_PENDING;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Article
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
     * @return Article
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
     * Set updatedBy
     *
     * @param \User $updatedBy
     * @return Article
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
     * 
     * @return Doctrine\Common\Collections\Collection
     */
    public function getCategories() {
        return $this->categories;
    }

    /**
     * 
     * @param Doctrine\Common\Collections\Collection $categories
     * @return \Article
     */
    public function setCategories(Doctrine\Common\Collections\Collection $categories) {
        $this->categories = $categories;

        return $this;
    }

    /**
     * 
     * @param Category $category
     * @return \Article
     */
    public function addCategory(Category $category) {
        $this->categories[] = $category;

        return $this;
    }

    public function getRegions() {
        return $this->regions;
    }

    public function setRegions(Doctrine\Common\Collections\Collection $regions) {
        $this->regions = $regions;

        return $this;
    }

    public function getImages() {
        return $this->images;
    }

    public function setImages(\Doctrine\Common\Collections\Collection $images) {
        $this->images = $images;

        return $this;
    }

    public function getRelated() {
        return $this->related;
    }

    public function setRelated(Doctrine\Common\Collections\Collection $related) {
        $this->related = $related;

        return $this;
    }

    public function addRelated(Article $article) {
        $this->related->add($article);

        return $this;
    }

    /**
     * 
     * @param Region $region
     * @return \Article
     */
    public function addRegion(Region $region) {
        $this->regions[] = $region;

        return $this;
    }

    public function getFeaturedImage() {
        return $this->featuredImage;
    }

    public function setFeaturedImage($featuredImage) {
        $this->featuredImage = $featuredImage;

        return $this;
    }

    /**
     * Check if article belongs to user
     * 
     * @param User $user
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
