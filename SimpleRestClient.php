<?php

class SimpleRestClient {
	private $dbname;
	private $dbtable;
	private $username;
	private $password;
	private $host;
	private $connection = null;
	private $idColumnName = 'id';

	public function __construct($dbname, $dbtable, $username = 'root', $password = '', $host = 'localhost') {
		$this->dbname = $dbname;
		$this->dbtable = $dbtable;
		$this->username = $username;
		$this->password = $password;
		$this->host = $host;
	}

	public function init() {
		$method = $this->getRequestMethod();
		$id = $this->getIdFromRequest();
		$data = $this->getDataFromRequest();

		switch($method) {
			case 'post':
				$this->store($data);
				break;
			case 'put':
				$this->update($id, $data);
				break;
			case 'delete':
				$this->delete($id);
				break;
			case 'get':
			default:
				if(!empty($id)) {
					$this->show($id);
				} else {
					$this->index();
				}
		}
	}

	public function setIdColumnName($name) {
		if(is_string($name) && !empty($name)) {
			$this->idColumnName = $name;
		} else {
			throw new Exception("setIdColumnName: name must be a non-empty string.");
		}
	}

	public function getRequestMethod() {
		$method = $_SERVER['REQUEST_METHOD'];
		if(isset($_REQUEST['_method'])) {
			$method = $_REQUEST['_method'];
		}
		return strtolower($method);
	}

	public function getIdFromRequest() {
		$id = null;
		$id_params = array_unique(array('id', $this->idColumnName));
		foreach ($id_params as $param) {
			if(isset($_REQUEST[$param])) {
				$id = $_REQUEST[$param];
				break;
			}
		}
		return $id;
	}

	public function getDataFromRequest() {
		return isset($_REQUEST['data']) ? $_REQUEST['data'] : array();
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

	public function escapeId($id) {
		$id = intval($id);
		if($id > 0) {
			return $id;
		} else {
			throw new Exception("escapeId: invalid id.");
		}
	}

	public function escapeValue($value) {
		if(is_numeric($value)) {
			$int_value = intval($value);
			$float_value = floatval($value);
			return $int_value != $float_value ? $float_value : $int_value;
		} else {
			return "'" . $this->getConnection()->real_escape_string($value) . "'";
		}
	}

	public function escapeRecord($record) {
		$columns = array_keys($record);
		$columns = array_map(function($column){
			return $this->getConnection()->real_escape_string($column);
		}, $columns);
		
		$values = array_values($record);
		$values = array_map(array($this, 'escapeValue'), $values);

		return array(
			'columns' => $columns,
			'values' => $values,
		);
	}

	public function makeJsonResponse($data) {
		header('Content-Type: application/json');
		echo json_encode($data);
		exit();
	}

	public function index() {
		$result = $this->getConnection()->query("SELECT * FROM {$this->dbtable}");
		if($result === false) {
			$this->makeJsonResponse(array(
				'error' => $this->getConnection()->error
			));
		} else {
			$records = array();
			while ($row = $result->fetch_assoc()) {
				$records[] = $row;
			}
			$this->makeJsonResponse($records);
		}
	}

	public function show($id) {
		$id = $this->escapeId($id);
		$result = $this->getConnection()->query("SELECT * FROM {$this->dbtable} WHERE {$this->idColumnName} = {$id} LIMIT 1");
		if($result === false) {
			$this->makeJsonResponse(array(
				'error' => $this->getConnection()->error
			));
		} else {
			$record = null;
			if($result && $row = $result->fetch_assoc()) {
				$record = $row;
			}
			$this->makeJsonResponse($record);
		}
	}

	public function store($record) {
		$escaped = $this->escapeRecord($record);
		$column_string = implode(',', $escaped['columns']);
		$value_string = implode(',', $escaped['values']);

		$result = $this->getConnection()->query("INSERT INTO {$this->dbtable} ({$column_string}) VALUES ({$value_string})");

		if($result === false) {
			$this->makeJsonResponse(array(
				'error' => $this->getConnection()->error
			));
		} else {
			$this->makeJsonResponse(array(
				'insertId' => $this->getConnection()->insert_id
			));
		}
	}

	public function update($id, $record) {
		$id = $this->escapeId($id);
		$escaped = $this->escapeRecord($record);

		$escaped_record = array_combine($escaped['columns'], $escaped['values']);
		$update_values = array();
		foreach ($escaped_record as $column => $value) {
			$update_values[] = "{$column} = {$value}";
		}
		$update_string = implode(',', $update_values);

		$result = $this->getConnection()->query("UPDATE {$this->dbtable} SET {$update_string} WHERE {$this->idColumnName} = {$id} LIMIT 1");
		
		if($result === false) {
			$this->makeJsonResponse(array(
				'error' => $this->getConnection()->error
			));
		} else {
			$this->makeJsonResponse(array(
				'affectedRows' => $this->getConnection()->affected_rows
			));
		}
	}

	public function delete($id) {
		$id = $this->escapeId($id);

		$result = $this->getConnection()->query("DELETE FROM {$this->dbtable} WHERE {$this->idColumnName} = {$id} LIMIT 1");
		
		if($result === false) {
			$this->makeJsonResponse(array(
				'error' => $this->getConnection()->error
			));
		} else {
			$this->makeJsonResponse(array(
				'affectedRows' => $this->getConnection()->affected_rows
			));
		}
	}
}