<?php namespace Indemnity83\SolderToolbelt;

use GuzzleHttp\Client;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class ModpackCommand extends Command {

	/**
	 * Configure the command options.
	 *
	 * @return void
	 */
	protected function configure()
	{
		$this->setName('modpack')
			->setDescription('Work with modpacks on the TechnicSolder site')
			->addArgument(
				'action',
				InputArgument::REQUIRED,
				'list, get'
			)
			->addArgument(
				'slug',
				InputArgument::OPTIONAL,
				'The slug of the modpack you wish to request'
			)
			->addArgument(
				'build',
				InputArgument::OPTIONAL,
				'Specific build you wish to view'
			);
	}

	/**
	 * Execute the command.
	 *
	 * @param	\Symfony\Component\Console\Input\InputInterface	$input
	 * @param	\Symfony\Component\Console\Output\OutputInterface	$output
	 * @return void
	 */
	public function execute(InputInterface $input, OutputInterface $output)
	{
		$commandAction = $input->getArgument('action');
		$modpackSlug = $input->getArgument('slug');
		$modpackBuild = $input->getArgument('build');

		switch( $commandAction ) {
			case 'info':
				$this->infoModpack($output, $modpackSlug, $modpackBuild);
				break;
			default:
				throw new \InvalidArgumentException('Invalid arguments');
		}
	}

	private function infoModpack($output, $modpackSlug, $modpackBuild)
	{
		$apiClient = new Client();
		$appConfig = solder_config();

		$output->writeln('<comment>Server:</comment>');
		$technicSolder = $apiClient->get($appConfig->api)->json();
		$output->writeln(" <info>{$technicSolder['api']}</info> version {$technicSolder['version']}");
		$output->writeln(" {$appConfig->api}");

		if($modpackBuild == 'latest' || $modpackBuild == 'recommended') {
			$apiResponse = $apiClient->get($appConfig->api.'/modpack/'.$modpackSlug)->json();
			$modpackBuild = $apiResponse[$modpackBuild];
		}

		if (  $modpackSlug == '' && $modpackBuild == ''  ) {
			$apiUri = $appConfig->api.'/modpack';
		} elseif( $modpackSlug != '' && $modpackBuild == '' ) {
			$apiUri = $appConfig->api.'/modpack/'.$modpackSlug;
		} else {
			$apiUri = $appConfig->api.'/modpack/'.$modpackSlug.'/'.$modpackBuild;
		}

		$apiResponse = $apiClient->get($apiUri)->json();
		if(isset($apiResponse['error'])) {
			throw new \Exception($apiResponse['error']);
		}

		$rows = array();
		$mods = array();
		$modpacks = array();
		foreach( $apiResponse as $key => $value ) {
			if( $key == 'mods' ) {
				foreach( $value as $mod) {
					$mods[] = array("<info>{$mod['name']}</info>", $mod['version']);
				}
			} elseif( $key == 'modpacks' ) {
				foreach( $value as $slug => $build) {
					$modpacks[] = array("<info>{$slug}</info>", $build);
				}
			} elseif( is_array($value) ) {
				$rows[] = array("<info>$key</info>", implode($value,"\n"));
			} else {
				$rows[] = array("<info>$key</info>", mb_strimwidth($value, 0, 60, "..."));
			}
		}

		if( $modpackSlug == '' && $modpackBuild == '') {
			$output->writeln('');
			$output->writeln('<comment>Available Modpacks:</comment>');
			$table = new Table($output);
			$table
				->setRows($modpacks)
				->setStyle('compact')
				->render();
		}

		if( $modpackSlug != '' && $modpackBuild == '' ) {
			$output->writeln('');
			$output->writeln("<comment>Modpack:</comment>");
			$table = new Table($output);
			$table
				->setRows($rows)
				->setStyle('compact')
				->render();
		}

		if( $modpackBuild != '' ) {
			$output->writeln('');
			$output->writeln('<comment>Build:</comment>');
			$table = new Table($output);
			$table
				->setRows($rows)
				->setStyle('compact')
				->render();

			$output->writeln('');
			$output->writeln('<comment>Mods:</comment>');
			$table = new Table($output);
			$table
				->setRows($mods)
				->setStyle('compact')
				->render();
		}

	}

	private function displayServerInfo($output)
	{
		$apiClient = new Client();
		$appConfig = solder_config();

		$technicSolder = $apiClient->get($appConfig->api)->json();
		if(isset($technicSolder['error'])) {
			throw new \Exception($technicSolder['error']);
		}

		$output->writeln('<comment>Server:</comment>');
		$output->writeln(" <info>{$technicSolder['api']}</info> version {$technicSolder['version']}");
		$output->writeln(" {$appConfig->api}");

		$apiClient = null;
		$appConfig = null;
		$technicSolder = null;
	}

}
