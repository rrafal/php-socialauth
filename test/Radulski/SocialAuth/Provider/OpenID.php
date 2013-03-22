<?php

namespace Radulski\SocialAuth\Provider;


class GoogleTest extends  \PHPUnit_Framework_TestCase {
    
    function testConfig(){
        $p = new OpenID();
        $p->config(array(
            'user_url' => 'http://example.com/user',
        ));
    }
    
}
