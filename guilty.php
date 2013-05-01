<?php

class Guilty
{
	private $evidence;
	private $matrix = array();
	private $suspects = array();
	private $scores = array();

	/**
	  * returns the suspect that has the closest match to the evidence
	  */
	public function searchGuilty($file)
	{
		$best = array("fitness" => 0);

		$this->loadFile($file);

		foreach ($this->suspects as $idSuspect => $suspect)
		{
			$fitness = $this->fitness($this->evidence, $suspect);
			if ($best["fitness"] < $fitness || $idSuspect == 0) {
				$best["fitness"] = $fitness;
				$best["suspect"] = $idSuspect + 1;
				$best["adn"] = $suspect;
			}
		}
		echo "El culpable es el sospechoso número ".$best["suspect"]." (".$best["adn"].").";
	}

	/**
	  * extracts data from the input file and stores them in the variables: matrix, evidence and suspects
	  */
	private function loadFile($route)
	{
		$columns = array("A","C","G","T","-");

		$file = file($route, FILE_IGNORE_NEW_LINES);

		foreach ($file as $line )
		{
			$line = str_replace(" ", "", $line);
			if ($line[0] == '#') continue;
			if ( in_array($line[0], $columns) ) {
				$this->matrix[ $line[0] ] = array_combine($columns, explode( ',', substr($line, 2) ));
			} else {
				if ($line[0] == "0") {
					$this->evidence = substr($line, 2);
				}
				else {
					$linePart = explode(":", $line);
					$this->suspects[] =  $linePart[1];
				}
			}
		}
	}

	/**
	  * Returns the maximum value of alignment of the suspect with the evidence
	  */
	private function fitness($evidence, $suspect)
	{

		$fitnessValue = array();
		$lenghtEvidence = strlen($evidence);
		$lenghtSuspect = strlen($suspect);

		if ($lenghtEvidence == 0 && $lenghtSuspect == 0) {
			return 0;
		} else {

			if ($lenghtEvidence > 0 && $lenghtSuspect > 0) {
				$fitnessValue[] = $this->searchScore(substr($evidence, 1), substr($suspect, 1)) + $this->matrix[$evidence[0]][$suspect[0]];
			}
			if ($evidence[0] != $suspect[0]) {
				if ($lenghtSuspect > 0) {
					$fitnessValue[] = $this->searchScore($evidence, substr($suspect, 1)) + $this->matrix["-"][$suspect[0]];
				}
				if ($lenghtEvidence > 0) {
					$fitnessValue[] = $this->searchScore(substr($evidence, 1),$suspect) + $this->matrix[$evidence[0]]["-"];
				}
			}
			return max($fitnessValue);
		}
	}

	/**
	  * stores the result in the matrix of scores, if it is found, returns the value contained.
	  */
	private function searchScore($evidence, $suspect)
	{
		if ( ! isset($this->scores[$evidence][$suspect]) ) {
			$this->scores[$evidence][$suspect] = $this->fitness($evidence, $suspect);
		}
		return $this->scores[$evidence][$suspect];
	}

}

$gen = new Guilty;
$gen->searchGuilty($argv[1]);

?>