<?php
	class Log {
		public $file;

		public function __construct($file) {
			$this->file = $file;
		}

		public function i($str) {
			$this->log("INFO", $str);
		}

		public function d($str) {
			$this->log("DEBUG", $str);
		}

		public function w($str) {
			$this->log("WARNING", $str);
		}

		public function e($str) {
			$this->log("ERROR", $str);
		}

		public function log($level, $str) {
			$timestamp = date("d.m-Y H:i:s", time());
			file_put_contents($this->file, "[$timestamp] ($level) $str\r\n", FILE_APPEND | LOCK_EX);
		}
	}
?>
