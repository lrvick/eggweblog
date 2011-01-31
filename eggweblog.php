<?
// EggWebLog v0.5
// parses a standard log directory created by an eggdrop bot then
// displays it via the web for browsing by date or search.

$logDir = "/path/to/eggdrop/logs/"; // complete path, including trailing slash
$logHead = "ChannelName.log.";      // inital portion of logs file name (before the DDMonthYear
$logTail = "";                      // appended portion (such as a file type)
$resultsCap = 1000;                 // Maximum results. Searching for a nickname can return a _lot_

$jumpto = $_GET['jumpto'];
$highlight = $_GET['highlight'];
$date = $_GET['date'];
if(strlen($date)==0){
	$date = date("dMY");
}

$search = $_GET['search'];
$resultsStart = $_GET['start'];
if(strlen($resultsStart)==0)$resultsStart =0;
$searchRes = "0";

echo "<form style='float:left;' action='?c=logs'>";
echo "<input type='hidden' name='c' value='logs'>";

$dir_handle = @opendir($logDir) or die("Unable to open $logDir");
$dirList = array();
while ($file = readdir($dir_handle))
	if($file!="." && $file!=".." && $file!="idlerpg"){
		$dirList[$file]=filectime("$logDir$file");
	}
closedir($dir_handle);

asort($dirList);

$dirList = array_keys($dirList);

echo "Date: <select name='date'>";
foreach(array_reverse($dirList) as $value) {
	$value = str_replace(array($logTail, $logHead),"",$value);
	$selected = ($date === $str) ? "selected" : "";
        echo "<option $selected>$value</option>";
}
echo "</select>";
echo " Highlight: <input name='highlight' size='10'></input>";
echo "&#x0020;<input type='submit' value='Go' />";
echo "</form>";
echo "<form style='float:left;padding-left:20%;'>";
echo "<input type='hidden' name='c' value='logs'>";
echo "<input name='search' size='15'>";
echo "<input type='submit' value='Search' />";
 echo "</form>";
echo "<br/>";
echo "<hr style='clear:both;'/>";

if (strlen($search)!=0){
	if(strlen($search)<3)//tee hee it's a heart
	{
		echo "Sorry.<string>\"$search\"</strong> was not long enough. =(";
		return;
	}

	exec("grep -n -i -m $resultsCap  \"$search\" $logDIr, $results);
	$search = htmlentities($search);
	if(strlen($highlight)==0) $highlight = $search;
	if (count($results)) {
		$searchRes = count($results);
		$i=0;
		while ($i < $resultsCap && ($i+$resultsStart < $searchRes)) {
			$results[$i+$resultsStart] = replaceFirst("$logDir$logHead","",$results[$i+$resultsStart]);
			$results[$i+$resultsStart] = replaceFirst("$logTail","",$results[$i+$resultsStart]);
	                $contents = "$contents ".$results[$i+$resultsStart]." \n";
	             $i++;
		}
	} else {
		// no hits
		echo "Sorry. Search on <strong>$search</strong> returned no results.\n";
		return;
	}
}else{
	// $searchRes,$resultsStart ,$resultsCap
// # returned  starting #    # per page
	$filename = "$logDir$logHead$date$logTail";
	$results = file($filename);
	$searchRes = count($results);
	while ($i < $resultsCap && ($i+$resultsStart < $searchRes)) {
                $contents = "$contents ".$results[$i+$resultsStart];
	       $i++;
	}

}

$contents = htmlentities($contents);

$i = 0;
$lines =0;
do{
	$contents = replaceFirst("&gt;","</b>&gt;",$contents,$i);
	$contents = replaceFirst("&lt;","&lt;<b>",$contents,$i);
	$i++;
}while(false!==($i=strpos($contents,"\n",$i)));

if(strlen($highlight)>3){
$i = 0;
$partA ="<a style='color:red;'>";
$partB ="</a>";
while(false!==($i=stripos($contents,"$highlight",$i))){
$contents = 
	 substr($contents,0,$i)
	.$partA
	.substr($contents,$i,strlen($highlight))
	.$partB
	.substr($contents,$i+strlen($highlight));
	$i+=strlen("$partA$highlight$partB");
}}

$contents = split("\n",$contents);
echo "<div id='PILog_Results' >";
echo "<div id='PILog_Results_Nav'>";
printSearchLinks();
echo "</div>";
$i = 0;
$numLines = count($contents);

$isSearchPage = 0!=strlen($search);
while($i<$numLines){
	if($isSearchPage){
		$parts = split(":",$contents[$i],3);
                $parts[0] = str_replace(" ","",$parts[0]);
		echo "<a href=\"http://".$PHP_SELF."&date=$parts[0]&highlight=$highlight";
                if ($parts[1] > $resultsCap){
                  //$start= round($parts[1], -3);
                  $start = $parts[1] - ($parts[1] % 1000);
                  $jt= $parts[1] - $start - 1;
                  echo "&start=$start&jumpto=$jt#jt";
                } else {
                  $jt=$parts[1] -1;
                  echo "&jumpto=$jt#jt";
                }
                echo " \">$parts[0]</a>$parts[2]<br/>\n";
	}else if ($i == $jumpto){
                echo "<a name='jt' id='jumpto'>$contents[$i]</a><br/>";
	}else{
                echo $contents[$i]."<br/>";
        }
$i++;
}

echo "<div id='PILog_Results_Nav'>";
printSearchLinks();
echo "</div>";
echo "</div>";

function printSearchLinks(){
	global $searchRes,$resultsStart ,$resultsCap,$search,$highlight,$date;
	$numLinks = $searchRes/$resultsCap;
		if($numLinks<1) return;
		$i = 0;
		while($i<$numLinks){
                        echo "| <a ";
                        if ($resultsStart == ($i*$resultsCap)){
                          echo "id='PILog_Results_Nav_Current' ";
                        }
			echo "href=\"http://".$PHP_SELF."&search=$search&start="
				.($i*$resultsCap)
				."&date=$date&highlight=$highlight\">"
				.($i*$resultsCap+1)."-"
				.min($searchRes,(($i+1)*$resultsCap))."</a> ";
			$i++;
		}
	echo "|<br>";
}
function replaceFirst($search,$target,$data,$start=0){
	if($search=="") return $data;
	$loc = strpos($data, $search,$start);
	if ($loc === false)
		return $data;
	else
		return substr($data, 0, $loc).$target.substr($data, $loc+strlen($search));
}
?>
