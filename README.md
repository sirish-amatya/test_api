# Test API - Simple HTTP API implementation
Sample API implementation to fetch records using php, mysql, docker

This program is a simple HTTP API service to fetch records from database using PHP, MySql and Docker. It has below mentioned features

1. List all records: http://localhost:8080/search/all/?api_user=testapi&api_pass=Api@123
2. Get single record by id: http://localhost:8080/search/id/?api_user=testapi&api_pass=Api@123&keyword=5a0e4b8279821d79b0c8ccc2
3. Get records by name (fulltext search by name): http://localhost:8080/search/name/?api_user=testapi&api_pass=Api@123&keyword=burt+england+moss+kirk

# Getting Started

The controller is present in class/Record.php
```

http://localhost:8080/<func_part1>/<func_part2>/?<get_variables>
```

For the above structure, the function is called in below mentioned fashion
```
Record::<func_part1><func_part2>(<get_variables>) 
```
Eg. for http://localhost:8080/search/all/?api_user=testapi&api_pass=Api@123

```

Record::searchAll(['api_user'=>'testapi', 'api_pass'=>'Api@123']) 
```
is called

### Related queries for searchAll, searchName and searchId
```

SELECT * FROM students;
SELECT * FROM students WHERE MATCH (first, last) AGAINST (:keyword IN NATURAL LANGUAGE MODE);
SELECT * FROM students WHERE id = :id;
```


Note 
 - Upto 500 records can be fetched in one API hit. When there is huge data in the database, it is better to fetch data in batches with multiple API hits rather than fetching it all at once. However, this value can be adjusted from constants.php
 - The sample data is present in sample-data.json

## Folder Structure

```
.
├── custom.conf
├── docker-compose.yaml
├── Dockerfile
├── sample-data.json
├── Seeder.php
└── src
    ├── class
    │   ├── Output.php
    │   ├── Record.php
    │   └── Request.php
    ├── config
    │   └── constants.php
    └── index.php
```

## Constants
It is present in src/config/constants.php
These can be changed as per the requirement

```

define('BASE_URL', 'http://localhost:8080');

//DB Credentials
define('DB_HOST', 'db');
define('DB_USER', 'apiuser');
define('DB_PASS', 'apipassword');
define('DB_NAME', 'school');

//API Credentials
define('API_USER', 'testapi');
define('API_PASS', 'Api@123');

//Batch length
define('MAX_BATCH_LENGTH', 500);
```

### Steps to execute the program

Step 1: Use the below docker command to make the build and run it
```

sudo docker-compose build
sudo docker-compose up -d
```
Step 2: Check the status of the containers using the below mentioned command
```

sudo docker-compose ps
```
```

Name                   Command               State           Ports          
-------------------------------------------------------------------------------
testapi_app_1   docker-php-entrypoint /usr ...   Up      0.0.0.0:8080->80/tcp   
testapi_db_1    docker-entrypoint.sh mysqld      Up      0.0.0.0:3306->3306/tcp
```
Step 3: Once the server is up, run the below mentioned command to seed the database
```

sudo docker exec -it testapi_app_1 php /tmp/Seeder.php
```
## Error Message and it's meaning

|Status | Error Message | Meaning |
|-------| --------------| --------|
| 503 | DB server is not ready. | DB Server is not up completely |
| 503 | Server not ready! Data unavailable. Please contact administrator | Seeder.php is not executed |
| 401 | Invalid API user or password. | The api_user and api_pass in URL is not valid |
| 404 | Invalid url. Please check the url. | The given API url is not valid |
| 400 | [from] in batch is not valid | The parameter batch=from,length. from part should be integer |
| 400 | [length] in batch is not valid. Value should be between 1 - MAX_BATCH_LENGTH | length part of batch is not valid |
| 400 | Keyword is not given. | keyword parameter is not passed in the url |
| 400 | No records found | No matching records found |


### Sample program output
```
http://localhost:8080/search/id/?api_user=testapi&api_pass=Api@123&keyword=5a0e4b8279821d79b0c8ccc2
```
Output
```
{"data":{"name":{"first":"Howe","last":"Baldwin"},"eyeColor":"brown","age":"39","isActive":false,"_id":"5a0e4b8279821d79b0c8ccc2"},"status":"success"}
```
### Prerequisites

- docker-compose
- docker
- ubuntu (xenial)

Ref: https://docs.docker.com/engine/installation/linux/docker-ce/ubuntu/

