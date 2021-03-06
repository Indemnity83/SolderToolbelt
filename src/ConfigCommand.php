<?php namespace Indemnity83\SolderToolbelt;

use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigCommand extends Command {

	/**
	 * Configure the command options.
	 *
	 * @return void
	 */
	protected function configure()
	{
		$this->setName('config')
                  ->setDescription('Edit the Solder.json file');
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
		$command = $this->executable().' '.solder_path().'/Solder.json';
		$process = new Process($command, realpath(__DIR__.'/../'), null, null, null);

		$process->run(function($type, $line) use ($output)
		{
			$output->write($line);
		});
	}

	/**
	 * Find the correct executable to run depending on the OS.
	 *
	 * @return string
	 */
	protected function executable()
	{
		return strpos(strtoupper(PHP_OS), 'WIN') === 0 ? 'start' : 'open';
	}

}
