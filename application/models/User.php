<?php

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity @Table(name="users")
 **/
class User {

    /**
     * @Id @Column(type="integer")
     **/
    protected $id;
    /**
     * @Column(type="string")
     **/
    protected $name;

    /**
     * @OneToMany(targetEntity="Message", mappedBy="user")
     * @var Message[]
     **/
    protected $messages = null;

    public function __construct() {
        $this->messages = new ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

}