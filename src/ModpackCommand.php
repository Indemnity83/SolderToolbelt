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
	 * @param  \Symfony\Component\Console\Input\InputInterface  $input
	 * @param  \Symfony\Component\Console\Output\OutputInterface  $output
	 * @return void
	 */
	public function execute(InputInterface $input, OutputInterface $output)
	{
    $client = new Client();
    $slug = $input->getArgument('slug');
    $build = $input->getArgument('build');
    $config = solder_config();

    $response = $client->get($config->api);
    $server = $response->json();

    if( $slug != '' && $build == '' ) {
      // Slug, No Build
      $response = $client->get($config->api.'/modpack/'.$slug);
      $json = $response->json();

      if(isset($json['error'])) {
        throw new \Exception($json['error']);
      }

      $rows = array();
      foreach( $json as $key => $value ) {
        if( $key == 'builds' ) {
          $rows[] = array("<info>$key</info>", implode($value,"\n"));
        } else {
          $rows[] = array("<info>$key</info>", $value);
        }
      }

      $output->writeln('<comment>Server:</comment>');
      $output->writeln(" <info>{$server['api']}</info> version {$server['version']}");
      $output->writeln(" {$config->api}");

      $output->writeln('');
      $output->writeln("<comment>Modpack:</comment>");
      $table = new Table($output);
      $table
          ->setRows($rows)
          ->setStyle('compact')
          ->render();

    } elseif( $slug != '' && $build != '' ) {
      // Slug With Build
      if($build == 'latest' || $build == 'recommended') {
        $response = $client->get($config->api.'/modpack/'.$slug);
        $json = $response->json();
        $build = $json[$build];
      }

      $response = $client->get($config->api.'/modpack/'.$slug.'/'.$build);
      $json = $response->json();

      if(isset($json['error'])) {
        throw new \Exception($json['error']);
      }

      $build = array();
      $mods = array();
      foreach( $json as $key => $value ) {
        if( $key == 'mods' ) {
          foreach( $value as $mod) {
            $mods[] = array("<info>{$mod['name']}</info>", $mod['version']);
          }
        } else {
          $build[] = array("<info>$key</info>", $value);
        }
      }

      $output->writeln('<comment>Server:</comment>');
      $output->writeln(" <info>{$server['api']}</info> version {$server['version']}");
      $output->writeln(" {$config->api}");

      $output->writeln('');
      $output->writeln('<comment>Build:</comment>');
      $table = new Table($output);
      $table
          ->setRows($build)
          ->setStyle('compact')
          ->render();

      $output->writeln('');
      $output->writeln('<comment>Mods:</comment>');
      $table = new Table($output);
      $table
          ->setRows($mods)
          ->setStyle('compact')
          ->render();


    } else {
      $response = $client->get($config->api.'/modpack');
      $json = $response->json();

      if(isset($json['error'])) {
        throw new \Exception($json['error']);
      }

      $rows = array();
      foreach( $json['modpacks'] as $slug => $name ) {
        $rows[] = array("<info>$slug</info>", $name);
      }

      $output->writeln('<comment>Server:</comment>');
      $output->writeln(" <info>{$server['api']}</info> version {$server['version']}");
      $output->writeln(" {$config->api}");

      $output->writeln('');
      $output->writeln('<comment>Available Modpacks:</comment>');
      $table = new Table($output);
      $table
          ->setRows($rows)
          ->setStyle('compact')
          ->render();
    }

	}

}
