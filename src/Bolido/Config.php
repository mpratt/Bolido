<?php
/**
 * Config.php
 *
 * @package Bolido
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bolido;

/**
 * Configuration object
 */
class Config implements \ArrayAccess
{
    /** @var array containing the structure */
    protected $config = array();

    /**
     * Construct
     *
     * @param array $config
     * @return void
     */
    public function __construct(array $config = array())
    {
        $config = array_merge(array(
            'exclude' => array(),
            'source_dir' => '',
            'output_dir' => '',
            'layout_dir' => '{source_dir}/layouts',
            'plugin_dir' => '{source_dir}/plugins',
            'url_prefix' => '',
            'compile_less' => true,
            'extended_markdown' => false,
        ), $config);

        $this->config = $this->validate($config);
    }

    /**
     * Validates and normalizes the directories
     *
     * @param array $config
     * @return array with normalized data
     *
     * @throws InvalidArgumentException when the Source dir is not a directory
     * @throws InvalidArgumentException when the Output dir is not valid
     * @throws InvalidArgumentException when the Layout and plugin dirs are not available
     */
    protected function validate(array $config)
    {
        if (!is_dir($config['source_dir'])) {
            throw new \InvalidArgumentException(
                sprintf('The source dir "%s" does not exist or is not a valid directory', $config['source_dir'])
            );
        } else {
            $config['source_dir'] = realpath(rtrim($config['source_dir'], '/ '));
        }

        // If the file starts with .., prefix the source_dir
        $config['output_dir'] = preg_replace('~^\.\.~', dirname($config['source_dir']), $config['output_dir']);
        if (!is_dir($config['output_dir']) || !is_writable($config['output_dir'])) {
            throw new \InvalidArgumentException(
                sprintf('The output dir "%s" does not exist or is not writable', $config['output_dir'])
            );
        }

        // Normalize the source_dir
        $return = array();
        foreach ($config as $key => $value) {
            if (preg_match('~_dir$~i', $key)) {
                $value = str_replace('{source_dir}', $config['source_dir'], rtrim($value, '/ '));
                if (!is_dir($value)) {
                    throw new \InvalidArgumentException(
                        sprintf('The %s directive "%s" is not a directory', $key, $value)
                    );
                }

                $value = realpath($value);
            }

            if ($key == 'url_prefix') {
                $value = rtrim($value, '/ ');
            }

            $return[$key] = $value;
        }

        return $return;
    }

    /**
     * Required by the ArrayAccess interface
     * Since setting a new directive directly is disabled,
     * an exception is always thrown.... always..
     *
     * @param string $offset
     * @param mixed $value
     * @return void
     *
     * @throws InvalidArgumentException always.
     */
    public function offsetSet($offset, $value)
    {
        throw new \InvalidArgumentException(
            sprintf('You cannot set the configuration directive "%s" to "%s" directly', $offset, $value)
        );
    }

    /**
     * Required by the ArrayAccess interface
     * Checks if a directive exists
     *
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return (isset($this->config[$offset]));
    }

    /**
     * Required by the ArrayAccess interface
     * Checks if a directive exists
     *
     * @param string $offset
     * @return bool
     */
    public function offsetUnset($offset)
    {
        unset($this->config[$offset]);
    }

    /**
     * Required by the ArrayAccess interface
     * Returns the config directive
     *
     * @param string $offset
     * @return mixed
     * @throws InvalidArgumentException when the $offset doesnt exist
     */
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->config[$offset];
        }

        throw new \InvalidArgumentException(
            sprintf('The  configuration key "%s" doesnt exist', $offset)
        );
    }
}
?>
