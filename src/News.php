<?php

/**
 * @Entity
 */
class News {

    const STATUS_PUBLISH = 'publish';
    const STATUS_ARCHIVE = 'archive';
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';

    /**
     * @Id
     * @GeneratedValue(strategy="UUID")
     * @Column(type="string", length=36, unique=true, nullable=false)
     * @var string
     */
    private $id;

    /**
     * @Column(type="string", columnDefinition="ENUM('publish', 'archive', 'draft', 'pending')")
     * @var string
     */
    private $status;

    /**
     * @OneToOne(targetEntity="NewsDetail", mappedBy="news")
     * @var NewsDetail
     */
    private $detail;

    public function getId() {
        return $this->id;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getDetail() {
        return $this->detail;
    }

    public function setStatus($status) {
        $this->status = $status;

        return $this;
    }

    public function setDetail(NewsDetail $detail) {
        $this->detail = $detail;

        return $this;
    }

}
