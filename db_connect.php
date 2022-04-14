<?php
class DbConnection extends mysqli{
	protected $db_host = "localhost"; 
	protected $db_username = "";  
	protected $db_pass = "";  
	protected $db_name = "changeform";
	protected $db_port = 8080;
	protected $connection;

	public function __construct(){
		parent::__construct($this->db_host, $this->db_username, $this->db_pass, $this->db_name, $this->db_port);
	}
	
}
