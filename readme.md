# 本地开发
## 环境安装
本文以 Windows 下的环境安装为例。MacOS可通过 Homebrew 快速安装。
1. 安装 PHP 7.4。
2. 安装 PHP 的包管理器 Composer
3. 安装后端项目依赖 ```composer install```
4. 打开```.env```文件，确保```env=dev```。
5. 安装 nodejs。
6. 安装前端项目依赖 ```npm install```。
7. 安装数据库 ```mysql 5.7```，创建一个新的数据库，并将数据库名、用户名和密码修改到```.env```文件内。
8. 安装缓存数据库 ```redis```，用户名密码默认即可。
9. 安装 ```python 3```。
10. 安装 ```python``` 的包管理器 ```pip```。
11. 进入```./bin/notification``` 目录下安装 ```pip install -r requirements.txt```。
12. 在```config.py```中配置 Submail 的 API Key 及短信模板编号。

## 启动环境
第一次安装，及调整数据库模型后需要执行 ```php bin/console doctrine:schema:update --force```。
其余情况，Windows 可运行 ```dev.bat``` 快速启动本地开发环境。Unix 系统可执行 ```php bin/console server:start``` 及 ``` ./node_modules/.bin/encore dev --watch``` 启动。

# 服务器部署
参考 `prod.sh`。

# 版本说明
**为保证安全，每年投票前请升级前后端依赖包**

后端使用 Symfony 架构，当前版本 4.4.13(LTS)，官方支持至2023年11月。后端升级可直接执行```composer update```，官方保证无 breaking changes。

前端使用 Vue 架构。NPM 包基本无 LTS 版本，升级需手动调整。
