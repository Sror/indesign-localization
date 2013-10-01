<?php
/**
 * PointandGo! Localise interface language handling class
 * @author Rick Xu, Mave Rick Technology
 * @copyright 2011 StorePoint Interntional Ltd
 * @access Singleton
 * @version 0.1
 */
class language {

	protected static $instance = null;
	protected static $default_lang = 'gb';
	protected static $interface_lang = null;
	protected static $dir = null;
	protected static $lang = array();

	protected function  __construct($langCode = 'gb', $dir = LANG) {
		$langCode = trim($langCode);
		if(empty($langCode)) return false;
		self::$interface_lang = $langCode;
		self::$dir = $dir;
		$this->load();
	}

	public function  __destruct() {
		self::$instance = null;
		self::$interface_lang = null;
		self::$dir = null;
		self::$lang = array();
	}

	public static function getInstance($langCode, $dir = LANG) {
		if(empty(self::$instance)) {
			self::$instance = new language($langCode, $dir);
		}
		return self::$instance;
	}

	private function load() {
		$default_lang_file = self::$dir.'lang.'.self::$default_lang.'.php';
		if(file_exists($default_lang_file)) include($default_lang_file);
		$default_lang = $lang;
		if(self::$interface_lang != self::$default_lang) {
			$interface_lang_file = self::$dir.'lang.'.self::$interface_lang.'.php';
			if(file_exists($interface_lang_file)) include($interface_lang_file);
			$lang = array_merge($default_lang,$lang);
		}
		unset($default_lang);
		self::$lang = $lang;
	}

	public function display($key) {
		if(array_key_exists($key, self::$lang)) return self::$lang[$key];
		$value =  preg_replace('/[^\w\(\)]/i', ' ', strtolower($key));
		return ucwords($value);
	}

}