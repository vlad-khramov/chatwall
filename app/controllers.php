<?php
namespace App\Controllers;
use \App\System\Locator;
use \App\System\Controller;
use \App\Models\User;
use \App\Models\Message;

class Home extends Controller {

    protected $em;
    public function __construct($request) {
        parent::__construct($request);
        $this->em = Locator::getEm();
    }

    protected function getUser() {
        if(isset($this->request['COOKIE']['userId'])) {
            $userId = $this->request['COOKIE']['userId'];
            $user = $this->em->find("\\App\\Models\\User", $userId);

        } else {
            $userId = rand(1, PHP_INT_MAX);
            $user = null;
        }

        if(empty($user)) {
            $user = new User(array(
                'id' => $userId,
                'name' => 'Anonymous ' . $userId
            ));
        }

        return $user;
    }

    ///Actions///

    public function home() {

        $user = $this->getUser();

        return array(
            'cookie' => array('userId'=>$user->id),
            'text' => Locator::getTS()->render('base.html', array(
                'user' => $user
            ))
        );
    }

    public function userSave() {
        $user = $this->getUser();
        if(!empty($this->request['POST']['value'])) {
            $user->name = $this->request['POST']['value'];
            $this->em->persist($user);
            $this->em->flush();
        } else {
            return "Name can't be empty";
        }

    }

    public function messagesDelete() {
        $user = $this->getUser();
        $id = $this->param('id', 0);

        if(!$id) return '';

        $message = $this->em->find("\\App\\Models\\Message", $id);
        if($message && $message->user == $user) {
            $this->em->remove($message);
            $this->em->flush();
        }
    }

    public function messagesAdd() {
        $user = $this->getUser();

        if(empty($this->request['FILES']) && empty($this->request['POST']['videos'])
            && empty($this->request['POST']['links']) && empty($this->request['POST']['message'])) {
            return array(
                'code' => 400,
                'text' => 'Empty request'
            );
        }

        $message = new Message(array(
            'text' => $this->request['POST']['message'],
            'date' => new \DateTime(),
            'user' => $user
        ));

        $this->em->persist($user);
        $this->em->persist($message);
        $this->em->flush();

    }

    public function messagesLike() {
        $user = $this->getUser();
        $id = $this->param('id', 0);
        if(!$id) return '';

        $message = $this->em->find("\\App\\Models\\Message", $id);

        if(!$message || $message->likedUsers->contains($user)) {
            return '';
        }



        $this->em->persist($user);
        $message->like($user);
        $this->em->persist($message);
        $this->em->flush();

        return json_encode(array('likes_count'=> $message->likesCount));
    }

    public function messagesGetLast() {
        $user = $this->getUser();
        $from = (int)$this->param('from', 0);


        $dql = "SELECT m, u FROM \\App\\Models\\Message m JOIN m.user u WHERE m.id>{$from} ORDER BY m.date DESC";

        $query = $this->em->createQuery($dql);
        if(!$from) {
            $query->setMaxResults(5);
        }
        $messages = $query->getResult();

        $resultJSON = array();
        foreach(array_reverse($messages) as $message) {
            $resultJSON[] = array(
                'id' => $message->id,
                'text' => $message->text,
                'date' => $message->date->format('H:i:s'),
                'own' => $message->user == $user,
                'username' => $message->user->name,
                'likes_count' => $message->likesCount,
                'liked' => $message->likedUsers->contains($user)
            );
        }

        return array(
            'content_type' => 'application/json',
            'text' => json_encode($resultJSON)
        );
    }
}