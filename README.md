# MeaPHP 一个简单快速的API服务的PHP框架 #

基于前后端分离的开发模式，将php作为纯服务器语言，只提供api接口服务的服务器框架

## 配置及使用 ##

+ **多站点模式**

1. 将MeaPHP放到服务器根目录下；linux系统的常规情况下的：var/www/html/;
2. 在 var/www/html 下新建站点api服务 根目录 ；域名解析的根目录
3. 将根目录下的Config文件复制到项目根目录中；
4. 修改Config/Config.php的项目相关数据
5. 建立api接口目录
6. 目录结构

   ```md
   >webSite1 [域名解析根目录]
      >Api  [业务接口文件夹]
         >BO
         >Port
         >WarningPage
         >index.php  [Api入口文件，需要引入config.php]
      >Config
         >Config.php [webSite1 配置文件]
      >Log  [错误日志]
      >Resource   [站点的接受文件；图片、音乐、文件等储存仓库]
      >WWW
         >index.html [webSite1 网站页面文件]

   >webSite2 [域名解析根目录]
      >Api  [业务接口文件夹]
         >BO
         >Port
         >WarningPage
         >index.php  [Api入口文件，需要引入config.php]
      >Config
         >Config.php [webSite2 配置文件]
      >Log  [错误日志]
      >Resource   [站点的接受文件；图片、音乐、文件等储存仓库]
      >WWW
         >index.html [webSite2 网站页面文件]

   >MeaPHP [框架核心文件]
   ```

7. api接口文件中 引入

   ```php
   //请参口文件样例
   include_once $_SERVER['DOCUMENT_ROOT'] . '/Config/Config.php';
   ```

8. 可以正常使用框架所提供的工具类处理业务了

**API返回格式**
1.recode

+ 2000-2999 成功
+ 3000-3999 debug模式下：不会退出系统；上线：高权重错误：权限错误、安全等需要剔除系统错误；这里考虑是否需要将ip加入黑名单
+ 4000-4999 参数错误
+ 5000-5999 系统程序错误
+ 6000-6999 Api路径或文件错误
+ 7000-7999 调试模式：错误
+ 8000-8999 调试模式：配置错误

2.data

+ 成功:$data['自定义']
+ 失败:$data['msg'] = "错误信息";
+ 响应时间： $data['ms'] = date('Y-m-d h:i:s', time());

### 框架提供工具 ###

### $\color{#409EFF}[DataBase类]$ ###

1. **查询单行**

   $DB->selectOne(sql语句,数据返回格式)

   ```php
   $res = $DB->selectOne("SELECT * FROM 表名 WHERE 字段 = 条件值",1-3);
   ```

2. **查询多行**

   $DB->selectAll(sql语句,数据返回格式);

   ```php
   $res = $DB->selectAll("SELECT * FROM 表名 WHERE 字段 = 条件值",1-3);
   ```

3. **插入数据**

   $DB->execute(sql语句);

   + 单行

      ```php
      $DB->execute("INSERT INTO 表名(字段1,字段2) VALUES ('值1','值2')");
      ```

   + 多行

      ```php
      $DB->execute("INSERT INTO 表名(字段1,字段2) VALUES ('值1','值2')，('值1','值2')，('值1','值2')，('值1','值2')");
      ```

4. **删除数据**

   $DB->execute(sql语句);

   ```php
   $DB->execute("DELETE FROM 表名 WHERE 字段 = 条件");
   ```

5. **修改单行数据**

   $DB->execute(sql语句);

   + 单字段

      ```php
      $DB->execute("UPDATE 表名 SET 字段 = '值'  WHERE  条件字段 = '条件值'");
      ```

   + 多字段

      ```php
      $DB->execute("UPDATE 表名 SET 字段 = '值' ,字段 = '值',字段 = '值' WHERE  条件字段 = '条件值'");
      ```

6. **修改多行数据**

   + 单字段

      ```php
      $DB->execute("UPDATE 表名 SET
            字段 = CASE 条件字段
               WHEN '条件值1' THEN '对应值1'
               WHEN '条件值2' THEN '对应值2'
               WHEN '条件值3' THEN '对应值3'
            END
      WHERE  条件字段 IN ('条件值1','条件值2','条件值3') ");

      ```

   + 多字段

      ```php
      $DB->execute("UPDATE 表名 SET
            字段1 = CASE 条件字段
               WHEN '条件值1' THEN '对应值1'
               WHEN '条件值2' THEN '对应值2'
               WHEN '条件值3' THEN '对应值3'
            END,
            字段2 = CASE 条件字段
               WHEN '条件值1' THEN '对应值1'
               WHEN '条件值2' THEN '对应值2'
               WHEN '条件值3' THEN '对应值3'
            END
      WHERE  条件字段 IN ('条件值1','条件值2','条件值3') ");
      ```

   + 混合使用

      当有些字段需要设置统一的值，可以这样混合使用
      END后面需要添加逗号
      这样对应条件值多行的【字段a】和【字段b】都会修改成对应值a和对应值b

      ```php
      $DB->execute("UPDATE 表名 SET
            字段 = CASE 条件字段
               WHEN '条件值1' THEN '对应值1'
               WHEN '条件值2' THEN '对应值2'
               WHEN '条件值3' THEN '对应值3'
            END,
            字段a = 对应值a,
            字段b = 对应值b
      WHERE  条件字段 IN ('条件值1','条件值2','条件值3') ");
      ```

7. **返回符合条件的数据条数**

   $DB->rowNum("SELECT * FROM 后的Sql语句");

   ```php
   $DB->rowNum("$tableAndCondition");
   ```

   例如：
   1:查询单表的所有条数

   ```php
   $DB->rowNum("SELECT * FROM $table");
   ```

   2:多表或连表等查询数量

   ```php
   $DB->rowNum("SELECT * FROM tableName1 LEFT JOIN tableName2 ON tableName1.id = tableName2.id WHERE ....");
   ```

8. **返回上一次数据库插入的id**

   ```php
   $DB->getInsertId()
   ```

### 工具类返回数据格式 ###

+ 成功返回：

   ```php
   return array(
      'status'=>'ok',
      'data'=>返回的数据
   );
   ```

+ 失败返回

   ```php
   return array(
      'status'=>'error',
      'msg'=>className->lineNum:错误原因
   );
   ```

### $\color{#409EFF}[MID类]$ ###

用于id的管理

1. id生成;最多支持添加3个前缀；至少一个前缀;
编号规则：前缀1+[前缀2]+[前缀3]+毫秒级时间戳;建议至少有2个前缀;
使用建议 `$prefix1 = 订单类别`; `$prefix2 = uid`

___
注意：
   greate方法生成的并不是严格意义上不重复的id；
   目前业务中还没有这样的需求，如果有需求可以创建新的function使用`session_create_id()`生成
___

```php
$res = MID->create($prefix1, [$prefix2], [$prefix3]);
```

***param***
$prefix1 string
***return***

```php
return array(
   'status'=>'ok',
   'data'=>图片资源
);
```

### [Captcha类] ###

   ___
   注意：
   需要php开启gd2
   ___
`echo phpinfo();` 如果没有当前扩展，请安装php扩展 `yum install php-gd` 当前工具类只能生成图片验证码

1. 生成4位包含数字大小写英文的图片验证码

   ```php
   $res = $Captcha->getImage();
   //如果测试需要查看输入的图片
   header("content-type:image/png");
   imagepng($res['data']);
   ```

   ***return***

   + 成功状态

   ```php
   return array(
      'status'=>'ok',
      'data'=>图片资源
   );
   ```

   + 失败状态

      当前类没有失败状态；如果失败报错；请检查是否开启了gd2

### $\color{#409EFF}[Save类]$ ###

用户保存用户上传的文件

1. 保存上传的图片

   ```php
   $Save->image(resource $file,string $filePath,[string $fileName]);
   ```

   ***param***
   `$file`:上传的图片资源；支持格式[gif/jpg/jpge/png//webp]
   `$filePath`:文件夹目录（项目根路径下的目录）;路径的合成：`$_SERVER['DOCUMENT_ROOT']  . $filePath;`
   `$fileName`:可选参数；文件名称；没有入参自动生成；

   ***return***

   + 成功返回

      $path:已经文件的储存路径+文件名称
   + 失败返回

      "error:请缺少入参image文件";
      "error:文件类型支持[gif/jpg/jpge/png//webp]";
      "error:缺少文件夹目录（项目根路径下的目录）";
      "error:已经有这个文件了";

2. 批量保存上传的图片

   ```php
   $Save->batchImage($arr);
   ```

   ***param***
   `$arr`:上传图片资源的数组
   数组的格式新要求：

   ```php
   [
      0:{
         resource:图片资源,
         path:从项目根路径下开始的储存路径,
         }
      1:{
         resource:图片资源,
         path:从项目根路径下开始的储存路径,
      }
      ]
   ```

   路径的合成：`$_SERVER['DOCUMENT_ROOT']  . $arr[i][Path];`

+ 成功返回

   ```php
   $res['status'] = 'ok';
   $res['data']=[
      '已经文件的储存路径+文件名称',
      '已经文件的储存路径+文件名称'
   ]
   ```

+ 失败返回

   ```php
   $res['status'] = 'error';
   $res['msg'] = '入参必须是数组';
   $res['msg'] = '入参的数组结构错误;
               [
                     0:{
                        resource:图片资源,
                        path:从项目根路径下开始的储存路径,
                        }
                     1:{
                        resource:图片资源,
                        path:从项目根路径下开始的储存路径,
                        }
                  ]';
   $res['msg'] = '其余错误和单张图片储存相同';
   ```

### $\color{#409EFF}[MoveFile类]$ ###

用于移动已储存服务端的文件；并删除旧文件

1. 单文件移动

   ```php
   $MoveFile->monofile(String $oldName = '',String $newName = '',Boole $mkdir = false);
   ```

   ***param***

      `$oldName`:已保存到服务端旧文件的完全路径
      `$newName`:服务端的移动的新位置
      `$mkdir`:新位置文件夹不存在的情况下是否新建文件夹

   + 成功返回

      ```php
      $res['status'] = 'ok';
      $res['data']='已经文件的储存路径+文件名称',
      ```

   + 失败返回

      ```php
      $res['status'] = 'error';
      $res['msg'] =  '缺少oldName';
      $res['msg'] = '缺少newName';
      $res['msg'] = '未找到oldname的文件';
      $res['msg'] = '移动文件失败';
      $res['msg'] = '新文件所在的目录不存在';
      ```

2. 数组式多文件递归移动

   ```php
   $MoveFile->multifile(Array $arr,Boole $mkdir = false);
   ```

   ***param***

   `$arr`:参数数组:数组类型要求如下：

   ```php
   [
      0:{
         oldName:当前文件包含文件名的完整目录,
         newName:需要移动到的完整目录，且包含文件名,
      },
      1:{
         oldName:当前文件包含文件名的完整目录,
         newName:需要移动到的完整目录，且包含文件名,
      }
   ]';
   ```

   `$mkdir`:新位置文件夹不存在的情况下是否新建文件夹

   + 成功返回

   ```php
   $res['status'] = 'ok';
   $res['data']=[
      '新的文件储存路径+文件名称',
      '新的文件储存路径+文件名称'
   ],
   ```

   + 失败返回

   ```php
   $res['status'] = 'error';
   $res['msg'] =  '入参格式必须是array';
   $res['msg'] = '入参的数组格式：
   [
   0:{
      oldName:当前文件包含文件名的完整目录,
      newName:需要移动到的完整目录，且包含文件名,
      },
   1:{
      oldName:当前文件包含文件名的完整目录,
      newName:需要移动到的完整目录，且包含文件名,
   }
   ]';
   $res['msg'] = '其余错误和单文件移动相同';
   ```
