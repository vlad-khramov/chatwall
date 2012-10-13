<?php
namespace App\Controllers;
use \App\System\Locator;
use \App\System\Controller;
use \App\System\Manager;
use \App\Models\User;
use \App\Models\Message;
use \App\Models\Attachment;
use \App\Models\MessageManager;


class Home extends Controller {

    protected $userManager;
    protected $messageManager;
    protected $attachmentManager;


    public function __construct($request) {
        parent::__construct($request);
        $this->userManager = new Manager("\\App\\Models\\User");
        $this->messageManager = new MessageManager();
        $this->attachmentManager = new Manager("\\App\\Models\\Attachment");
    }

    protected function getUser() {
        if(isset($this->request['COOKIE']['userId'])) {
            $userId = $this->request['COOKIE']['userId'];
            $user = $this->userManager->get($userId);
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
        $now = new \DateTime();

        return array(
            'cookie' => array('userId'=>$user->id),
            'text' => Locator::getTS()->render('base.html', array(
                'user' => $user,
                'now' => $now->format('U')
            ))
        );
    }

    public function userSave() {
        $user = $this->getUser();
        if(!empty($this->request['POST']['value'])) {
            $user->name = $this->request['POST']['value'];
            $this->userManager->save($user, true);
        } else {
            return "Name can't be empty";
        }

    }

    public function messagesDelete() {
        $user = $this->getUser();
        $id = $this->param('id', 0);

        if(!$id) return '';

        $message = $this->messageManager->get($id);
        if($message && $message->user == $user) {
            $this->messageManager->delete($message, true);
        }
    }

    public function messagesAdd() {
        $user = $this->getUser();


        if(empty($this->request['FILES']['images']) && empty($this->request['POST']['videos'])
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
        $this->userManager->save($user);
        $this->messageManager->save($message);

        if(!empty($this->request['FILES']['images'])) {
            $images = $this->request['FILES']['images'];
            foreach(array_keys($images['name']) as $fileNum) {
                $tmp_name = $images['tmp_name'][$fileNum];
                if(!is_uploaded_file($tmp_name)) {
                    continue;
                }
                try{
                    $imageInfo = getimagesize($tmp_name);
                }catch (\Exception $e) {
                    continue;
                }
                if(empty($imageInfo[0]) || empty($imageInfo[1])){
                    continue;
                }
                $newFileName = rand(1,PHP_INT_MAX) . $images['name'][$fileNum];
                try {
                    move_uploaded_file($tmp_name,  Locator::getConfig()->media_dir . '/' . $newFileName);
                } catch (\Exception $e) {
                    continue;
                }

                $attachment = new Attachment(array(
                    'type' => 'image',
                    'data' => $newFileName,
                    'message' => $message
                ));
                $this->attachmentManager->save($attachment);
            }
        }


        if(isset($this->request['POST']['links']) && is_array($this->request['POST']['links'])) {
            foreach($this->request['POST']['links'] as $link) {
                if(filter_var($link, FILTER_VALIDATE_URL) === false)continue;

                $attachment = new Attachment(array(
                    'type' => 'link',
                    'data' => $link,
                    'message' => $message
                ));
                $this->attachmentManager->save($attachment);

            }
        }

        if(isset($this->request['POST']['videos']) && is_array($this->request['POST']['videos'])) {
            foreach($this->request['POST']['videos'] as $video) {
                if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $video, $match)) {
                    $videoId = $match[1];

                    $attachment = new Attachment(array(
                        'type' => 'video',
                        'data' => $videoId,
                        'message' => $message
                    ));
                    $this->attachmentManager->save($attachment);

                }
            }
        }

        if($message->attachments->count() || $message->text) {
            $this->messageManager->flush();
        }

    }

    public function messagesLike() {
        $user = $this->getUser();
        $id = $this->param('id', 0);
        if(!$id) return '';

        $message = $this->messageManager->get($id);

        if(!$message || $message->likedUsers->contains($user)) {
            return '';
        }


        $this->userManager->save($user);
        $message->like($user);
        $this->messageManager->save($message, true);

        return json_encode(array('likes_count'=> $message->likesCount));
    }

    public function messagesGetLast() {
        $user = $this->getUser();
        $from = (int)$this->param('from', 0);

        $messages = $this->messageManager->getLastMessages($from, 5);

        $resultJSON = array();
        foreach(array_reverse($messages) as $message) {
            $resultJSON[$message->id] = array(
                'id' => $message->id,
                'text' => $message->text,
                'date' => $message->date->format('H:i:s'),
                'own' => $message->user == $user,
                'username' => $message->user->name,
                'likes_count' => $message->likesCount,
                'liked' => $message->likedUsers->contains($user),
                'attachments' => array()
            );
            foreach($message->attachments as $attachment) {
                $resultJSON[$message->id]['attachments'][$attachment->type][] = $attachment->data;
            }
        }
        return array(
            'content_type' => 'application/json',
            'text' => json_encode($resultJSON)
        );
    }

    public function messagesGetChanges() {
        $user = $this->getUser();
        $from = (int)$this->param('from', 0);

        $now = new \DateTime('now');
        $resultJSON = array(
            'now' => $now->format('U'),
            'liked' => array(),
            'deleted' => array(),
        );

        $fromDate = \DateTime::createFromFormat('U', $from);
        $fromDate->setTimezone(Locator::getTZ());
        $messages = $this->messageManager->getDeletedMessages($fromDate);
        foreach($messages as $message) {
            $resultJSON['deleted'][] = $message->id;
        }

        $messages = $this->messageManager->getLikedMessages($fromDate);
        foreach($messages as $message) {
            $resultJSON['liked'][] = array(
                'id' => $message->id,
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
