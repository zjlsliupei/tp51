# tp51
底层基于tp5.1，增加大量常用函数，扩展

## 配置获取 
会从conf/conf.ini文件中读取配置，如果有环境变量，会自动加上环境变量参数
$host = e_config('database_host');
