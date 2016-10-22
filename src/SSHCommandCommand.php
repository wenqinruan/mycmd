<?php

namespace Ruanwenqin\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use phpseclib\Net\SSH2;

class SSHCommandCommand extends Command
{
    protected $ipList = array();
    protected $username = '';
    protected $password = '';
    protected $sshSessionList = array();
    protected $isConnectedOK = true;
    protected $input;
    protected $output;

    protected function configure()
    {
        $this
        // the name of the command (the part after "bin/console")
        ->setName('ssh:cmd')
        ->addArgument('ips', InputArgument::REQUIRED, '远程服务器的IP，用逗号隔开，也可以是一个包含服务器IP的文件绝对路径')
        ->addArgument('cmd', InputArgument::REQUIRED, '要执行的命令')
        ->addArgument('username', InputArgument::OPTIONAL, '运行命令的用户，默认使用运行此command的用户')
        ->addArgument('password', InputArgument::OPTIONAL, '远程服务器的密码，默认密码为空')

        // the short description shown while running "php bin/console list"
        ->setDescription('给远程服务器发送命令')

        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp("给远程服务器发送命令");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->ipList = $this->getIpList($input->getArgument('ips'));
        $this->password = $input->getArgument('password');
        $this->username = $input->getArgument('username');

        if (!$this->username) {
            $this->username = trim(shell_exec('whoami'));
        }

        $this->tesConnected();
        if (!$this->isConnectedOK) {
            return;
        }
  
    }

    protected function tesConnected()
    {
        $this->output->writeln("开始测试服务器SSH连通性...");
        foreach ($this->ipList as $ip) {
            $ssh = new SSH2($ip);

            $isOk = false;
            if ($this->password) {
                $isOk = $ssh->login($this->username, $this->password);
            } else {
                $isOk = $ssh->login($this->username);
            }
            if (!$isOk) {
                $this->isConnectedOK = false;
                $this->output->writeln("无法连通IP：".$ip);
            }

            $this->sshSessionList[] = $ssh;
        }

        if (!$this->isConnectedOK) {
            $this->output->writeln("服务器SSH连通有问题，请检查...");
        } else {
            $this->output->writeln("连通正常.....");
        }
    }

    public function getIpList($ipString)
    {
        if (file_exists($ipString)) {
            $ipList = explode(PHP_EOL, file_get_contents($ipString));
        } else {
            $ipList = explode(',', trim($ipString));
        }

        return $ipList;
    }
}
