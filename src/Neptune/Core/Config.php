<?php

namespace Neptune\Core;

use Neptune\Exceptions\ConfigKeyException;
use Neptune\Exceptions\ConfigFileException;

use Crutches\DotArray;

/**
 * Config
 * @author Glynn Forrest <me@glynnforrest.com>
 */
class Config {

	protected $name;
	protected $filename;
	protected $modified = false;
	protected $dot_array;
    protected $original;

	public function __construct($name, $filename = null) {
		if($filename) {
			if(!file_exists($filename)) {
				throw new ConfigFileException(
					'Configuration file ' . $filename . ' not found');
			}
			$values = include $filename;
			if (!is_array($values)) {
				throw new ConfigFileException(
					'Configuration file ' . $filename . ' does not return a php array');
			}
			$this->filename = $filename;
		} else {
			$values = array();
		}
		$this->name = $name;
		$this->dot_array = new DotArray($values);
        $this->original = new DotArray($values);
		return true;
	}

	/**
	 * Get a configuration value that matches $key.
	 * $key uses the dot array syntax: parent.child.child
	 * If the key matches an array the whole array will be returned.
	 * If no key is specified the entire configuration array will be
	 * returned.
	 * $default will be returned (null unless specified) if the key is
	 * not found.
	 */
	public function get($key = null, $default = null) {
		return $this->dot_array->get($key, $default);
	}

	/**
	 * Get the first value from an array of configuration values that
	 * matches $key.
	 * $default will be returned (null unless specified) if the key is
	 * not found or does not contain an array.
	 */
	public function getFirst($key = null, $default = null) {
		return $this->dot_array->getFirst($key, $default);
	}

	/**
	 * Get a configuration value that matches $key in the same way as
	 * get(), but a ConfigKeyException will be thrown if
	 * the key is not found.
	 */
	public function getRequired($key) {
		$value = $this->get($key);
		if (null !== $value) {
			return $value;
		}
		throw new ConfigKeyException("Required value not found in Config instance '$this->name': $key");
	}

	/**
	 * Get a directory path from the configuration value that matches
	 * $key. The value will be added to dir.root to form a complete
	 * directory path. If the value begins with a slash it will be
	 * treated as an absolute path and returned explicitly. A
	 * ConfigKeyException will be thrown if the path can't be resolved.
	 *
	 * @param string $key The key in the config file
	 */
	public function getPath($key) {
		$path = $this->getRequired($key);
		if(substr($path, 0, 1) === '/') {
			return $path;
		}
        if (!$this->root_dir) {
            throw new \Exception('Root directory has not been set');
        }

		return $this->root_dir . $path;
	}

	/**
	 * Get a directory path from the configuration value that matches
	 * $key. The value will be added to the directory of this module
	 * to form a complete directory path. If the value begins
	 * with a slash it will be treated as an absolute path and
	 * returned explicitly. If this config instance is
	 * 'neptune', the result will be the same as getPath(). A
	 * ConfigKeyException will be thrown if the path can't be
	 * resolved.
	 *
	 * @param string $key The key in the config file
	 */
	public function getRelativePath($key) {
        $path = $this->getRequired($key);
        if(substr($path, 0, 1) === '/') {
            return $path;
        }
        return dirname($this->filename) . '/' . $path;
	}

	/**
	 * Get the first value from an array of configuration values that
	 * matches $key in the same way as getFirst(), but a
	 * ConfigKeyException will be thrown if the key is not found.
	 */
	public function getFirstRequired($key) {
		$value = $this->dot_array->getFirst($key);
		if ($value) {
			return $value;
		}
		throw new ConfigKeyException("Required first value not found in Config instance '$this->name': $key");
	}

	/**
	 * Set a configuration value with $key.
	 * $key uses the dot array syntax: parent.child.child.
	 * If $value is an array this will also be accessible using the
	 * dot array syntax.
	 */
	public function set($key, $value) {
		$this->dot_array->set($key, $value);
		$this->original->set($key, $value);
		$this->modified = true;
        return $this;
	}

	/**
	 * Override values in this Config instance with values from
	 * $array. They will not be included in toString() or save().
	 */
	public function override(array $array) {
		$this->dot_array->merge($array);
	}

    /**
     * Get the current configuration as a string, as it would appear
     * when saved to a file.
     *
     * @return string The current configuration
     */
    public function toString()
    {
        return '<?php return ' . var_export($this->original->get(), true) . '?>';
    }

	/**
	 * Save the current configuration instance.
	 * A ConfigFileException will be thrown if filename is not set or
	 * if php can't write to the file.
	 */
	public function save() {
		$values = $this->dot_array->get();
		if (false === $this->modified) {
			return true;
		}
		if(!$this->filename) {
			throw new ConfigFileException(
				"Unable to save Config instance '$this->name', \$filename is not set"
			);
		}
		if(!file_exists($this->filename) && !@touch($this->filename)){
			throw new ConfigFileException(
				"Unable to create configuration file
						$this->filename. Check file paths and permissions
						are correct."
			);
		};
		if(!is_writable($this->filename)) {
			throw new ConfigFileException(
				"Unable to write to configuration file
						$this->filename. Check file paths and permissions
						are correct."
			);
		}
		$content = $this->toString();
		file_put_contents($this->filename, $content);
		return true;
	}

	/**
	 * Set the filename for the current configuration instance.
	 *
	 * @param string $filename The name of the file
	 */
	public function setFileName($filename) {
		$this->filename = $filename;
	}

	/**
	 * Get the filename of the current configuration instance.
	 */
	public function getFileName() {
		return $this->filename;
	}

    public function setRootDirectory($directory)
    {
        $this->root_dir = $directory;
        return $this;
    }

}
