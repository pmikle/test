<?
	class MainClass {
	
		private $conn = NULL;
		
		public function query($query) {
			
			$result = pg_query($this->conn, $query);
			if (!$result) {
				return NULL;
			} else {
				return $result;
			}
		
		}
		
		public function GSV ($query){
		
			$result = pg_query($this->conn, $query);
			if (!$result) {
				return NULL;
			} else {
				$result = pg_fetch_array($result);
				return $result[0];
			}		
		}
		
		private function dbconnect() {
		
			require_once("configuration.php");
			return $dbconn3 = pg_connect("host={$host} dbname={$dbname} user={$user} password={$password}");
			
		}
		
		private function update_rss() {
			
			$r = $this->query("select chid, name, url, link from rss_channels");
			while ($row = pg_fetch_assoc($r)) {

				$url = $row['url'];
				$chid = $row['chid'];
				
				$xmlstring = file_get_contents($url);
				$xml = simplexml_load_string($xmlstring);
			
				foreach ($xml->channel->item as $key => $val) {
					
					if ( !$this->GSV("select nid from news where chid = {$row[chid]} and \"pubDate\" = '{$val->pubDate}' and title = '{$val->title}'") ) {
						$sql = "INSERT INTO news 
						(chid, title, link, description, \"pubDate\")
						VALUES ({$row[chid]}, '{$val->title}', '{$val->link}', '{$val->description}', '{$val->pubDate}')";
						$this->query($sql);
					}
				}
			}
		}
		
		public function show_rss ($str="") {
			$s = $this->query("select * from news as n join rss_channels as c using(chid){$str}");
			?><table class="table1 tablesorter">
				<thead>
					<tr>
						<th>№</th>
						<th>Канал</th>
						<th>Новость</th>
						<th>Дата</th>
					</tr>
				</thead>
				<tbody>
			<?
			while ($r = pg_fetch_assoc($s)) {
				?>
					<tr>
						<td> </td>
						<td><?=$r['name']?></td>
						<td><?=$r['title']?></td>
						<td><?=$r['pubDate']?></td>
					<tr>
				<?

			}
			?>	</tbody>
			</table><?
		}
		
		public function show_filter_by_channel() {
			$s = $this->query("select chid, name from rss_channels");
			?><select name="channel">
				<option value=""></option><?
			while ($r = pg_fetch_assoc($s)) {
				?><option value="<?=$r['chid']?>"><?=$r['name']?></option><?
			}
			?></select><?
		}
		
		public function show_filter_by_date() {
			?>
			<div id="date_range"></div>
			<input name="startDate">
			<input name="endDate">
			<span id="dater">выбрать</span><?
		}
		
		public function security_query($str) {
		
			/* Здесь можно много чего придумать */
			return $str =  htmlspecialchars($str);
			
		}
		
		function __construct() {
			
			$this->conn = $this->dbconnect();
			
			//$this->update_rss();
			
			

		}
	
	
	
	
	}