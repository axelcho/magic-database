<?php 
//todo list
//union
//having
//group by
//rollup
//subtotal
//distribution of values
//email
//soap
//
//author: axel cho<axelcho@gmail.com>
//version: 2.0
//2011.1.18

class Magic 
    { 
		protected $where = null;
        protected $connect = null; 
		protected static $mana = "1";
		
		//page variables
		public $page = null;
		public $perpage = null;
		public $first = null;
		public $last = null;
	
		//select,  insert, update operation variables
		public $table =null;
		public $selectfield = "*";
		public $fields = array();		
		public $op = null;
		public $update = "`update`";
		public $orderby = null;
		public $order = "DESC";
		
		//table variable
		public $style = "border='1px'";
		
		//match operation variables
		public $tb_1 = null;
		public $tb_2 = null;
		public $key_1 = null;
		public $key_2 = null;

		//part I: the basic functions
		
		//constructor and destructor 
        public function __construct($include = "Magic") 
        { 	require $include.".define.php"; //load database statics			
			if (Magic::$mana > 0){  //check duplicate connection, which means error - use static instead of variable to prevent injection of values by users
			$this->connect_db(); 
			Magic::$mana = 0;
			}
			else {
			die("Existing database connection detected.");
			}
        } 
		
		public function __destruct()
		{
			$this->close_db();
			Magic::$mana = 1; 
		}
		
		public function __call($field, $value) //With this 'magic method', any calls to undefined functions such as $this->City("New York") will be interpreted as mysql element of "City LIKE 'New York'". The call will be sanitized by mysql_real_escape_strings for safety's sake.
		{
        $operator = $value[1];
		if (!isset($operator)) 	$operator = "LIKE"; //default is "LIKE". Users may add operators like "=", "in", "NOT LIKE", etc.
		$field = $this->escape($field); //field name 
		$data = "'".$this->escape($value[0])."'"; //values
		$operator = $this->escape($operator);  
		$where = $field." ".$operator." ".$data;
		$this->addWhere($where);
		}
		
		//regenerate initial values
		protected function regen($level="base") //reinitialize variables
		{
		$this->table =null;
		$this->selectfield = "*";
		$this->where = null;
		$this->orderby = null;
		$this->order = "DESC";
		
		switch ($level)
		{
		case "insert":		
		$this->op = null;
		$this->update = "`update`";
		$this->fields = array();
		break;
		
		case "unmatch":
		$this->tb_1 = null;
		$this->tb_2 = null;
		$this->key_1 = null;
		$this->key_2 = null;
		break;
		
		case "page":		
		$this->page = null;
		$this->perpage = null;
		$this->first = null;
		$this->last = null;	
		break;
		}
		}
     
	    //connector
        protected function connect_db() //make db_connection accessible only via __constructor
        { 
            $this->connect = mysql_connect(DB_HOST,DB_USER,DB_PASS); 
            $m = mysql_select_db(DATABASE,$this->connect); 
        }

		//close db 
        protected function close_db() 
        { 
         mysql_close($this->connect); 
        } 
		
		//security 
		protected function escape($sql) //mysql_real_escape_string is suppoesed to be more secure than addslashes
		{
		return mysql_real_escape_string($sql);
		}
				     
		//memory
		protected function free_memory($query)
		{
		return mysql_free_result($query);			
		}
		
        //basic query method 
        protected function query_db($query) //do not let users run query directly. This restriction can be circumvent by "dark magic". 
        {   
			return mysql_query($query, $this->connect);
        } 

		//get number of rows 
        public function num_rows($query) 
        { 
			return mysql_num_rows($query);
        } 
                          
        //fetch methods 
        protected function fetch_db_row($fetched) //returns array of 0=>[value0], 1=>[value1], used in fetch_table
        { 
            return mysql_fetch_row($fetched);  
        } 
		
		protected function fetch_db_assoc($fetched) //returns array of key0=>value0, key1=>value1, used in fetch_xml
        { 
            return mysql_fetch_assoc($fetched);  
        } 
         
        protected function fetch_db_array($fetched) //returns array of 0=>(key0=>value0)
        { 
            return mysql_fetch_array($fetched);  
        }
        
		public function fetch_handle() // returns safe handle
		{
			$query = $this->selectBuild();
			return $this->query_db($query);		
		}
        
		public function fetch_db_object($fetched) //returns results as objects. 
        { 
            return mysql_fetch_object($fetched);  
        }               	
		
	
		//Part 2: query builders    
		
		//for multitables
		public function addTable($table) //if no table is set, $this->addTable("tablename") returns the same result with $this->table = "tablename"
		{
		$this->table = (isset($this->table) ? $this->table.", ".$table : $table);
		}
		
		//add fields (default *)
		public function addColumn($field)
		{
		$this->selectfield = ($this->selectfield == "*" ? $field: $this->selectfield.", ".$field);
		}
		
		//count
		public function addCount($count,$sum=null)
		{
		$field = (isset($sum) ? "count($count) as $sum" : "count($count)" );
		$this->addColumn($field);		
		}
		//build where clause
		protected function addWhere($clause, $op="AND") //
		{
		$op = strtoupper($op);
        $this->where = (isset($this->where) ? $this->where." ".$op." ".$clause : $clause);
		}
		
		protected function buildWhere()
		{
		$where = (isset($this->where) ? " WHERE ".$this->where : null);
		return $where;
		}		
		
		//set limits
		protected function getPage()
		{
		$perpage = (isset($this->perpage) ? $this->perpage : "10");
		$page = $this->page;
		$this->first = ($page -1) * $perpage;
		$this->last = ($page * $perpage);
		}
		
		//basic select query engine that builds everything else
		protected function selectBuild()
		{
		$table = $this->table;	
		$select = $this->selectfield;
		$orderby = (isset($this->orderby) ? " ORDER BY ".$this->orderby." ".$this->order : null);
		$limit = null;
		if (isset($this->page)) {
			$this->getPage();		
			$limit = " LIMIT ".$this->first.",".$this->last;
		}		
		$where = $this->buildWhere();
		$this->regen();
		$sql = "SELECT ".$select." FROM ".$table.$where.$orderby.$limit;			
		return $sql;
		}
				
		public function selectDistinct($field)
		{
		$this->selectfield = "DISTINCT ".$field;
		}
	
		//matching key query builders (left join)
		public function unmatching_keys()
		{
		$this->selectfield = $this->tb_1.".*";
		$this->table = "`".$this->tb_1."` LEFT JOIN `".$this->tb_2."` ON ".$this->tb_1.".".$this->key_1." = ".$this->tb_2.".".$this->key_2;
		$this->addWhere($this->tb_2.".".$this->key_2." IS NULL");
		}   

		public function matching_keys()
		{
		$this->selectfield = $this->tb_1.".*";
		$this->table = "`".$this->tb_1."` LEFT JOIN `".$this->tb_2."` ON ".$this->tb_1.".".$this->key_1." = ".$this->tb_2.".".$this->key_2;
		$this->addWhere($this->tb_2.".".$this->key_2." IS NOT NULL");
		}		
				
		//subqueries
		public function subQuery($field, $operator = "LIKE")
		{
		$sql = $this->selectBuild();
        $subsql = $field." ".$operator." (".$sql.")";
		$this->addWhere($subsql, "AND");
		}

		//part 3: table, xml, rss output from select query. 
        //show in table		
		public function fetch_table()
		{
		$query = $this->selectBuild();
		$run = $this->query_db($query);
		$num = mysql_num_fields($run);
		$colspan = $num +1;
		$result = "<table ".$this->style.">";
		$result .= "<tr><td colspan = '".$colspan."'><center><strong>".$this->output_title." rows:".$this->num_rows($run);
		$result .= "</strong></center></td></tr>";
		$result .= "<tr>";
		for ($i = 0; $i < $num; $i++)		{
		$fieldname = mysql_field_name($run, $i);
		$result .= "<td>".$fieldname."</td>";	
		}
		$result .= "</tr>";
		
			while ($table = $this->fetch_db_row($run))		{
			$result .= "<tr>";
				foreach ($table as $data)		{
				$result .= "<td>".stripslashes($data)."</td>";
				}
			$result .= "</tr>";
			}
			
		$result .= "</table>";
		
		echo $result;		
		}
		
		public function fetch_xml()
		{
		$result = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
		$result .= "<".$this->output_title.">\n";
		$query = $this->selectBuild();
		$run = $this->query_db($query);
			while ($xml = $this->fetch_db_assoc($run)) {
				$result .= "\t<".$item.">\n";
				foreach ($xml as $key => $value) {
					$result .= "\t\t<".$key.">\n\t\t\t<![CDATA[".$value."]]>\n\t\t</".$key.">\n";
					}
				$result .= "\t</".$item.">\n";
				}
				
		$result .= "</".$this->output_title.">";
		header("Content-type: text/xml");
		echo $result;		
		}		
		
				
		//part 4: insert and update
    
		//add fields-value pair to array for the purpose of insert or update operation
		public function addField($key, $value, $type = "string")
		{
		$this->fields[$key] = array("type" => $type, "value" => $value);
		}
		  
        //magic insert and update function
		public function insertQuery()
		{
		if ($this->table =="")
		return "";
		if (count($this->fields) ==0)
		return "";
		
		$_key = "";
		$_val = "";
		$_up = "";
		$_where = "";
		reset($this->fields);//php command "reset" would rewind array serial to 0
		
		while (list($key, $field) = each($this->fields)){
			$val = $field["value"];			
			$alt = $key;
			
			if (is_string($val))
			$val = $this->escape($val);
			
			if ($field["type"] !="update")
			$val = "'$val'";
						
			if ($field["type"] =="update")
			$alt = $this->update;
						
			$_key .= ($_key != "" ? ", ":""). "$key";
			$_val .= ($_val != "" ? ", ":""). "$val";
			
			if ($field["type"] == "key"){
			$_where .= ($_where !="" ? "AND ":""). "$key = $val";
			}
			else {			
			$_up .= ($_up != ""? ", ":"")."$alt = $val";
			}
		}
		
		switch($this->op) {
			case "insert":
			$sql = "INSERT INTO ".$this->table." (".$_key.") VALUES (".$_val.")";
			break;
				
			case "update":
			$this->addWhere($_where);
			$this->buildWhere();
			$sql = "UPDATE ".$this->table." SET ".$_up." ".$where;
			break;
		
			case "insert_ignore":
			$sql = "INSERT IGNORE INTO ".$this->table." (".$_key.") VALUES (".$_val.")";
			break;
		
			case "insert_update":
			$sql = "INSERT INTO ".$this->table." (".$_key.") VALUES (".$_val.") ON DUPLICATE KEY UPDATE ".$_up;
			break;		
			}
		$this->regen("insert");
		$this->query_db($sql);
		}	

		//part 5: other operations
		public function delete()
		{
		$sql = "DELETE FROM ".$this->table.$this->buildWhere;
		$this->regen();
		$this->query_db($sql);
		}
}
