<?php

namespace Radulski\SocialAuth\Provider;


class GoogleTest extends  \PHPUnit_Framework_TestCase {
    
    function testConfig(){
        $p = new Google();
        $p->config(array(
            'client_id' => '123',
            'client_secret' => 'secret',
        ));
    }
    
    function testBeginLogin(){
        $p = new Google();
        $p->beginLogin('profile');
    }
    
}
