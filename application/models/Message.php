<?php

/**
 * @Entity @Table(name="users")
 **/
class Message {

    /**
     * @Id @Column(type="integer") @GeneratedValue
     **/
    protected $id;
    /**
     * @Column(type="string")
     **/
    protected $text;
    /**
     * @Column(type="datetime")
     **/
    protected $date;
    /**
     * @ManyToOne(targetEntity="User", inversedBy="messages")
     **/
    protected $user;

    public function getId() {
        return $this->id;
    }

    public function getText() {
        return $this->text;
    }

    public function setText($text) {
        $this->text = $text;
    }

    public function getDate() {
        return $this->date;
    }

    public function setDate($date) {
        $this->date = $date;
    }
}