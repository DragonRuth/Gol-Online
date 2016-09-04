<?php
	namespace App\Classes\Game;

	class Position
	{
		public $x ;
		public $y ;

		public function __construct($xInput, $yInput) {
			if (!empty($xInput) && !empty($yInput)) {
				$this->x = $xInput;
				$this->y = $yInput;
			} else {
				$this->x = 0;
				$this->y = 0;
			}
		}

		public function setPosition($xInput, $yInput) {
			$this->x = $xInput;
			$this->y = $yInput;
		}
	}

	class Teams 
	{
		public $team;
		public $score;

		public function __construct() {
			$this->team = 0;
			$this->score = 0;
		}

		public function setTeam($teamNumber) {
			$this->team = $teamNumber;
			return NULL;
		}
	}

	class Point
	{
		public $added;
		public $position;
		public $teams;
		public $teamNumber;
		public $team;
        public $zero;

		public function __construct() {
			$this->added = false;
			$this->position = new Position(0, 0);
			$this->teamNumber = 0;
			$this->team = 0;
            $this->zero = 0;
            $this->teams = array();
		}

		public function setPoint($x, $y, $teamNum) {
			$this->position->x = $x;
			$this->position->y = $y;
			$this->teamNumber = $teamNum;
			$this->teams = array();
			if ($this->teamNumber > 1) {
				for ( $i = 0; $i < $this->teamNumber; $i++) {
					$tm = new Teams();
					$tm->setTeam($i);
					array_push($this->teams, $tm);
				}
			} else {
                $tm = new Teams();
                $tm->setTeam(1);
                array_push($this->teams, $tm);
			}
		}

		public function incScore($teamNum) {
		    if ($this->teamNumber >= $teamNum) {
                $this->teams[$teamNum-1]->score++;
            }
        }

        public function cleanTeams() {
            for ($i = 0; $i < $this->teamNumber; $i++) {
                $this->teams[$i]->score = 0;
            }
            $this->zero = 0;
        }

        public function zeroPoint($number) {
            for ($i = 0; $i < $this->teamNumber; $i++) {
                $this->teams[$i]->score = $number;
            }
            $this->zero = $number;
        }

        public function kill() {
            $this->team = 0;
            $this->cleanTeams();
        }

        public function alive($teamNum) {
            $this->team = $teamNum;
            $this->cleanTeams();
        }

        public function nothing() {
            $this->cleanTeams();
        }

        public function add() {
            $this->added = true;
        }

        public function disadd() {
            $this->added = false;
        }

        public function add_q() {
            return $this->added;
        }
	}


    class GOL {
        public $points;
        public $teamNumber;
        public $sizeX;
        public $sizeY;
        public $alivePoints;
        public $checkPoints;
        public $teams;

        public function __construct($sX, $sY, $teamNum)
        {
            if ($teamNum < 1) $this->teamNumber = 1;
            else $this->teamNumber = $teamNum;
            $this->sizeX = $sX;
            $this->sizeY = $sY;
            $this->points = array();
            //$pos = new Position(0, 0);
            for ( $i = 0; $i < $this->sizeY ; $i++) {
                $helpArray = array();
                for ( $j = 0; $j < $this->sizeX ; $j++) {
                    $point = new Point();
                    $point->setPoint($j, $i, $this->teamNumber); //Changed it here
                    array_push($helpArray, $point);
                }
                array_push($this->points, $helpArray);
            }

            $this->alivePoints = array();
            $this->checkPoints = array();
            $this->teams = array();
            for ($i = 0; $i < $this->teamNumber; $i++) {
                $teamHelp = new Teams();
                $teamHelp->setTeam($i + 1);
                array_push($this->teams, $teamHelp);
            }

        }

        public function incScore($teamNum, $number) {
            if ($teamNum <= $this->teamNumber)
                $this->teams[$teamNum - 1]->score += $number;
        }

        public function setPoints($input) {
            if (count($input) === ($this->sizeX * $this->sizeY + $this->teamNumber)) {
                $number = 0;
                $alivePointsArray = array();
                for ($j = 0; $j < $this->sizeX; $j++) {
                    for ($i = 0; $i < $this->sizeY; $i++) {
                        $helpElement = array_shift($input);
                        if ($helpElement[0] !== "z") {
                            if ($helpElement !== 0) {
                                array_push($alivePointsArray, $j);
                                array_push($alivePointsArray, $i);
                                array_push($alivePointsArray,  $helpElement);
                                $number++;
                            }
                        } elseif ($helpElement[1] == "-") $this->points[$i][$j]->zeroPoint(-1);
                          else  $this->points[$i][$j]->zeroPoint(1);
                    }
                }
                $this->addPoints($alivePointsArray, $number);
                for ($i = 0; $i < $this->teamNumber; $i++) {
                    $score = array_shift($input);
                    $this->teams[$i]->score = $score;
                }
            } else echo "Fail!!!";
        }

        public function addPoints($inputArray, $number) {
            for ($i = 0; $i < $number; $i++) {
                $this->points[$inputArray[$i*3+1]][$inputArray[$i*3]]->alive($inputArray[$i*3+2]);
                $pos = new Position($inputArray[$i*3], $inputArray[$i*3+1]);
                array_unshift($this->alivePoints, $pos);
            }
        }

        public function addPoint($x, $y) {
            $this->points[$y][$x]->add();
            $pos = new Position($x, $y);
            array_unshift($this->checkPoints, $pos);
        }

        public function culcPoint($pos) {
            $x = $pos->x;
            $y = $pos->y;
            $t = $this->points[$y][$x]->team;
            if ($x === 0) $xM1 = $this->sizeX - 1;
            else    $xM1 = $x - 1;
            if ($x === $this->sizeX - 1) $xP1 = 0;
            else    $xP1 = $x + 1;
             if ($y === 0) $yM1 = $this->sizeY - 1;
             else    $yM1 = $y - 1;
            if ($y === $this->sizeY - 1) $yP1 = 0;
            else    $yP1 = $y + 1;
            $this->points[$yM1][$xM1]->incScore($t);
            if (! $this->points[$yM1][$xM1]->add_q()) $this->addPoint($xM1, $yM1);
            $this->points[$yM1][$x]->incScore($t);
            if (! $this->points[$yM1][$x]->add_q()) $this->addPoint($x, $yM1);
            $this->points[$yM1][$xP1]->incScore($t);
            if (! $this->points[$yM1][$xP1]->add_q()) $this->addPoint($xP1, $yM1);
            $this->points[$y][$xM1]->incScore($t);
            if (! $this->points[$y][$xM1]->add_q()) $this->addPoint($xM1, $y);
            $this->points[$y][$xP1]->incScore($t);
            if (! $this->points[$y][$xP1]->add_q()) $this->addPoint($xP1, $y);
             $this->points[$yP1][$xM1]->incScore($t);
            if (! $this->points[$yP1][$xM1]->add_q()) $this->addPoint($xM1, $yP1);
            $this->points[$yP1][$x]->incScore($t);
            if (! $this->points[$yP1][$x]->add_q()) $this->addPoint($x, $yP1);
            $this->points[$yP1][$xP1]->incScore($t);
            if (! $this->points[$yP1][$xP1]->add_q()) $this->addPoint($xP1, $yP1);
        }

        public function culcPoints() {
            while (! empty($this->alivePoints)) {
                $pos = array_shift($this->alivePoints);
                if (! $this->points[$pos->y][$pos->x]->add_q()) $this->addPoint($pos->x, $pos->y);
                $this->culcPoint($pos);
            }
        }

        public function rules($pos) {
            if ($this->teamNumber > 1) {
                $max = $this->points[$pos->y][$pos->x]->teams[0]->score;
                $imax = 0;
                $kll = false;
                for ($i = 1; $i < $this->teamNumber; $i++) {
                    if ($this->points[$pos->y][$pos->x]->teams[$i]->score > $max) {
                        $max = $this->points[$pos->y][$pos->x]->teams[$i]->score;
                        $imax = $i;
                    }
                }
                if ($this->points[$pos->y][$pos->x]->team !== 0 && $this->points[$pos->y][$pos->x]->team !== ($imax + 1)) $alive = true;
                else $alive = false;
                for ($i = 0; $i < $this->teamNumber; $i++) {
                    if ($this->points[$pos->y][$pos->x]->teams[$i]->score === $max && $imax !== $i) {
                        $kll = true;
                    }
                }
                if ($kll) $this->points[$pos->y][$pos->x]->kill();
                else {
                    if ($alive) {
                        $score = $max;
                        if ($score == 3) {
                            $this->points[$pos->y][$pos->x]->alive($imax+1);
                            $this->incScore($imax+1, 2);
                        }
                        //elseif ($score == 2) $this->points[$pos->y][$pos->x]->nothing();
                        else $this->points[$pos->y][$pos->x]->kill();
                    } else {
                        $score = $max;
                        if ($score == 3) {
                            if ($this->points[$pos->y][$pos->x]->team === 0)
                                $this->incScore($imax+1, 1);
                            $this->points[$pos->y][$pos->x]->alive($imax+1);
                        }
                        elseif ($score == 2) $this->points[$pos->y][$pos->x]->nothing();
                        else $this->points[$pos->y][$pos->x]->kill();
                    }
                }
                return $this->points[$pos->y][$pos->x]->team;
            }  else {
                $score = $this->points[$pos->y][$pos->x]->teams[0]->score;
                if ($score == 3) {
                    if ($this->points[$pos->y][$pos->x]->team === 0)
                        $this->incScore(1, 1);
                    $this->points[$pos->y][$pos->x]->alive(1);
                }
                elseif ($score == 2) $this->points[$pos->y][$pos->x]->nothing();
                else $this->points[$pos->y][$pos->x]->kill();
                return $this->points[$pos->y][$pos->x]->team;
            }
        }

        public function alivePointAdd($pos) {
            array_unshift($this->alivePoints, $pos);
        }

        public function generateZeroPoints() {
            $x = rand(0 , $this->sizeX-1);
            $y = rand(0 , $this->sizeY-1);
            for ($i = 0; $i < count($this->alivePoints); $i++ ) {
                if ($x === $this->alivePoints[$i]->x && $y === $this->alivePoints[$i]->y) {
                    $i = 0;
                    $x = rand(0 , $this->sizeX-1);
                    $y = rand(0 , $this->sizeY-1);
                }
            }
            $this->points[$y][$x]->zeroPoint(rand(-1, 1));
        }

        public function step() {
            $this->culcPoints();

            //$this->show();

            while (! empty($this->checkPoints)) {
                $pos = array_shift($this->checkPoints);
                $this->points[$pos->y][$pos->x]->disadd();
                if ($this->rules($pos) !== 0) {
                    $this->alivePointAdd($pos);
                }
            }
            $this->generateZeroPoints();
        }

        public function xstep()
        {
            $this->step();
            $array = $this->output();
            while (! empty($this->alivePoints)) {
              $pos = array_shift($this->alivePoints);
            }
            for ($j = 0; $j < $this->sizeX; $j++) {
                for ($i = 0; $i < $this->sizeY; $i++) {
                $this->points[$j][$i]->team = 0;
                }
            }
             $this->setPoints($array);
        }


        public function output() {
            $helpArray = array();
            for ($j = 0; $j < $this->sizeX; $j++) {
                for ($i = 0; $i < $this->sizeY; $i++) {
                    if ($this->points[$i][$j]->zero === 0)
                        array_push($helpArray, $this->points[$i][$j]->team);
                    else  array_push($helpArray, "z".$this->points[$i][$j]->zero);
                }
            }
            for ($i = 0; $i < $this->teamNumber; $i++) array_push($helpArray, $this->teams[$i]->score);
            //var_dump($helpArray);
            return $helpArray;
        }

        public function show() {
            echo "</ br> </ br> </ br>";
            for ($i = 0; $i < $this->sizeY; $i++) {
                echo "</ br>";
                for ($j = 0; $j < $this->sizeX; $j++) {
                //echo $this->points[$j][$i]->teams[0]->score." ";
                    echo $this->points[$i][$j]->team." ";
                    //echo $this->points[$j][$i]->zero." ";
                }
            }
        }

        public function log() {
            echo "</ br> </ br> </ br>";
            for ($i = 0; $i < count($this->points); $i++) {
                echo "</ br>";
                for ($j = 0; $j < count($this->points[$i]); $j++) {
                    echo $this->points[$i][$j]->team." ";

                }
            }
        }
    }

 ?>