# 我的远程服务器命令

```
//使用方法
bin/console ssh:cmd ssh-cmd.yml
```

```
ssh-cmd.yml配置文件说明：
ips: 'try6' //远程服务器ip，或者域名，1.多个使用逗号隔开 2.也可以是一个包含ip地址的文件，绝对路径，一个ip一行
username: root //需要使用ssh命令的用户
privatekey: '/Users/ruanwenqin/.ssh/id_rsa' //使用私钥登陆，如果填了，则使用私钥登陆。
password: topxiaxia //如果私钥没填，则使用密码登陆
cmd: 'ps aux|grep nginx' //需要远程服务器执行的命令,如果是后台命令,请使用nohup 和&
```