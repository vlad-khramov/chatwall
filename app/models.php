<?php
namespace App\Models;

use \Doctrine\Common\Collections\ArrayCollection;
use \App\System\DomainObject;

/**
 * @Entity @Table(name="users")
 **/
class User extends DomainObject {

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

    public function __construct(array $options = null) {
        parent::__construct($options);
        $this->messages = new ArrayCollection();
    }


}



/**
 * @Entity @Table(name="messages")
 **/
class Message extends DomainObject {

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

}