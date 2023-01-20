### Connect to mysqlserver with a specific user name
```
mysql -u [username] -p
```
(password prompt)
### Reset admin password
```
UPDATE `wp_users` SET `user_pass` = MD5( 'new_password' ) WHERE `wp_users`.`user_login` = "admin_username";
```
### Dump a specific database
```
mysqldump [-h host] -u [username] -p [databaseName] > [filename].sql
```
(password prompt)

#### Dump a database from  a containerized (docker) instance 
```
docker exec sql.$DOMAIN_NAME /usr/bin/mysqldump -u root --password=wordpress --single-transaction --skip-lock-tables wordpress > database.sql
```

### Import a database from SQL dump
```
mysql -u username -p database_name < file.sql
```
#### inject a SQL dump to a containerized (docker) instance of MySQL
```
cat database.sql | docker exec -i sql.$DOMAIN_NAME /usr/bin/mysql -u root --password=wordpress wordpress
```

### View current status and resources usage

```bash
docker exec -ti sql.$DOMAIN_NAME /bin/bash
mysql -u root -p
```

```sql
SHOW GLOBAL STATUS;
```
```sql
 SHOW GLOBAL STATUS LIKE '%Threads_connected%';
 SHOW GLOBAL STATUS LIKE '%Threads_running%';
 SHOW GLOBAL STATUS LIKE 'Connections';
 SHOW GLOBAL STATUS LIKE 'Aborted_c%';
 SHOW GLOBAL STATUS LIKE 'Connection_errors%';
 SHOW GLOBAL STATUS LIKE 'Max_used_connections%';
```

See current processes and detect possible queues of processes waiting for table locks:
```sql
SHOW PROCESSLIST;
```
### View active configuration
```sql
SHOW VARIABLES; 
```

```sql
SHOW VARIABLES LIKE '%max%'; 
```

### Legend
**max_connections** limits the number of connections overall. But usually many are in "Sleep" mode, therefore encurring virtually no load.

There is one "extra" connection allowed -- this lets 'root' get in to see what is going wrong, even when the max is hit.

By default **151** is the maximum permitted number of simultaneous client connections in MySQL 5.5. If you reach the limit of *max_connections* you will get the *“Too many connections”* error when you to try to connect to your MySQL server. This means all available connections are in use by other clients.

On systems with small RAM or with a hard number of connections control on the application side, we can use small *max_connections* values like 100-300. Systems with 16G RAM or higher *max_connections=1000* is a good idea, and per-connection buffer should have good/default values while on some systems we can see up to 8k max connections, but such systems usually became down in case of load spikes.



**Max_used_connections** is a high-water-mark toward max_connections.

Threads_running may be closest to what you are looking for. It is the current number of connections not in "Sleep". It is often "1", namely the connection you are using to read the value. A value of "10" is rather high. "100" probably means that MySQL is stumbling over itself.

SHOW PROCESSLIST gives you hints of some of the above info, plus clues of what queries are running.

**Connections** is a counter that started when the server started. Divide it by Updtime to get an average of new connections. 1/second is pretty typical. 100/second is high, but not necessarily 'bad'.

There is no metric to recent "recent", etc. The would require a monitoring program or script. I would not do SHOW GLOBAL STATUS more than once a minute, else it might have an adverse impact on the system.

**Connection_errors_max_connections** a counter that is optimally 0. Since you have 434 and Max_used_connections = 152, I will guess that you started max_connections with the default of 151, hit the error, then raised max_connections to 300. Am I right?

Raising to 300 is fine, though not necessary on 'most' machines. You should also see if having lots of connections is justified by your app. Maybe something else is going wrong.

A common issue is to have the webserver that talks to MySQL allow lots of connections. It then simply assumes it can hand things off to MySQL; instead, it should allow fewer connections to the webserver itself.

If **Threads_running** gets high, response time will probably suffer. The likely remedy is to find "slow" queries via the slowlog. The fix may be a better index or a reformulation of a query. Alas, Threads_running is hard to monitor.