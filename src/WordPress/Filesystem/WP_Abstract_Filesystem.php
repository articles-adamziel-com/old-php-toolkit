<?php

namespace WordPress\Filesystem;

/**
 * Abstract class for filesystem implementations.
 * 
 * It enables navigating multiple filesystem implementations in a unified way.
 * For example, WP_Zip_Filesystem and WP_Local_Filesystem are both implemented
 * as subclasses of this class.
 */
abstract class WP_Abstract_Filesystem {

	/**
	 * List the contents of a directory.
	 * 
	 * @param string $parent The path to the parent directory.
	 * @return array<string> The contents of the directory.
	 */
	abstract public function ls($parent = '/');

	/**
	 * Check if a path is a directory.
	 * 
	 * @param string $path The path to check.
	 * @return bool True if the path is a directory, false otherwise.
	 */
	abstract public function is_dir($path);

	/**
	 * Check if a path is a file.
	 * 
	 * @param string $path The path to check.
	 * @return bool True if the path is a file, false otherwise.
	 */
	abstract public function is_file($path);

	/**
	 * Start streaming a file.
	 * 
	 * @example
	 * 
	 * $fs->open_file_stream($path);
	 * while($fs->next_file_chunk()) {
	 *     $chunk = $fs->get_file_chunk();
	 *     // process $chunk
	 * }
	 * $fs->close_file_stream();
	 * 
	 * @param string $path The path to the file.
	 */
	abstract public function open_file_stream($path);

	/**
	 * Get the next chunk of a file.
	 * 
	 * @return string|false The next chunk of the file or false if the end of the file is reached.
	 */
	abstract public function next_file_chunk();

	/**
	 * Get the current chunk of a file.
	 * 
	 * @return string|false The current chunk of the file or false if no chunk is available.
	 */
	abstract public function get_file_chunk();

    /**
     * Get the length of the streamed file.
     * 
     * @return int|false The length of the file or false if the file is not streamed.
     */
    abstract public function get_streamed_file_length();

	/**
	 * Get the error message of the filesystem.
	 * 
	 * @return string|false The error message or false if no error occurred.
	 */
	abstract public function get_error_message();

	/**
	 * Close the file reader.
	 */
	abstract public function close_file_stream();

	// @TODO: Support for write methods, perhaps in a separate interface?
	// abstract public function append_to($path, $data);
	// abstract public function overwrite($path, $data);
	// abstract public function rename($old_path, $new_path);
	// abstract public function mkdir($path);
	// abstract public function rm($path);
	// abstract public function rmdir($path, $options = []);


	/**
	 * Buffers the entire contents of a file into a string
	 * and returns it.
	 * 
	 * @param string $path The path to the file.
	 * @return string|false The contents of the file or false if the file does not exist.
	 */
	public function get_contents($path) {
		$this->open_file_stream($path);
		$body = '';
		while($this->next_file_chunk()) {
			$chunk = $this->get_file_chunk();
			if($chunk === false) {
				return false;
			}
			$body .= $chunk;
		}
		$this->close_file_stream();
		return $body;
	}

}
