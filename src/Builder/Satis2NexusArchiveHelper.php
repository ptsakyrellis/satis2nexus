<?php

declare(strict_types=1);

namespace Composer\Satis\Builder;

use \Curl\Curl;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Satis to Nexus
 *
 * @author Gaétan Vénuat <gaetanvenuat@gmail.com>
 */
class Satis2NexusArchiveHelper
{
	const NEXUS = 'http://nexus3.tyforge.in.ac-rennes.fr';
	const REPO = 'composer-aca-toulouse';
	const USER = 'systeme-act';
	const PASSWORD = "kI}A]H>DDC~bo=&y`N*u";

	/** @var OutputInterface The output Interface. */
	private $output;

	/**
	 * @param OutputInterface $output        The output Interface
	 */
	public function __construct(OutputInterface $output)
	{
		$this->output = $output;
	}

	/**
	 * Ajoute un package Nexus
	 * @param String $file
	 * @param String $name
	 * @param String $version
	 * @throws \ErrorException
	 */
	public function send2Nexus(String $file, String $name, String $version)
	{
		$url = $this::NEXUS.'/repository/'.$this::REPO.'/packages/upload'.'/'.$name.'/'.$version;
		$fh_res = fopen($file, 'r');

		$curl = new Curl();
		$curl->verbose();
		$curl->setBasicAuthentication($this::USER, $this::PASSWORD);
		$curl->setOpt(CURLOPT_INFILE,$fh_res);
		$curl->setOpt(CURLOPT_INFILESIZE,filesize($file));
		$curl->put($url);

		fclose($fh_res);
	}

	/**
	 * Supprime un package Nexus
	 * @param String $id
	 * @throws \ErrorException
	 */
	public function remove2Nexus(String $id)
	{
		$url = $this::NEXUS.'/service/rest/v1/components/'.$id;

		$curl = new Curl();
		$curl->verbose();
		$curl->setBasicAuthentication($this::USER, $this::PASSWORD);
		$curl->delete($url);
	}

	/**
	 * Recherche toutes l'id du package Nexus dont on connait le path
	 * @param array $neededPackages
	 * @throws \ErrorException
	 */
	public function deleteNoNeeded2Nexus(array $neededPackages)
	{
		$continuationToken = -1;

		$curl = new Curl();
		$curl->verbose();
		$curl->setBasicAuthentication($this::USER, $this::PASSWORD);
		$curl->setHeader('accept','application/json');

		while($continuationToken != null) {
			if($continuationToken==-1){
				$url = $this::NEXUS.'/service/rest/v1/components?repository='.$this::REPO;
			}else{
				$url = $this::NEXUS.'/service/rest/v1/components?repository='.$this::REPO.'&continuationToken='.$continuationToken;
			}
			$res = $curl->get($url);
			$continuationToken = $res->continuationToken;
			foreach ($res->items as $presentPackage) {
				// Si c'est pas un package needed, on supprime
				if(!$this->checkIfNeeded($neededPackages,$presentPackage)){
					$this->remove2Nexus($presentPackage->id);
				}
			}
		}
	}

	private function checkIfNeeded(array $neededPackages, $package){
		foreach($neededPackages as $neededPackage){
			if($neededPackage->getPrettyName() == $package->group.'/'.$package->name
			&& $neededPackage->getPrettyVersion() == $package->version){
				return true;
			}
		}
		return false;
	}


}