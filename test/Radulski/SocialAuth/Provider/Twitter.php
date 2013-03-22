<?php

namespace Radulski\SocialAuth\Provider;


class GoogleTest extends  \PHPUnit_Framework_TestCase {
    
    function testConfig(){
        $p = new Twitter();
        $p->config(array(
            'consumer_key' => '123',
            'consumer_secret' => 'secret',
        ));
    }
    
}
