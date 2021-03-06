<?php

/**
 * @Entity
 */
class Eventi18n {

    /**
     * @Id
     * @GeneratedValue(strategy="UUID")
     * @Column(type="string", length=36, unique=true, nullable=false)
     * @var string
     */
    private $id;

    /**
     * @Column(type="string", length=60, nullable=false)
     * @var string
     */
    private $title;

    /**
     * @Column(type="text")
     * @var string
     */
    private $description;

    /**
     * @Column(type="string", length=2)
     * @var string
     */
    private $language = "id";

    /**
     * @OneToOne(targetEntity="Event", mappedBy="detail", cascade="persist")
     * @var Event
     */
    private $event;

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
     * @ManyToOne(targetEntity="Eventi18n", inversedBy="i18n", cascade={"persist"}, fetch="LAZY")
     * @var Eventi18n
     */
    private $base;

    /**
     * @OneToMany(targetEntity="Eventi18n", mappedBy="base", cascade={"persist"}, orphanRemoval=true)
     * @var \Doctrine\Common\Collections\Collection
     */
    private $i18n;

    /**
     * @ManyToMany(targetEntity="Stat", cascade={"persist", "remove", "detach"})
     * @JoinTable(
     *  name="eventi18n_stat", 
     *  joinColumns={@JoinColumn(name="eventi18n_id", referencedColumnName="id", onDelete="cascade")}, 
     *  inverseJoinColumns={@JoinColumn(name="stat_id", referencedColumnName="id", unique=true, onDelete="cascade")}
     * )
     * @var \Doctrine\Common\Collections\Collection 
     */
    private $stats;

    function __construct() {
        $this->stats = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getLanguage() {
        return $this->language;
    }

    public function getEvent() {
        return $this->event;
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

    public function getBase() {
        return $this->base;
    }

    public function getI18n() {
        return $this->i18n;
    }

    public function setTitle($title) {
        $this->title = $title;

        return $this;
    }

    public function setDescription($description) {
        $this->description = $description;

        return $this;
    }

    public function setLanguage($language) {
        $this->language = $language;

        return $this;
    }

    public function setEvent(Event $event) {
        $this->event = $event;

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

    public function setBase(Eventi18n $base) {
        $this->base = $base;

        return $this;
    }

    public function setI18n(\Doctrine\Common\Collections\Collection $i18n) {
        $this->i18n = $i18n;

        return $this;
    }

    public function addI18n(Eventi18n $i18n) {
        $this->i18n->add($i18n);

        return $this;
    }

}
