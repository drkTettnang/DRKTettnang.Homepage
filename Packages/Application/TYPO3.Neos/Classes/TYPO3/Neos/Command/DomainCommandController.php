<?php
namespace TYPO3\Neos\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Neos".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Neos\Domain\Model\Domain;
use TYPO3\Neos\Domain\Model\Site;
use TYPO3\Neos\Domain\Repository\DomainRepository;
use TYPO3\Neos\Domain\Repository\SiteRepository;

/**
 * Domain command controller for the TYPO3.Neos package
 *
 * @Flow\Scope("singleton")
 */
class DomainCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @var DomainRepository
	 * @Flow\Inject
	 */
	protected $domainRepository;

	/**
	 * @var SiteRepository
	 * @Flow\Inject
	 */
	protected $siteRepository;

	/**
	 * Add a domain record
	 *
	 * @param string $siteNodeName The nodeName of the site rootNode, e.g. "neostypo3org"
	 * @param string $hostPattern The host pattern to match on, e.g. "neos.typo3.org"
	 * @return void
	 */
	public function addCommand($siteNodeName, $hostPattern) {
		$site = $this->siteRepository->findOneByNodeName($siteNodeName);
		if (!$site instanceof Site) {
			$this->outputLine('No site found with nodeName "%s".', array($siteNodeName));
			$this->quit(1);
		}

		$domains = $this->domainRepository->findByHostPattern($hostPattern);
		if ($domains->count() > 0) {
			$this->outputLine('The host pattern "%s" is not unique.', array($hostPattern));
			$this->quit(1);
		}

		$domain = new Domain();
		$domain->setSite($site);
		$domain->setHostPattern($hostPattern);
		$this->domainRepository->add($domain);

		$this->outputLine('Domain created.');
	}

	/**
	 * Display a list of available domain records
	 *
	 * @param string $hostPattern An optional host pattern to search for
	 * @return void
	 */
	public function listCommand($hostPattern = NULL) {
		if ($hostPattern === NULL) {
			$domains = $this->domainRepository->findAll();
		} else {
			$domains = $this->domainRepository->findByHost($hostPattern);
		}

		if (count($domains) === 0) {
			$this->outputLine('No domains available.');
			$this->quit(0);
		}

		$longestNodeName = 9;
		$longestHostPattern = 12;
		$availableDomains = array();

		foreach ($domains as $domain) {
			/** @var \TYPO3\Neos\Domain\Model\Domain $domain */
			array_push($availableDomains, array(
				'nodeName' => $domain->getSite()->getNodeName(),
				'hostPattern' => $domain->getHostPattern(),
				'active' => $domain->getActive()
			));
			if (strlen($domain->getSite()->getNodeName()) > $longestNodeName) {
				$longestNodeName = strlen($domain->getSite()->getNodeName());
			}
			if (strlen($domain->getHostPattern()) > $longestHostPattern) {
				$longestHostPattern = strlen($domain->getHostPattern());
			}
		}

		$this->outputLine();
		$this->outputLine(' ' . str_pad('Node name', $longestNodeName + 10) . str_pad('Host pattern', $longestHostPattern + 5) . 'State');
		$this->outputLine(str_repeat('-', $longestNodeName + $longestHostPattern + 10 + 2 + 14));
		foreach ($availableDomains as $domain) {
			$this->outputLine(' ' . str_pad($domain['nodeName'], $longestNodeName + 10) . str_pad($domain['hostPattern'], $longestHostPattern + 5) . ($domain['active'] ? 'Active' : 'Inactive'));
		}
		$this->outputLine();
	}

	/**
	 * Delete a domain record
	 *
	 * @param string $hostPattern The host pattern of the domain to remove
	 * @return void
	 */
	public function deleteCommand($hostPattern) {
		$domain = $this->domainRepository->findOneByHostPattern($hostPattern);
		if (!$domain instanceof Domain) {
			$this->outputLine('Domain not found.');
			$this->quit(1);
		}

		$this->domainRepository->remove($domain);
		$this->outputLine('Domain deleted.');
	}

	/**
	 * Activate a domain record
	 *
	 * @param string $hostPattern The host pattern of the domain to activate
	 * @return void
	 */
	public function activateCommand($hostPattern) {
		$domain = $this->domainRepository->findOneByHostPattern($hostPattern);
		if (!$domain instanceof Domain) {
			$this->outputLine('Domain not found.');
			$this->quit(1);
		}

		$domain->setActive(TRUE);
		$this->domainRepository->update($domain);
		$this->outputLine('Domain activated.');
	}

	/**
	 * Deactivate a domain record
	 *
	 * @param string $hostPattern The host pattern of the domain to deactivate
	 * @return void
	 */
	public function deactivateCommand($hostPattern) {
		$domain = $this->domainRepository->findOneByHostPattern($hostPattern);
		if (!$domain instanceof Domain) {
			$this->outputLine('Domain not found.');
			$this->quit(1);
		}

		$domain->setActive(FALSE);
		$this->domainRepository->update($domain);
		$this->outputLine('Domain deactivated.');
	}

}