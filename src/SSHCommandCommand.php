<?php

namespace Ruanwenqin\Command;

use Symfony\Component\Yaml\Yaml;
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
    protected $privatekey = '';
    protected $cmd = '';
    protected $sshSessionList = array();
    protected $isConnectedOK = true;
    protected $input;
    protected $output;

    protected function configure()
    {
        $this
        // the name of the command (the part after "bin/console")
        ->setName('ssh:cmd')
        ->addArgument('config', InputArgument::REQUIRED, '配置文件')

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
        $config = Yaml::parse(file_get_contents($input->getArgument('config')));

        if (!$config) {
            $output->writeln("<error>配置文件不存在</error>");
            return;
        }

        $this->ipList = $this->getIpList($config['ips']);
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->privatekey = $config['privatekey'];
        $this->cmd = $config['cmd'];

        if (!$this->username) {
            $this->username = trim(shell_exec('whoami'));
        }

        $this->tesConnected();
        if (!$this->isConnectedOK) {
            return;
        }


        $this->runCMD();
  
    }

    protected function runCMD()
    {
        foreach ($this->sshSessionList as $ip => $ssh) {
            $this->output->writeln("{$ip}:执行命令 - '{$this->cmd}'");
            echo $ssh->exec($this->cmd);
        }
    }

    protected function tesConnected()
    {
        $this->output->writeln("开始测试服务器SSH连通性...");
        foreach ($this->ipList as $ip) {
            $ssh = new SSH2($ip);

            $isOk = false;
            if ($this->privatekey) {
                $key = new \phpseclib\Crypt\RSA();
                $key->loadKey(file_get_contents($this->privatekey));
                $isOk = $ssh->login($this->username, $key);
            } else {
                $isOk = $ssh->login($this->username, $this->password);
            }
            if (!$isOk) {
                $this->isConnectedOK = false;
                $this->output->writeln("无法连通IP：".$ip);
            }

            $this->sshSessionList[$ip] = $ssh;
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
