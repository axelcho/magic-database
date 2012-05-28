<?php
	class Magic_Debug extends Magic
		public function drop($drop)
		{
		$sql = "DROP TABLE ".$drop;
		$this->regen();
		$this->query_db($sql);
		}
				
		public function truncate($truncate)
		{
		$sql = "TRUNCATE TABLE ".$truncate;
		$queried = mysql_query($sql,$this->connect) or $this->deleteall($truncate);
        return($queried);
		}

		protected function deleteall($table)
		{
		$this->where = null;
		$this->table = $table;
		$this->delete();		
		}	
		
		//To debug query building: 
		public function selectDebug()
		{
	    echo $this->selectBuild();
		}
				
		//raw methods in case categorized query builds does not work. Use these functions only when absolutely necessary
		public function raw_query($query) 
        {                
            $q = mysql_query($query,$this->connect) or print mysql_error(); 
            return($q); 
        }     
		
		public function raw_where($where)
		{
		$this->where = $where;
		}	