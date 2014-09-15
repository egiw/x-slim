<?php

/**
 * @Entity
 * Article
 */
class Article {

	/**
	 * @Id
	 * @GeneratedValue(strategy="UUID")
	 * @Column(type="string", length=36)
	 * @var string
	 */
	private $id;

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
	 * @Column(type="string", length=255, unique=false, nullable=false)
	 * @var string
	 */
	private $featuredImage;

	/**
	 * @OneToOne(targetEntity="Articlei18n", inversedBy="article", cascade={"persist"})
	 * @var Articlei18n
	 */
	private $detail;

	/**
	 * @ManyToMany(targetEntity="Article")
	 * @var Doctrine\Common\Collections\Collection 
	 */
	private $related;

	/**
	 * @ManyToMany(targetEntity="Event", mappedBy="relatedArticles")
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $relatedEvents;

	/**
	 * @ManyToMany(targetEntity="News", mappedBy="relatedArticles")
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $relatedNews;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->categories = new \Doctrine\Common\Collections\ArrayCollection();
		$this->regions = new \Doctrine\Common\Collections\ArrayCollection();
		$this->related = new \Doctrine\Common\Collections\ArrayCollection();
		$this->relatedNews = new Doctrine\Common\Collections\ArrayCollection();
		$this->relatedEvents = new Doctrine\Common\Collections\ArrayCollection();
	}

	public function getId() {
		return $this->id;
	}

	public function getCategories() {
		return $this->categories;
	}

	public function getRegions() {
		return $this->regions;
	}

	public function getImages() {
		return $this->images;
	}

	public function getFeaturedImage() {
		return $this->featuredImage;
	}

	public function getDetail() {
		return $this->detail;
	}

	public function getRelated() {
		return $this->related;
	}

	public function getRelatedEvents() {
		return $this->relatedEvents;
	}

	public function getRelatedNews() {
		return $this->relatedNews;
	}

	public function setCategories(Doctrine\Common\Collections\Collection $categories) {
		$this->categories = $categories;

		return $this;
	}

	public function setRegions(Doctrine\Common\Collections\Collection $regions) {
		$this->regions = $regions;

		return $this;
	}

	public function setImages(\Doctrine\Common\Collections\Collection $images) {
		$this->images = $images;

		return $this;
	}

	public function setFeaturedImage($featuredImage) {
		$this->featuredImage = $featuredImage;

		return $this;
	}

	public function setDetail(Articlei18n $detail) {
		$this->detail = $detail;

		return $this;
	}

	public function setRelated(Doctrine\Common\Collections\Collection $related) {
		$this->related = $related;

		return $this;
	}

	public function setRelatedEvents(\Doctrine\Common\Collections\Collection $relatedEvents) {
		$this->relatedEvents = $relatedEvents;

		return $this;
	}

	public function setRelatedNews(\Doctrine\Common\Collections\Collection $relatedNews) {
		$this->relatedNews = $relatedNews;

		return $this;
	}

	public function addCategory(Category $category) {
		$this->categories->add($category);

		return $this;
	}

	public function addRegion(Region $region) {
		$this->regions->add($region);

		return $this;
	}

	public function addRelated(Article $article) {
		$this->related->add($article);

		return $this;
	}

	public function addRelatedNews(News $news) {
		$this->relatedNews->add($news);

		return $this;
	}

	public function addRelatedEvent(Event $event) {
		$this->relatedEvents->add($event);

		return $this;
	}

	/**
	 * Check if article belongs to user
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
