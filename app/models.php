<?php
namespace App\Models;

use \Doctrine\Common\Collections\ArrayCollection;
use \App\System\DomainObject;
use \App\System\DeleteMarkable;

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
class Message extends DomainObject implements DeleteMarkable {

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

    /**
     * @Column(type="datetime", nullable=true)
     **/
    protected $lastLikeDate = null;

    /**
     * @Column(type="datetime", nullable=true)
     **/
    protected $isDeleted = null;


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
        $this->lastLikeDate = new \DateTime();
        $user->addLikedMessage($this);
    }


}

class MessageManager extends \App\System\Manager {

    public function __construct() {
        parent::__construct("\\App\\Models\\Message");
    }

    public function getLastMessages($from, $limit) {
        $dql = "
            SELECT
                m, u
            FROM
                \\App\\Models\\Message m
                JOIN m.user u
            WHERE
                m.isDeleted is null
                and m.id>{$from}
            ORDER BY m.date DESC
        ";

        $query = $this->em->createQuery($dql);
        if(!$from) {
            $query->setMaxResults($limit);
        }
        return $query->getResult();
    }

    public function getDeletedMessages(\DateTime $from) {
        $dql = "
            SELECT m
            FROM
                \\App\\Models\\Message m
            WHERE
                m.isDeleted is not null
                and m.isDeleted > :from
        ";

        $query = $this->em->createQuery($dql)->setParameter('from', $from);

        return $query->getResult();
    }

    public function getLikedMessages(\DateTime $from) {
        $dql = "
            SELECT m
            FROM
                \\App\\Models\\Message m
            WHERE
                m.isDeleted is null
                and m.lastLikeDate is not null
                and m.lastLikeDate > :from
        ";

        $query = $this->em->createQuery($dql)->setParameter('from', $from);

        return $query->getResult();
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

