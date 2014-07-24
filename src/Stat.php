<?php

/**
 * @Entity
 */
class Stat {

    /**
     * @Id
     * @GeneratedValue(strategy="UUID")
     * @Column(type="string", length=36)
     * @var string
     */
    private $id;

    /**
     * @Column(type="string", length=15)
     * @var string
     */
    private $ip;

    /**
     * @Column(type="string", length=255)
     * @var string
     */
    private $path;

    /**
     * @Column(type="datetime")
     * @var DateTime
     */
    private $visitDate;

    /**
     * @Column(type="string", length=32)
     * @var string
     */
    private $browser;

    /**
     *
     * @Column(type="string", length=10)
     * @var string
     */
    private $version;

    /**
     * @Column(type="string", length=32)
     * @var string
     */
    private $platform;

    /**
     * @Column(type="boolean")
     * @var bool
     */
    private $isMobile;

    /**
     *
     * @Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $referrer;

    public function __construct() {
        $this->articles = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getRid() {
        return $this->rid;
    }

    public function getIp() {
        return $this->ip;
    }

    public function getPath() {
        return $this->path;
    }

    public function getVisitDate() {
        return $this->visitDate;
    }

    public function getReferrer() {
        return $this->referrer;
    }

    public function getBrowser() {
        return $this->browser;
    }

    public function getVersion() {
        return $this->version;
    }

    public function getPlatform() {
        return $this->platform;
    }

    public function getIsMobile() {
        return $this->isMobile;
    }

    public function setRid($rid) {
        $this->rid = $rid;

        return $this;
    }

    public function setIp($ip) {
        $this->ip = $ip;

        return $this;
    }

    public function setPath($path) {
        $this->path = $path;

        return $this;
    }

    public function setVisitDate(DateTime $visitDate) {
        $this->visitDate = $visitDate;

        return $this;
    }

    public function setReferrer($referrer) {
        $this->referrer = $referrer;

        return $this;
    }

    public function setBrowser($browser) {
        $this->browser = $browser;

        return $this;
    }

    public function setVersion($version) {
        $this->version = $version;

        return $this;
    }

    public function setPlatform($platform) {
        $this->platform = $platform;

        return $this;
    }

    public function setIsMobile($isMobile) {
        $this->isMobile = $isMobile;

        return $this;
    }


}
