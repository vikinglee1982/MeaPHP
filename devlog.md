# 开发日志 #

#### 框架思路与想法 ####

1. 框架值作为服务器服务端程序，不提供页面渲染等其他任务，只提供api接口服务

2. api接口，操作数据库；公共工具等

3. 暂时不计划使用单一入口模式

#### 工作计划 ####
1：数据库操作由mysqli转为PDO（多数据库支持）
2：参数接受验证Verify类文件的修改（考虑安全验证和手机号；身份证号码等的验证是否还在同一个文件里面；和原来的一致）
3：生成缩略图Fotophire类文件的修改
4: 新建格式化类文件；用于各类数据的格式化：比如时间日期；保留小数点的价格（考虑是否独立创建类文件）

#### git 工具命令 ####

1. 将所有修改的文件添加到暂存区

````
git add -A
````

2. 将暂存区的内容提交到本地仓库，并注明了修改内容注释

````
git commit -m "修改的内容注释"
````

3. 将本地仓库中的修改内容推送到github中的仓库的 “main”分支中

````
git push origin main
````

4. 查看git 状态;在那个分支上

````
git status
````
5. 删除远程库文件，但本地保留该文件    rm中多文件需要 -r

````
git rm --cached xxx
````
````
git commit -m "remove file from remote"
````
````
git push -u origin master
````

6. 拉取覆盖到本地

````
 git pull origin main
````

7. 查看分支 [尾部添加 -a；同时显示远程分支]
```
git branch -a
```
8. 切换分支
```
git checkout main
```
9. 合并分支：将指定分支合并到当前分支[将dev分支合并到当前分支]
```
git merge dev
```
10. 删除本币分支
```
git branch -d name
```
11. 删除远程分支
```
git push origin --delete branch_name
```
12. 忽略本地文件提交
```
文件夹内添加 .gitigmore 文件
打开添加
**/node_modules
```
13. git区分大小写（默认忽略大小写）
```
git config core.ignorecase false
```
