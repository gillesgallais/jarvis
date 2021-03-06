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

namespace Jarvis\Command\VirtualMachine;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;

class DisablePhpExtensionCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Disables PHP extension in virtual machine');

        $this->addArgument(
            'name',
            InputArgument::REQUIRED,
            'Extension name'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getSshExec()->run(sprintf('sudo php5dismod %s', $input->getArgument('name')), $output);

        $this->getApplication()->executeCommand('vm:service:php-fpm:restart', [], $output);

        if ($this->getSshExec()->getLastReturnStatus() == 0) { // EXIT_SUCCESS
            $output->writeln(sprintf(
                '<info>PHP extension "%s" is disabled in virtual machine</info>',
                $input->getArgument('name')
            ));
        }

        return $this->getSshExec()->getLastReturnStatus();
    }
}
