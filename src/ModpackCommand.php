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
				'info, get'
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
			case 'get':
				$this->getModpack($output, $modpackSlug, $modpackBuild);
				break;
			default:
				throw new \InvalidArgumentException('Invalid arguments');
		}
	}

	private function infoModpack($output, $modpackSlug, $modpackBuild)
	{
		$apiClient = new Client();
		$appConfig = solder_config();

		displayServerInfo($output);

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

	private function getModpack($output, $modpackSlug, $modpackBuild)
	{
		if($modpackSlug == '' || $modpackBuild == '') {
			throw new \InvalidArgumentException('Invalid arguments');
		}

		$apiClient = new Client();
		$appConfig = solder_config();

		if($modpackBuild == 'latest' || $modpackBuild == 'recommended') {
			$apiResponse = $apiClient->get($appConfig->api.'/modpack/'.$modpackSlug)->json();
			$modpackBuild = $apiResponse[$modpackBuild];
		}

		$apiResponse = $apiClient->get($appConfig->api . '/modpack/' . $modpackSlug . '/' . $modpackBuild)->json();
		if(isset($apiResponse['error'])) {
			throw new \Exception($apiResponse['error']);
		}

		if(!is_dir($modpackSlug . '-' . $modpackBuild)){
			$output->writeln("creating: $modpackSlug-$modpackBuild" . DIRECTORY_SEPARATOR);
			mkdir($modpackSlug . '-' . $modpackBuild);
		}

		foreach( $apiResponse['mods'] as $mod ) {
			$url = $mod['url'];
			$filename = basename($url);
			$md5 = $mod['md5'];
			downloadFile($url, $modpackSlug . '-' . $modpackBuild . DIRECTORY_SEPARATOR . $filename, $output, $md5);
			unpackFile($modpackSlug . '-' . $modpackBuild . DIRECTORY_SEPARATOR . $filename, $output);
		}
	}


}
