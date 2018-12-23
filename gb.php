<?php
class L{
	// Переменная для подключения к бд
	static private $connection;
	static private $arr;
	static private $freeze = false;

	static private function describe($table){
		$q = mysqli_query(self::$connection,"DESCRIBE `$table`");
		$qu = mysqli_query(self::$connection,"SELECT COUNT(1) FROM `$table`");
		$count = mysqli_fetch_array($qu);
		self::$arr = [];
		for($i = 0;$i < 250;$i++){
			self::$arr[$i] = mysqli_fetch_array($q);
		}
	}
	public function freeze(){
		self::$freeze = true;
	}
	public function unfreeze(){
		self::$freeze = false;
	}
	// Подключаемся к БД
	public function setup($host,$dbname,$login,$pass){
		 self::$connection = mysqli_connect($host,$login,$pass,$dbname);
	}
	// Ишем в таблице по условию ($condition) 
	public function read($table,$condition){
		
			$result = mysqli_query(self::$connection,"SELECT *  FROM  `$table` WHERE  $condition ");
			return mysqli_fetch_array($result);
	}
	// Удаляем по условию
	public function del($table,$condition){
		$del = "DELETE FROM `$table` WHERE $condition";
		$result = mysqli_query(self::$connection,$del);
	}
	// Очищаем таблицу полностью
	public function clear($table){
		$clr = "TRUNCATE TABLE `$table`";
		$result = mysqli_query(self::$connection,$clr);
	}
	// Выводит все данные из таблицы в виде двумерного массива
	public function readAll($table,$limit = false){
		$arr = array();
		$q = mysqli_query(self::$connection,"SELECT COUNT(1) FROM `$table`");
		$count = mysqli_fetch_array($q);
		for($i = 1;$i < $count[0] + 1;$i++){
			$result = mysqli_query(self::$connection,"SELECT *  FROM  `$table` WHERE id = $i");
			$arr['id-'.$i] = mysqli_fetch_array($result);

		}
		return $arr;
	}
	// Создаёт массив с которым будет работать update
	public function load($table,$id){
		$variable = array('table' => $table,'id' => (int)$id);
		return $variable;
	}
	// Обновляет записи в бд по id
	public function update($variable){
		$update = "
UPDATE 
	`".$variable['table']."`  
SET 
	";
		foreach ($variable as $key => $value) {
			if(trim($key) != 'table' && trim($key) != 'id'){
				if(trim(gettype($value)) == 'string'){
						$update = $update.'`'.$key.'` = \''.$value.'\',';
					}else{
						$update = $update.'`'.$key.'` = '.$value.',';
					}
				}				
		}
		$update = substr($update,0,-1);
		$update = $update." 
WHERE 
	id = ".$variable['id']."";
		$q = mysqli_query(self::$connection,$update);
	}
	// Создаёт массив для store
	public function dispense($table){
		$arr = array('table' => $table);
		return $arr;
	}
	// Создаёт таблицу если её нет, Закидывает записи в таблицу 
	public function store($variable){
		if(self::$freeze == false){
			self::describe($variable['table']);
			$arr = [];
			$arr2 = [];
			$arr3 = [];
			foreach($variable as $key => $value){
				if(trim($key) != 'table'){
					$arr[$key] = $value;
				}
			}
			foreach (self::$arr as $key => $value) {
				if(trim(gettype($value)) == 'array'){
					foreach (self::$arr[$key] as $key => $value) {
						if($key == 'Field'){
							array_push($arr2,$value);
						}
					}
				}
			}
			foreach ($arr as $key => $value) {			
					if(in_array($key, $arr2)){

					}else{
						$arr3[$key] = $value;
					};
			}
			foreach ($arr3 as $key => $value) {
			if(trim($key) != 'table'){
				if(trim(gettype($value)) == 'string'){
					$q  = mysqli_query(self::$connection,"ALTER TABLE `".$variable['table']."` ADD `".$key."` TEXT(65535) AFTER `".array_pop($arr2)."`");
				}
				if(gettype($value) == 'integer'){
					$q  = mysqli_query(self::$connection,"ALTER TABLE `".$variable['table']."` ADD `".$key."` INT(128) AFTER `".array_pop($arr2)."`");
				}
				if(gettype($value) == 'double'||gettype($value) == 'float'){
					$q  = mysqli_query(self::$connection,"ALTER TABLE `".$variable['table']."` ADD `".$key."` FLOAT(53) AFTER `".array_pop($arr2)."`");
				};
			}
		}

		}else{
			
			self::describe($variable['table']);
			$arr = [];
			$arr2 = [];
			$arr3 = [];
			foreach($variable as $key => $value){
				if(trim($key) != 'table'){
					$arr[$key] = $value;
				}
			}
			foreach (self::$arr as $key => $value) {
				if(trim(gettype($value)) == 'array'){
					foreach (self::$arr[$key] as $key => $value) {
						if($key == 'Field'){
							array_push($arr2,$value);
						}
					}
				}
			}
			foreach ($arr as $key => $value) {			
					if(in_array($key, $arr2)){

					}else{
						$arr3[$key] = $value;
					};
			}
		}

		$insert = "INSERT INTO `".$variable['table']."`(";
		foreach ($variable as $key => $value) {
			if(trim($key) != 'table'){		
				if(!array_key_exists($key,$arr3)){
						
					$insert = $insert.''.$key.',';
				}
			}
		}
		$insert = substr($insert,0,-1);
		$insert = $insert.") VALUES(";
		foreach ($variable as $key => $value) {
			if(trim($key) != 'table'){	
				if(!array_key_exists($key,$arr3)){
						if(trim(gettype($value)) == 'string'){
							$insert = $insert.'\''.$value.'\',';
						}else{
							$insert = $insert.''.$value.',';
						}
					}
				}
		}
		$insert = substr($insert,0,-1);
		$insert = $insert.')';
		foreach ($variable as $key => $value) {
			if(trim($key) != 'table'){
				if(trim(gettype($value)) == 'string'&&strlen($value) < 512){
					$variable[$key] = $key.' TEXT(512),
';
				}elseif(trim(gettype($value)) == 'string'&&strlen($value) > 512&&strlen($value) <= 1024){
					$variable[$key] = $key.' TEXT(1024),
';
				}elseif(trim(gettype($value)) == 'string'&&strlen($value) > 1024&&strlen($value) <= 2048){
					$variable[$key] = $key.' TEXT(2048),
';
				}elseif(trim(gettype($value)) == 'string'&&strlen($value) > 2048&&strlen($value) <= 4096){
					$variable[$key] = $key.' TEXT(4096),
';
				}elseif(trim(gettype($value)) == 'string'&&strlen($value) > 4096){
					$variable[$key] = $key.' TEXT(65535),
';
				}
				if(gettype($value) == 'integer'){
					$variable[$key] = $key.' INT(128),
';
				}
				if(gettype($value) == 'double'||gettype($value) == 'float'){
					$variable[$key] = $key.' FLOAT(53),
';
				}
			}

		};
				$query = "
CREATE TABLE ".$variable['table']."(
id INT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
";
				foreach ($variable as $key => $value) {
					if(trim($key) != 'table'){
							$query = $query.''.$value;
						
					}

				};
				$query = substr($query,0,-3);
				$query = $query.'
)';
				
				$q = mysqli_query(self::$connection,$query);
				$q = mysqli_query(self::$connection,$insert);

		
	}
	public function close(){
		mysqli_close(self::$connection);
	}

}
?>