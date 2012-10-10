<?php
namespace App\Controllers;
use \App\System\Locator;

class Home {
    public function home($request) {

        return Locator::getTS()->render('base.html', array(
            'var' => 'dfdf'
        ));
    }
}