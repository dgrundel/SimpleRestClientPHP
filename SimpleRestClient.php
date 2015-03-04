<?php

class SimpleRestClient {
	private $dbname;
	private $dbtable;
	private $username;
	private $password;
	private $host;
	private $connection = null;

	public function __construct($dbname, $dbtable, $username = '', $password = '', $host = 'localhost') {
		$this->dbname = $dbname;
		$this->dbtable = $dbtable;
		$this->username = $username;
		$this->password = $password;
		$this->host = $host;
	}

	public function getConnection() {
		if($this->connection === null) {
			$c = new mysqli($this->host, $this->username, $this->password, $this->dbname);
			if($c->connect_error) {
				throw new Exception("Error Connecting to Database: {$c->connect_error}");
			} else {
				$this->connection = $c;
			}
		}
		return $this->connection;
	}

	public function makeJsonResponse($data) {
		header('Content-Type: application/json');
		echo json_encode($data);
		exit();
	}

	public function index() {
		$result = $this->getConnection()->query("SELECT * FROM {$this->dbtable}");
		$records = array();
		if($result) {
			while ($row = $result->fetch_assoc()) {
				$records[] = $row;
			}
		}
		$this->makeJsonResponse($records);
	}

	public function store($record) {
		$connection = $this->getConnection();
		$columns = implode(',', array_keys($record));
		$values = "'" . implode(',', array_map(array($connection, 'real_escape_string'), array_values($record))) . "'";

		$result = $connection->query("INSERT INTO {$this->dbtable} ({$columns}) VALUES ({$values})");

		// $sql="INSERT INTO tbl (col1_varchar, col2_number) VALUES ($v1,10)";

		// if($conn->query($sql) === false) {
		// 	trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
		// } else {
		// 	$last_inserted_id = $conn->insert_id;
		// 	$affected_rows = $conn->affected_rows;
		// }
	}

	public function update($id, $record) {
		// $v1="'" . $conn->real_escape_string('col1_value') . "'";

		// $sql="UPDATE tbl SET col1_varchar=$v1, col2_number=1 WHERE id>10";

		// if($conn->query($sql) === false) {
		// trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
		// } else {
		// $affected_rows = $conn->affected_rows;
		// }
	}

	public function delete($id) {
		// $sql="DELETE FROM tbl WHERE id>10";

		// if($conn->query($sql) === false) {
		// trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
		// } else {
		// $affected_rows = $conn->affected_rows;
		// }
	}
}