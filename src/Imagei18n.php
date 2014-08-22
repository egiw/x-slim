<?php

/**
 * @Entity
 */
class Imagei18n {

    /**
     * @Id
     * @GeneratedValue(strategy="UUID")
     * @Column(type="string", length=36)
     * @var string
     */
    private $id;

    /**
     * @ManyToOne(targetEntity="Image", inversedBy="i18n")
     * @var Image
     */
    private $image;

    /**
     * @Column(type="string", length=255)
     * @var string 
     */
    private $title;

    /**
     * @Column(type="text")
     * @var string
     */
    private $caption;

    /**
     * @ManyToOne(targetEntity="Articlei18n", inversedBy="images")
     * @var Articlei18n
     */
    private $articlei18n;

    public function getId() {
        return $this->id;
    }

    /**
     * 
     * @return Image
     */
    public function getImage() {
        return $this->image;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getCaption() {
        return $this->caption;
    }

    public function getLanguage() {
        return $this->language;
    }

    public function setImage(Image $image) {
        $this->image = $image;

        return $this;
    }

    public function setTitle($title) {
        $this->title = $title;

        return $this;
    }

    public function setCaption($caption) {
        $this->caption = $caption;

        return $this;
    }

    public function setLanguage($language) {
        $this->language = $language;

        return $this;
    }

    public function getArticlei18n() {
        return $this->articlei18n;
    }

    public function setArticlei18n(Articlei18n $articlei18n) {
        $this->articlei18n = $articlei18n;

        return $this;
    }

}
