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
     * @Column(type="string", length=2, columnDefinition="ENUM('id', 'en')")
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
     * @Column(type="string", length=8, columnDefinition="ENUM('publish', 'archive', 'draft')")
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
     * @JoinColumn(name="created_by", referencedColumnName="id")
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

}
