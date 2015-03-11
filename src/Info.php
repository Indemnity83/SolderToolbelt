<?php namespace Indemnity83\ModPack;

use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class Info extends Command {
	/**
	 * Configure the command options.
	 *
	 * @return void
	 */
	protected function configure()
	{
		$this
      ->setName('info')
      ->setDescription('Show information about a mod file')
      ->addArgument('mod', InputArgument::REQUIRED);
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

    $mod = new ModLib($input->getArgument('mod'));

    $table = new Table($output);
    $table->setRows(array(
      array('Mod Name', $mod->name),
      array('Mod Slug', $mod->slug),
      array('Mod Version', $mod->version),
      array('MC Version', $mod->mcversion)
    ));

    $table->render();
	}
}
