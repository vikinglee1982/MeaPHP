# 开发日志 #

#### 框架思路与想法 ####

1. 框架值作为服务器服务端程序，不提供页面渲染等其他任务，只提供api接口服务

2. api接口，操作数据库；公共工具等

3. 暂时不计划使用单一入口模式


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

7. 查看分支
```
git branch
```
8. 切换分支
```
git checkout main
```
