<?php
class Magic_Transform extends Magic
{
public function convert_tables($source_, $target_ , $table)
{
$target = str_replace($source_, $target_, $table);

$_source_query = "SELECT * from ".$table;
$source_query = $this->query_db($_source_query);

while ($source_line = $this->fetch_db_assoc($source_query)) {

	$this->table = $target;
	
	$this->op = "insert_ignore";

	foreach ($source_line AS $field=>$value) {
		$convert = iconv('euc-kr', 'utf-8', $value);
		$this->addField($field, $convert);
		}		
	$this->insertQuery();
	}
}

public function show_tables($prefix = null)
{
$query = "SHOW TABLES";
$query = (!isset($prefix) ? $query : $query." LIKE '".$prefix."%'");
$show = $this->query_db($query);

while ($tables = $this->fetch_db_object($show)) {
	foreach ($tables as $table=>$name){
	echo $name."<br/>";	
	}
	}

}

public function copy_structure($prefix,$convert,$overwrite)
{
$query = "SHOW TABLES";
$query = (!isset($prefix) ? $query : $query." LIKE '".$prefix."%'");
$show = $this->query_db($query);

while ($tables = $this->fetch_db_row($show)) {
	
	$table = $tables[0];
		$show_create = "SHOW CREATE TABLE ".$table;		
		$_show_create = $this->query_db($show_create);
			while ($_create = $this->fetch_db_row($_show_create))
			{
			$converted = str_replace($prefix, $convert, $table);
			if (isset($overwrite)) {
				$delete = "DROP TABLE ".$converted;
				$this->query_db($delete);
				}
			
			$create = $_create[1];
			$create_replace = str_replace($table, $converted, $_create[1]);
			$create_replace = str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $create_replace);
			$create_query = str_replace('latin1', 'utf8',$create_replace);
			$this->query_db($create_query);					
			}
	}
}




public function transform_tables($prefix, $convert, $verbose, $overwrite)
{
$this->copy_structure($prefix,$convert,$overwrite);	
$query = "SHOW TABLES";
$query = (!isset($prefix) ? $query : $query." LIKE '".$prefix."%'");
$show = $this->query_db($query);

while ($tables = $this->fetch_db_row($show)) {
	
	$table = $tables[0];
	$this->convert_tables($prefix, $convert, $table, $overwrite);
		if (isset ($verbose)){
			echo $table." converted.<br>";
		}
	}
}

}