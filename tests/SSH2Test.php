<?php

namespace Ruanwenqin\Tests;

class SSH2Test extends \PHPUnit_Framework_TestCase
{
    public function testLoginWithRSA()
    {
        $ssh = new \phpseclib\Net\SSH2('try6');
        $key = new \phpseclib\Crypt\RSA();
        $key->loadKey(file_get_contents('/Users/ruanwenqin/.ssh/id_rsa'));
        if (!$ssh->login('root', $key)) {
            exit('Login Failed');
        }
        
    }

    public function testLoginWithPassword()
    {
        $ssh = new \phpseclib\Net\SSH2('try6');
        if (!$ssh->login('root', 'topxiaxia')) {
            exit('Login Failed');
        }
        
    }

    public function testLoginWithNoPassword()
    {
        $ssh = new \phpseclib\Net\SSH2('try6');
        if (!$ssh->login('root')) {
            exit('Login Failed');
        }
        
    }
}
