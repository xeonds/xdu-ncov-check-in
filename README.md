# xdu-ncov-check-in
## 介绍
晨午晚检自动填报系统。不过是部署在Linux服务器上的。

* 部署：很简单，将网站拷贝到一个有PHP7.x，Python3.x的Linux服务器的网站目录中即可。自动打卡需要crontab和curl支持。详见下文
* 使用：普通用户注册即可使用。管理员页面是`admin.html`，默认密码是`4321`，在`assets/db/config.json`中修改。

管理员可以一次性给所有人打卡，可生成授权码。授权码用于注册验证。
用户可以查看打卡日志，可以手动打卡。
**部署的时候注意把`admin.html`和`index.html`里的js和css换成cdn的，开发的时候忘了换。**

## 配置自动打卡
原理：用`GET`方式调用API`core.php?admin=check_in`即可为所有人打卡。

实现方式A：我目前用的是这个方式。在`crontab`中添加计划任务，定时访问即可：

```bash
0 8 * * * curl http://localhost/xdu-ncov-check-in/core.php?admin=check_in
30 12 * * * curl http://localhost/xdu-ncov-check-in/core.php?admin=check_in
30 18 * * * curl http://localhost/xdu-ncov-check-in/core.php?admin=check_in
```

附：`crontab`参数含义：`分 时 每月第几天 月 每周第几天 指令`

>PS:引用的项目回头补上（）
