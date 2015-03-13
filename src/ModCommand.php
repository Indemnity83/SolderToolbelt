<?php namespace Indemnity83\SolderToolbelt;

use GuzzleHttp\Client;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class ModCommand extends Command {

	/**
	 * Configure the command options.
	 *
	 * @return void
	 */
	protected function configure()
	{
		$this->setName('mod')
			->setDescription('Work with mods on the TechnicSolder site')
			->addArgument(
					'action',
					InputArgument::REQUIRED,
					'info, get'
				)
			->addArgument(
				 'slug',
				 InputArgument::REQUIRED,
				 'The slug of the mod you wish to request'
			)
			->addArgument(
				 'version',
				 InputArgument::OPTIONAL,
				 'Specific modversion you wish to view'
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
		$modSlug = $input->getArgument('slug');
		$modVersion = $input->getArgument('version');

		switch( $commandAction ) {
			case 'info':
				$this->infoMod($output, $modSlug, $modVersion);
				break;
			case 'get':
				$this->getMod($output, $modSlug, $modVersion);
				break;
			default:
				throw new \InvalidArgumentException('Invalid arguments');
		}
	}

	private function infoMod($output, $slug, $version)
	{
		$this->displayServerInfo($output);

		$apiClient = new Client();
		$appConfig = solder_config();

		$apiResponse = $apiClient->get($appConfig->api . '/mod/' . $slug . '/' . $version)->json();
		if(isset($apiResponse['error'])) {
			throw new \Exception($apiResponse['error']);
		}

		$rows = array();
		foreach( $apiResponse as $key => $value ) {
			if( $key == 'versions' ) {
				$rows[] = array("<info>$key</info>", implode($value,"\n"));
			} else {
				$rows[] = array("<info>$key</info>", mb_strimwidth($value, 0, 60, "..."));
			}
		}

		$output->writeln('');
		$output->writeln("<comment>Mod:</comment>");
		$table = new Table($output);
		$table
				->setRows($rows)
				->setStyle('compact')
				->render();
	}

	private function getMod($output, $slug, $version)
	{
		if($slug == '' || $version == '') {
			throw new \InvalidArgumentException('Invalid arguments');
		}

		$apiClient = new Client();
		$appConfig = solder_config();

		$apiResponse = $apiClient->get($appConfig->api . '/mod/' . $slug . '/' . $version)->json();
		if(isset($apiResponse['error'])) {
			throw new \Exception($apiResponse['error']);
		}

		$url = $apiResponse['url'];
		$filename = basename($url);
		$md5 = $apiResponse['md5'];

		downloadFile($url, $filename, $output, $md5);

		if( md5_file($filename) != $md5 ) {
			throw new \Exception('Hash dosen\'t match');
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
