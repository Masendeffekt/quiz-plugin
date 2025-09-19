<?php
/**
 * Modules manager
 *
 * @package WPQuiz
 */

namespace WPQuiz;

/**
 * Class ModulesManager
 */
class ModuleManager {

	/**
	 * List of modules.
	 *
	 * @var array
	 */
	protected $_modules = array();

	/**
	 * Adds module.
	 *
	 * @param Module $module Module object.
	 */
	public function add( Module $module ) {
		if ( $module->get_id() ) {
			$module->init();
			$this->_modules[ $module->get_id() ] = $module;
		}
	}

	/**
	 * Gets module.
	 *
	 * @param string $module_id Module ID.
	 * @return false|Module
	 */
	public function get( $module_id ) {
		if ( ! isset( $this->_modules[ $module_id ] ) ) {
			return false;
		}
		return $this->_modules[ $module_id ];
	}
}
