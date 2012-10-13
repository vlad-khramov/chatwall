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

    public function addMessage(Message $message) {
        $this->messages[] = $message;
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

    /**
     * @OneToMany(targetEntity="Attachment", mappedBy="message")
     * @var Attachment[]
     **/
    protected $attachments = null;


    public function __construct(array $options = null) {
        parent::__construct($options);
        $this->attachments = new ArrayCollection();
    }

    public function setUser(User $user) {
        var_dump(1);
        $user->addMessage($this);
        $this->user = $user;
    }

}


/**
 * @Entity @Table(name="attachments")
 **/
class Attachment extends DomainObject {

    /**
     * @Id @Column(type="integer") @GeneratedValue
     **/
    protected $id;
    /**
     * @Column(type="string")
     **/
    protected $type;
    /**
     * @Column(type="datetime")
     **/
    protected $data;
    /**
     * @ManyToOne(targetEntity="Message", inversedBy="attachments")
     **/
    protected $message;

}