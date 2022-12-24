# MeaPHP 一个简单快速的多入口API服务的PHP框架 #

基于前后端分离的开发模式，将php作为纯服务器语言，只提同api接口服务的服务器框架；重新构建一个全新的构架

#### 配置及使用 ####
1. 将MeaPHP放到服务器根目录下；linux系统的常规情况下的：var/www/html/
2. 在 var/www/html 下新建站点api服务 根目录 ；域名解析的根目录
3. 将根目录下的Config文件复制到项目根目录中；
4. 修改Config/Config.php的项目相关数据
5. 建立api接口目录
6. api接口文件中 引入
````
include_once $_SERVER['DOCUMENT_ROOT'] . '/Config/Config.php';
````
7. 可以正常使用框架所提供的工具类处理业务了

#### 框架提供工具 ####
* [DB类](#1)
1.  **查询单行**
$DB->selectOne(sql语句,数据返回格式)
```
$res = $DB->selectOne("SELECT * FROM 表名 WHERE 字段 = 条件值",1-3);
```
2. **查询多行**
$DB->selectAll(sql语句,数据返回格式);
````
$res = $DB->selectAll("SELECT * FROM 表名 WHERE 字段 = 条件值",1-3);
````
3. **插入数据**
$DB->execute(sql语句);
 + 单行
```
$DB->execute("INSERT INTO 表名(字段1,字段2) VALUES ('值1','值2')");
```
 + 多行
````
$DB->execute("INSERT INTO 表名(字段1,字段2) VALUES ('值1','值2')，('值1','值2')，('值1','值2')，('值1','值2')");
````
4.   **删除数据**
$DB->execute(sql语句);
```
$DB->execute("DELETE FROM 表名 WHERE 字段 = 条件");
```
5. **修改数据**
$DB->execute(sql语句);
+ 单字段
```
$DB->execute("UPDATE 表名 SET 字段 = '值'  WHERE  条件字段 = '条件值'");
```
+ 多字段
```
$DB->execute("UPDATE 表名 SET 字段 = '值' ,字段 = '值',字段 = '值' WHERE  条件字段 = '条件值'");
```
6. **返回符合条件的数据条数**
$DB->rowNum(sql语句);
```
$DB->rowNum("SELECT * FROM 表名 WHERE 字段 = 条件值");
```
7. **返回上一次数据库插入的id**
```
$DB->getInsertId()
```