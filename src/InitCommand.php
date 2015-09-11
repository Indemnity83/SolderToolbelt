<?php namespace Indemnity83\SolderToolbelt;

use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command {

	/**
	 * Configure the command options.
	 *
	 * @return void
	 */
	protected function configure()
	{
		$this->setName('init')
                  ->setDescription('Create a stub config file');
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
		if (!is_dir(solder_path())) {
			mkdir(solder_path());
		}

		if(is_file(solder_path().'/Solder.json')){
			throw new \InvalidArgumentException("Solder has already been initialized.");
		}

		$output->write('<comment>Creating Solder.json file...</comment> ');

		if( copy(__DIR__.'/stubs/Solder.json', solder_path().'/Solder.json') ){
			$output->writeln('<info>✔</info>');
			$output->writeln('<comment>Solder.json file created at:</comment> '.solder_path().'/Solder.json');
		} else {
			$output->writeln('<fg=red>✘</>');
			$output->writeln('<error>Could not write Solder.json to: '.solder_path().'/Solder.json</error>');
		}
	}

}
