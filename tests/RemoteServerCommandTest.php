<?php

namespace Ruanwenqin\Tests;

use Ruanwenqin\Command\SSHCommandCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class SSHCommandCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testGetIpList()
    {
        $commnad = new SSHCommandCommand();
        $ipList = $commnad->getIpList(__DIR__.'/fixtures/iplist.txt');
        $this->assertCount(5, $ipList);
        $this->assertEquals('192.168.1.1', $ipList[0]);


        $ipList = $commnad->getIpList('192.168.1.1,192.168.2.1,192.168.3.1');
        $this->assertCount(3, $ipList);
        $this->assertEquals('192.168.3.1', $ipList[2]);
    }

    public function testRun()
    {
        $commandTester = $this->createSSHCommandCommand();
        $commandTester->execute(array('config' => __DIR__.'/fixtures/ssh-cmd-test.yml'));

        $output = <<<'EOF'
开始测试服务器SSH连通性...
连通正常.....
try6:执行命令 - 'ls /'

EOF;

        $this->assertEquals($output, $commandTester->getDisplay());
    }

    private function createSSHCommandCommand()
    {
        $application = new Application();
        $command = new SSHCommandCommand();
        $application->add($command);

        return new CommandTester($application->find('ssh:cmd'));
    }
}
