<?php
class Magic_Slimkitchen extends Magic
{
public function convert_tables($source_ = null, $target_ = "convert", $table)
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
}