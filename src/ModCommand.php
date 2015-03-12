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
           'modname',
           InputArgument::REQUIRED,
           'The slug of the mod you wish to request'
         )
         ->addArgument(
           'modversion',
           InputArgument::OPTIONAL,
           'Specific modversion you wish to view'
         );
	}

	/**
	 * Execute the command.
	 *
	 * @param  \Symfony\Component\Console\Input\InputInterface  $input
	 * @param  \Symfony\Component\Console\Output\OutputInterface  $output
	 * @return void
	 */
	public function execute(InputInterface $input, OutputInterface $output)
	{
    $client = new Client();
    $modname = $input->getArgument('modname');
    $modversion = $input->getArgument('modversion');
    $config = solder_config();

    $response = $client->get($config->api);
    $server = $response->json();

    $response = $client->get($config->api.'/mod/'.$modname.'/'.$modversion);
    $json = $response->json();

    if(isset($json['error'])) {
      throw new \Exception($json['error']);
    }

    $rows = array();
    foreach( $json as $key => $value ) {
      if( $key == 'versions' ) {
        $rows[] = array("<info>$key</info>", implode($value,"\n"));
      } else {
        $rows[] = array("<info>$key</info>", mb_strimwidth($value, 0, 80, "..."));
      }
    }

    $output->writeln('<comment>Server:</comment>');
    $output->writeln(" <info>{$server['api']}</info> version {$server['version']}");
    $output->writeln(" $api");

    $output->writeln('');
    $output->writeln("<comment>Mod:</comment>");
    $table = new Table($output);
    $table
        ->setRows($rows)
        ->setStyle('compact')
        ->render();



	}

}
