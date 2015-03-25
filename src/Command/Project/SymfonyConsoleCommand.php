<?php

/*
 * This file is part of the Jarvis package
 *
 * Copyright (c) 2015 Tony Dubreil
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Tony Dubreil <tonydubreil@gmail.com>
 */

namespace Jarvis\Command\Project;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Jarvis\Project\ProjectConfiguration;
use Jarvis\Project\Repository\ProjectConfigurationRepository;

class SymfonyConsoleCommand extends BaseSymfonyCommand
{
    /**
     * @var string
     */
    private $symfonyCommandName;

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->enabled && count($this->getProjectConfigurationRepository()->getProjectNames());
    }

    /**
     * @{inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Executes Symfony console command');

        if ($this->getName() === 'project:symfony:console') {
            $this->addArgument('command_name', InputArgument::OPTIONAL, 'Command name', 'list');
        }

        if (!$this->getDefinition()->hasOption('project-name')) {
            $this->addOption('project-name', null, InputOption::VALUE_OPTIONAL);
        }

        $this->addOption('symfony-env', null, InputOption::VALUE_REQUIRED, 'The Symfony Environment name.', 'dev');
    }

    /**
     * @{inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->symfonyCommandName = $input->hasArgument('command_name') ?
            $input->getArgument('command_name')
            :
            str_replace('project:symfony:', null, $this->getName())
        ;
        $this->symfonyCommandArguments = $this->getSymfonyCommandArguments($input);
        $this->symfonyCommandOptions = $this->getSymfonyCommandOptions($input);
    }

    /**
     * @{inheritdoc}
     */
    // protected function execute(InputInterface $input, OutputInterface $output)
    // {
    protected function executeCommandByProject($projectName, ProjectConfiguration $projectConfig, OutputInterface $output)
    {
        $commandLine = strtr(
            '%command_name% %command_arguments% %command_options%',
            [
                '%command_name%' => $this->symfonyCommandName,
                '%command_arguments%' => implode(' ', $this->symfonyCommandArguments),
                '%command_options%' => implode(' ', $this->symfonyCommandOptions),
            ]
        );

        $this->getSymfonyRemoteConsoleExec()->exec(
            $projectConfig->getRemoteSymfonyConsolePath(),
            $commandLine,
            $this->getSymfonyEnv(),
            $output->getVerbosity()
        );

        return $this->getSymfonyRemoteConsoleExec()->getLastReturnStatus();
    }
}
