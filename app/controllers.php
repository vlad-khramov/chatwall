<?php
namespace App\Controllers;
use \App\System\Locator;
use \App\System\Controller;
use \App\Models\User;

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

    public function user_save() {
        $user = $this->getUser();
        if(!empty($this->request['POST']['value'])) {
            $user->name = $this->request['POST']['value'];
            $this->em->persist($user);
            $this->em->flush();
        } else {
            return "Name can't be empty";
        }

    }
}