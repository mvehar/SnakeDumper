<?php

namespace Digilist\SnakeDumper\Converter;

use Faker;
use InvalidArgumentException;

/**
 * The FakerConverter replaces a value with a value generated by Faker (https://github.com/fzaninotto/Faker)
 */
class FakerConverter implements ConverterInterface
{

    /**
     * @var Faker\Generator
     */
    private $faker;

    /**
     * @var string
     */
    private $formatter;

    /**
     * @var array
     */
    private $arguments;

    /**
     * @param array  $parameters
     */
    public function __construct(array $parameters = array())
    {
        if (empty($parameters['formatter'])) {
            throw new InvalidArgumentException(
                'You have to provide the name of the formatter to use the FakerConverter.'
            );
        }

        $parameters = array_merge(array(
            'locale' => Faker\Factory::DEFAULT_LOCALE,
            'arguments' => array(),
            'unique' => false,
            'maxRetries' => 100,
        ), $parameters);

        $this->faker = Faker\Factory::create($parameters['locale']);
        $this->formatter = $parameters['formatter'];
        $this->arguments = $parameters['arguments'];

        if ($parameters['unique']) {
            $this->faker = new Faker\UniqueGenerator($this->faker, $parameters['maxRetries']);
        }
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = array())
    {
        return $this->faker->format($this->formatter, $this->arguments);
    }
}