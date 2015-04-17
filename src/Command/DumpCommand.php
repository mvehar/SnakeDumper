<?php

namespace Digilist\SnakeDumper\Command;

use Digilist\SnakeDumper\Configuration\DumperConfigurationInterface;
use Digilist\SnakeDumper\Configuration\SnakeConfiguration;
use Digilist\SnakeDumper\Configuration\SnakeConfigurationTree;
use Digilist\SnakeDumper\Converter\Service\SqlConverterService;
use Digilist\SnakeDumper\Dumper\DumperInterface;
use Digilist\SnakeDumper\Output\GzipStreamOutput;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Yaml\Yaml;

class DumpCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('dump')
            ->addOption('config', null, InputOption::VALUE_REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->parseConfig($input);

        /** @var DumperInterface $dumper */
        $class = $config->getFullQualifiedDumper();
        $dumper = new $class();

        $dumper->setConverterService(SqlConverterService::createFromConfig($config));
        $dumper->dump($config, $this->getOutputStream($config));
    }

    private function parseConfig(InputInterface $input)
    {
        $configFile = realpath(sprintf('%s/%s', getcwd(), $input->getOption('config')));
        if (!$configFile) {
            throw new \InvalidArgumentException('Cannot find configuration file: ' . $input->getOption('config'));
        }

        $config = Yaml::parse($configFile);

        $processor = new Processor();
        $configuration = new SnakeConfigurationTree();
        $processed = $processor->processConfiguration($configuration, array($config));

        return new SnakeConfiguration($processed);
    }

    /**
     * Returns the target output interface for the dump.
     *
     * @param DumperConfigurationInterface $config
     *
     * @return Output
     */
    private function getOutputStream(DumperConfigurationInterface $config)
    {
        if ($config->getOutput()->getGzip()) {
            return new GzipStreamOutput(gzopen($config->getOutput()->getFile(), 'w'));
        }

        return new StreamOutput(fopen($config->getOutput()->getFile(), 'w'));
    }
}
