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

    /**
     * @ManyToMany(targetEntity="Message", inversedBy="likedUsers")
     * @JoinTable(name="likes",
     *   joinColumns={@JoinColumn(name="user_id", referencedColumnName="id")},
     *   inverseJoinColumns={@JoinColumn(name="message_id", referencedColumnName="id")}
     * )
     */
    protected $likedMessages = null;

    public function __construct(array $options = null) {
        parent::__construct($options);
        $this->messages = new ArrayCollection();
        $this->likedMessages = new ArrayCollection();
    }

    public function addMessage(Message $message) {
        $this->messages[] = $message;
    }

    public function addLikedMessage(Message $message) {
        $this->likedMessages[] = $message;
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

    /**
     * @ManyToMany(targetEntity="User", mappedBy="likedMessages")
     * Users[]
     */
    protected $likedUsers = null;

    /**
     * @Column(type="integer")
     **/
    protected $likesCount = 0;


    public function __construct(array $options = null) {
        parent::__construct($options);
        $this->attachments = new ArrayCollection();
        $this->likedUsers = new ArrayCollection();
    }

    public function setUser(User $user) {
        $user->addMessage($this);
        $this->user = $user;
    }

    public function like(User $user) {
        $this->likedUsers[] = $user;
        $this->likesCount = count($this->likedUsers);
        $user->addLikedMessage($this);
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

