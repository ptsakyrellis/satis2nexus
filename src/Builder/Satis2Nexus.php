<?php

declare(strict_types=1);

namespace Composer\Satis\Builder;

use Composer\Package\Package;
use \Curl\Curl;
use ErrorException;
use stdClass;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Satis to Nexus
 *
 * @author Gaétan Vénuat <gaetanvenuat@gmail.com>
 */
class Satis2Nexus
{
	/** @var OutputInterface $output The output Interface. */
	protected $output;

	/** @var array $config The parameters from ./satis.json. */
	protected $config;

	/**
	 * Base Constructor.
	 *
	 * @param OutputInterface $output     The output Interface
	 * @param array           $config     The parameters from ./satis.json
	 */
	public function __construct(OutputInterface $output, $config)
	{
		$this->output = $output;
		$this->config = $config;
	}

	/**
	 * Envoi un package sur Nexus
	 *
	 * @param String $file Le lien de l'archive du package à envoyer
	 * @param Package $package Le package à envoyer
	 * @throws ErrorException
	 */
	public function send2Nexus(String $file, Package $package)
	{
		$url = $this->config['nexus'].'/repository/'.$this->config['nexus-repository'].'/packages/upload/'.$package->getPrettyName().'/'.$package->getPrettyVersion();
		$fh_res = fopen($file, 'r');

		$curl = new Curl();
		$curl->verbose();
		$curl->setBasicAuthentication($this->config['nexus-user'], $this->config['nexus-password']);
		$curl->setOpt(CURLOPT_PUT,true);
		$curl->setOpt(CURLOPT_HTTPHEADER, array('Expect:'));
		$curl->setOpt(CURLOPT_INFILE,$fh_res);
		$curl->setOpt(CURLOPT_INFILESIZE,filesize($file));
		$curl->put($url);

		fclose($fh_res);
	}


	/**
	 * Supprime un package de Nexus
	 * @param String $id L'id du package
	 * @throws ErrorException
	 */
	public function remove2Nexus(String $id)
	{
		$url = $this->config['nexus'].'/service/rest/v1/components/'.$id;

		$curl = new Curl();
		$curl->verbose();
		$curl->setBasicAuthentication($this->config['nexus-user'], $this->config['nexus-password']);
		$curl->delete($url);
	}

	/**
	 * Supprime tous les packages non référencés sur Nexus
	 * @param array $neededPackages Les packages référencés
	 * @throws ErrorException
	 */
	public function deleteNoNeeded2Nexus(array $neededPackages)
	{
		$packages = $this->getAllFromNexus();
		foreach ($packages as $presentPackage) {
			// Si c'est pas un package needed, on supprime
			if(!$this->checkIfNeeded($neededPackages,$presentPackage)){
				$this->remove2Nexus($presentPackage->id);
			}
		}
	}

	/**
	 * Envoi tous les packages référencés qui n'existent pas déjà sur Nexus
	 *
	 * @param array $files Tableau (clé = Lien de l'archive, valeur = package) des packages qui doivent êtres présents sur Nexus
	 * @throws ErrorException
	 */
	public function sendNeeded2Nexus(array $files)
	{
		$presentPackages = $this->getAllFromNexus();
		foreach ($files as $file => $neededPackage) {
			// Si c'est un package needed, on ajoute
			if(!$this->checkIfPresent($presentPackages,$neededPackage)){
				$this->send2Nexus($file ,$neededPackage);
			}
		}
	}

	/**
	 * Regarde si un package à besoin d'être sur Nexus
	 *
	 * @param array $neededPackages Liste des packages qui doivent êtres sur Nexus
	 * @param $package stdClass Le package Nexus à vérifier
	 * @return bool
	 */
	private function checkIfNeeded(array $neededPackages, $package){
		foreach($neededPackages as $neededPackage){
			if($neededPackage->getPrettyName() == $package->group.'/'.$package->name
			&& $neededPackage->getPrettyVersion() == $package->version){
				return true;
			}
		}
		return false;
	}

	/**
	 * Regarde si in package est présent sur Nexus
	 *
	 * @param array $presentPackages Liste des packages au format stdClass de Nexus
	 * @param $package Package Le package à vérifier
	 * @return bool
	 */
	private function checkIfPresent(array $presentPackages, $package){
		foreach($presentPackages as $presentPackage){
			if($package->getPrettyName() == $presentPackage->group.'/'.$presentPackage->name
				&& $package->getPrettyVersion() == $presentPackage->version){
				return true;
			}
		}
		return false;
	}

	/**
	 * Récupère la liste des packages Nexus
	 *
	 * @return array La liste des packages
	 * @throws ErrorException
	 */
	public function getAllFromNexus()
	{
		$continuationToken = -1;

		$curl = new Curl();
		$curl->verbose();
		$curl->setBasicAuthentication($this->config['nexus-user'], $this->config['nexus-password']);
		$curl->setHeader('accept','application/json');
		$packages = array();

		while($continuationToken != null) {
			if($continuationToken==-1){
				$url = $this->config['nexus'].'/service/rest/v1/components?repository='.$this->config['nexus-repository'];
			}else{
				$url = $this->config['nexus'].'/service/rest/v1/components?repository='.$this->config['nexus-repository'].'&continuationToken='.$continuationToken;
			}
			$res = $curl->get($url);
			$continuationToken = $res->continuationToken;
			foreach ($res->items as $presentPackage) {
				$packages[]=$presentPackage;
			}
		}

		return $packages;
	}


}