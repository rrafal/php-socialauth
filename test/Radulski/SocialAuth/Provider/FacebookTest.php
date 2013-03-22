<?php

namespace Radulski\SocialAuth\Provider;

class FacebookTest extends  \PHPUnit_Framework_TestCase {
    
    function testConfig(){
        $p = new Facebook();
        $p->config(array(
            'app_id' => '123',
            'secret' => 'secret',
        ));
    }
    
}
