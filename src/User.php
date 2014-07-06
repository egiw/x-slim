<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * @Entity
 * User
 */
class User {

    /**
     * @Id
     * @GeneratedValue(strategy="UUID")
     * @Column(type="string", length=36)
     * @var string
     */
    private $id;

    /**
     * @Column(type="string", length=32, unique=true, nullable=false)
     * @var string
     */
    private $username;

    /**
     * @Column(type="string", length=60, nullable=false)
     * @var string
     */
    private $password;

    /**
     * @Column(type="string", length=60, nullable=true)
     * @var string
     */
    private $fullname;

    /**
     * @Column(type="string", length=100, unique=true, nullable=true)
     * @var string
     */
    private $email;

    /**
     * @Column(type="blob", nullable=true)
     * @var string
     */
    private $avatar;

    /**
     * @Column(type="json_array", nullable=true)
     * @var array
     */
    private $settings;

    /**
     * @OneToMany(targetEntity="Articlei18n", mappedBy="author", cascade={"persist"})
     * @var \Doctrine\Common\Collections\Collection
     */
    private $articles;

    /**
     * Constructor
     */
    public function __construct() {
        $this->articles = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username) {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password) {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * Set fullname
     *
     * @param string $fullname
     * @return User
     */
    public function setFullname($fullname) {
        $this->fullname = $fullname;

        return $this;
    }

    /**
     * Get fullname
     *
     * @return string 
     */
    public function getFullname() {
        return $this->fullname;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email) {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Set avatar
     *
     * @param string $avatar
     * @return User
     */
    public function setAvatar($avatar) {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get avatar
     *
     * @return string 
     */
    public function getAvatar() {
        return $this->avatar;
    }

    /**
     * Set settings
     *
     * @param array $settings
     * @return User
     */
    public function setSettings($settings) {
        $this->settings = $settings;

        return $this;
    }

    /**
     * Get settings
     *
     * @return array 
     */
    public function getSettings() {
        return $this->settings;
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
     * Add articles
     *
     * @param \Articlei18n $articles
     * @return User
     */
    public function addArticle(\Articlei18n $articles) {
        $this->articles[] = $articles;

        return $this;
    }

    /**
     * Remove articles
     *
     * @param \Articlei18n $articles
     */
    public function removeArticle(\Articlei18n $articles) {
        $this->articles->removeElement($articles);
    }

    /**
     * Get articles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getArticles() {
        return $this->articles;
    }

}
