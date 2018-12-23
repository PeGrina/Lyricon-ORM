<?php
class L{
	// Переменная для подключения к бд
	static private $connection;
	// Подключаемся к БД
	public function setup($host,$dbname,$login,$pass){
		 self::$connection = mysqli_connect($host,$login,$pass,$dbname);
	}
	// Ишем в таблице по условию ($condition) 
	public function find($table,$condition){
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
	public function readAll($table){
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
		$insert = "INSERT INTO `".$variable['table']."`(";
		foreach ($variable as $key => $value) {
			if(trim($key) != 'table'){				
					$insert = $insert.''.$key.',';
			}
		}
		$insert = substr($insert,0,-1);
		$insert = $insert.") VALUES(";
		foreach ($variable as $key => $value) {
			if(trim($key) != 'table'){	
				if(trim(gettype($value)) == 'string'){
						$insert = $insert.'\''.$value.'\',';
					}else{
						$insert = $insert.''.$value.',';
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
				echo $query;
				$q = mysqli_query(self::$connection,$query);
				$q = mysqli_query(self::$connection,$insert);

		
	}


}
?>