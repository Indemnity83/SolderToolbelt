<?php namespace Indemnity83\ModPack;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class Pack extends Command {
	/**
	 * Configure the command options.
	 *
	 * @return void
	 */
	protected function configure() {
		$this
      ->setName('pack')
      ->setDescription('Package a mod file in a zip, ready for distribution')
      ->addArgument(
        'file',
        InputArgument::REQUIRED,
        'Mod file to be packaged'
      )
      ->addOption(
        'add-config',
        null,
        InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        'config file(s) to include in package'
      )
      ->addOption(
        'no-folder',
        null,
        InputOption::VALUE_NONE,
        'Do not create a folder for the mod'
      )
      ->addOption(
        'dest',
        null,
        InputOption::VALUE_REQUIRED,
        'Specify a destination for packed files'
      )
      ->addOption(
        'override-name',
        null,
        InputOption::VALUE_REQUIRED,
        'Override mod name parameter'
      )
      ->addOption(
        'override-version',
        null,
        InputOption::VALUE_REQUIRED,
        'Override mod version parameter'
      )
      ->addOption(
        'override-mcversion',
        null,
        InputOption::VALUE_REQUIRED,
        'Override minecraft version parameter'
      )
      ->addOption(
        'extract-folder',
        null,
        InputOption::VALUE_REQUIRED,
        'Set folder to be used when mod package is expanded',
				'mods'
      );
	}
	/**
	 * Execute the command.
	 *
	 * @param  \Symfony\Component\Console\Input\InputInterface  $input
	 * @param  \Symfony\Component\Console\Output\OutputInterface  $output
	 * @return void
	 */
	public function execute(InputInterface $input, OutputInterface $output) {
    $mod = new ModLib($input->getArgument('file'));

    // Process overrides
    if ($input->getOption('override-name')) {
      $mod->name = $input->getOption('override-name');
    }
    if ($input->getOption('override-version')) {
      $mod->version = $input->getOption('override-version');
    }
    if ($input->getOption('override-mcversion')) {
      $mod->mcversion = $input->getOption('override-mcversion');
    }

    // Validate required info, ask for missing info
    $helper = $this->getHelper('question');
    if ($mod->name == '') {
      $question = new Question('Mod Name: ', 'AcmeDemoBundle');
      $mod->name = $helper->ask($input, $output, $question);
      $mod->slug = $mod->slug($mod->name);
    }
    if ($mod->version == '') {
      $question = new Question('Mod Version: ', 'AcmeDemoBundle');
      $mod->version = $helper->ask($input, $output, $question);
    }
    if ($mod->mcversion == '') {
      $question = new Question('Minecraft Version: ', 'AcmeDemoBundle');
      $mod->mcversion = $helper->ask($input, $output, $question);
    }

    // Pack it up
    $mod->package(
      $input->getOption('dest'),
      $input->getOption('add-config'),
      $input->getOption('no-folder'),
      $input->getOption('extract-folder')
    );
	}
}
