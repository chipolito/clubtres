<?php
	class Leaguemodel {
		public function __construct() {
	        require_once '../dbConnection.php';
	    }

		public function createLeague($data) {
			$pdo = new Conexion();

			if( $data['idLeague'] == 0 ){
				$cmd = '
					INSERT INTO league
						(name, sport, register_date, status, user_id)
					VALUES
						(:name, :sport, now(), 1, :user_id)
				';

				$parametros = array(
					':name'		=> $data['name'],
					':sport'	=> $data['sport'],
					':user_id'  => $data['user_id']
				);
				
				try {
					$sql = $pdo->prepare($cmd);
					$sql->execute($parametros);

					return [TRUE, $pdo->lastInsertId()];
				} catch (PDOException $e) {
			        return [FALSE, 0];
			    }
			} else {
				$cmd = '
					UPDATE league SET name =:name, sport =:sport, status =:status WHERE id =:id
				';

				$parametros = array(
					':id'		=> $data['idLeague'],
					':name'		=> $data['name'],
					':sport'	=> $data['sport'],
					':status'	=> $data['chkActive']
				);

				$sql = $pdo->prepare($cmd);
				$sql->execute($parametros);

				return [TRUE, $data['idLeague']];
			}			
		}

		public function updateImage($leagueId, $image){
			$pdo = new Conexion();
			$cmd = 'UPDATE league SET image =:image WHERE id =:leagueId';

			$parametros = array(
				':image' => $image,
				':leagueId' => $leagueId			
			);

			$sql = $pdo->prepare($cmd);
			$sql->execute($parametros);

			return TRUE;
		}

		public function getLeagueId($leagueId) {
			$pdo = new Conexion();
			$cmd = 'SELECT id, name, sport, image, status FROM league WHERE id =:leagueId';

			$parametros = array(
				':leagueId' => $leagueId
			);

			$sql = $pdo->prepare($cmd);
			$sql->execute($parametros);
			$sql->setFetchMode(PDO::FETCH_OBJ);

			return $sql->fetch();
		}

		public function getLeague( $user_id ) {
			$pdo = new Conexion();
			$cmd = '
				SELECT id, name, sport, register_date, status, image, 1 AS type 
				FROM league 
				WHERE status = 1 AND user_id =:user_id

				UNION 

				SELECT id, name, sport, register_date, status, image, 2 AS type 
				FROM league 
				WHERE status = 1 
				AND id IN(SELECT league_id FROM team_league WHERE status = 1 AND team_id IN ( SELECT team_id FROM user_team WHERE user_id =:user_id AND status = 1))
			';

			$parametros = array(
				':user_id' => $user_id
			);

			$sql = $pdo->prepare($cmd);
			$sql->execute($parametros);

			return $sql->fetchAll(PDO::FETCH_ASSOC);
		}

		public function deleteLeague($leagueId) {
			$pdo = new Conexion();
			$cmd = 'UPDATE league SET status = 0 WHERE id =:leagueId';

			$parametros = array(
				':leagueId' => $leagueId
			);

			$sql = $pdo->prepare($cmd);
			$sql->execute($parametros);

			return TRUE;
		}
	}