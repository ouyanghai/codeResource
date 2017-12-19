
===========================================
Storage 直接支持 memcache redis 等外部接口

Storage 支持 dba  Berkery 数据库支持
Storage 支持 shmop memcache 共享内存数据支持

Storage 支持 file
Storage 支持 ftp
Storage 支持 webdav

以下类全部依赖本存储类，存储类实现缓存和数据存储


===========================================

异步任务服务：提供实时任务和定时任务的异步处理功能，
Task.class.php

Task::create($name,$options)
Task::remove($name)

Task::insert($name,$data)
Task::delete($name,$data)


Task::run($name)
Task::query($name,$id)


===========================================

消息队列服务：当发布一个消息时，系统会给所有订阅者发送通知（异步主动请求订阅者）  看一下 PHP的扩展库 SAM 是否可以借鉴
MQ.class.php

MQ::list() // 查询所有消息队列、以及每个队列的订阅者和消息总量等基本信息

MQ::publish($name,$message) // 指定对某个消息队列发布消息
MQ::query($name) // 查询发布过的所有消息

MQ::subscribe($name,$url) // 订阅某个消息队列
MQ::unsubscribe($name,$url) // 取消订阅某个消息队列
MQ::check($name,$url) // 检查订阅者没有收到的消息
MQ::fetch($name) // 主动抓取消息队列的内容

MQ::run($name) // 供外部程序调用（核心部分、包含整个消息队列的内部实现机制）


===========================================
Rank


===========================================
ShortUrl
Add: 数据统计