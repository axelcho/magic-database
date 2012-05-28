<?php
class Magic_Rss extends Magic 
{
//rss variables		
	public $output_item_title = null;
	public $output_item_description = null; 
	public $output_item_guid = null;
	public $output_item_link = null;
	public $output_item_pubdate = null;
	public $output_title = "Result";
	public $output_description = "Description";
	public $output_link = null;
	public $output_lastbuilddate = null;
	public $output_pubdate = null;

	
	public function regen()
	{
	$this->table =null;
	$this->selectfield = "*";
	$this->where = null;
	$this->orderby = null;
	$this->order = "DESC";
	$this->output_item_title = null;
	$this->output_item_description = null;
	$this->output_item_guid = null;
	$this->output_item_link = null;
	$this->output_item_pubdate = null;
	$this->output_title = "Result";
	$this->output_description = "Description";
	$this->output_link = null;
	$this->output_lastbuilddate = null;
	$this->output_pubdate = null;	
	}
	
	public function fetch_rss()
	{
	if (!isset ($this->output_item_guid))
	$this->output_item_guid = "'http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."'";
	
	$this->addColumn($this->output_item_guid." as guid");

	if (!isset ($this->output_item_title))
	die("Title is required for each item.");
	
	$this->addColumn($this->output_item_title." as title");

	if (!isset ($this->output_item_description))
	die("Description is required for each item.");

	$this->addColumn($this->output_item_description." as description");

	if (!isset ($this->output_item_link))
	$this->output_item_link = "'http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."'";

	$this->addColumn($this->output_item_link." as link");

	if (!isset ($this->output_item_pubdate))
	$this->output_item_pubdate = "'".date(DATE_RFC822)."'";		

	$this->addColumn($this->output_item_pubdate." as pubDate");
						
	if (!isset($this->output_link))
	$this->output_link = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];

	if (!isset($this->rss_lastbuilddate))
	$this->rss_lastbuilddate = date(DATE_RFC822);

	if (!isset($this->output_pubdate))
	$this->output_pubdate = date(DATE_RFC822);
		
	$result = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n<rss version = \"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n<channel>\n";
	$result .= "\t<title>".$this->output_title."</title>\n\t<description>".$this->output_description."</description>\n";
	$result .= "\t<link>".$this->output_link."</link>\n";
	$result .= "\t<lastBuildDate>".$this->rss_lastbuilddate."</lastBuildDate>\n\t<pubDate>".$this->output_pubdate."</pubDate>\n";
	$result .= "<atom:link href=\"http://".$_SERVER['SERVER_NAME']."/".$this->output_title.".rss\" rel=\"self\" type= \"application/rss+xml\" />\n";
		
	$query = $this->selectBuild();
	$run = $this->query_db($query);
		while ($xml = $this->fetch_db_assoc($run)) {
			$result .= "\t<item>\n";
			foreach ($xml as $key => $value) {
				$result .= "\t\t<".$key.">\n\t\t\t<![CDATA[".$value."]]>\n\t\t</".$key.">\n";
				}
			$result .= "\t</item>\n";
			}
	$this->free_memory($run);	
	$result .= "</channel>\n</rss>";
		
	//write the feed into a file
	$filename = $_SERVER['DOCUMENT_ROOT']."/".$this->output_title.".rss";
	$handle = fopen($filename, "w");
	fwrite($handle, $result);
	fclose($handle);
		
	$this->regen();
		
	//show the rss feed to screen
	header("Content-type: text/xml");
	echo $result;
}
}