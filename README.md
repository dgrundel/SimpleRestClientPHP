# SimpleRestClientPHP
A really simple RESTful JSON API

Note: This is just a simple example that connects to a MySQL database. *Not* production grade code.

## Prerequisites

You should have MySQL database setup with a table in which you'll be manipulating records.

## Setup

Include the main class file, then create a new instance. If you need to set the ID column name, you can do that. 
Then, call the init method. That's it!

### Minimum Example

```
$database_name = 'mysql';
$database_table_name = 'help_category';

require_once('SimpleRestClient.php');
$client = new SimpleRestClient($database_name, $database_table_name);
$client->init();
```

### Full Example

```
$database_name = 'mysql';
$database_table_name = 'help_category';
$database_user = 'root'; // optional, defaults to 'root'
$database_password = ''; // optional, defaults to ''
$database_host = 'localhost'; // optional, defaults to 'localhost'

require_once('SimpleRestClient.php');
$client = new SimpleRestClient($database_name, $database_table_name, $database_user, $database_password, $database_host);
$client->setIdColumnName('help_category_id'); // optional, default id column name is 'id'
$client->init();
```

## Usage

SimpleRestClient looks for a few parameters in the request. These can be included via GET or POST, as the $_REQUEST global is used to retrieve them.

### Request Parameters

**_method** (optional) forces SimpleRestClient to behave as if a particular HTTP verb was used (GET, POST, PUT, DELETE.) If not provided, $_SERVER['REQUEST_METHOD'] is used.

**id** or **your own id column name** (required for PUT, DELETE, optional for GET) tells SimpleRestClient which record to operate on. If you used *setIdColumnName*, you may use either *id* or your actual column name.

**data** (required for POST, PUT) is the array of data to load into the record.

#### Example Query String

Update (PUT) record with ID 45 with the following values:
name = 'Some Test Name', parent_category_id = 36, url = ''

```
_method=put&id=45&data[name]=Some%20Test%20Name&data[parent_category_id]=36&data[url]=
```
