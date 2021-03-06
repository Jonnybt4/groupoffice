<?php

namespace go\core\event;

use go\core\App;
use go\core\module\model\Module;
use go\core\Singleton;

/**
 * Contains and executes all static event listeners
 * 
 * Static event listeners can be set defined by any class that implements the
 * {@see EventListenerInterface}
 * 
 * This class is not used directly. Objects can use the {@see EventEmiterTrait} 
 * to emit events. Because we need all listeners together in one object this 
 * singleton class  holds them all.
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Listeners extends Singleton {

	private $listeners;
	
	protected function __construct() {
		parent::__construct();
		
		$this->listeners = App::get()->getCache()->get('listeners');
	}
	
	/**
	 * Add an event listener
	 * 
	 * @param int $event Defined in constants prefixed by EVENT_
	 * @param callable $fn 
	 * @return int $index Can be used for removing the listener.
	 */
	public function add($firingClass, $event, $listenerClass, $method) {
		
//		App::get()->debug(func_get_args());

		if (!isset($this->listeners[$firingClass][$event])) {
			$this->listeners[$firingClass][$event] = [];
		}
		$this->listeners[$firingClass][$event][] = [$listenerClass, $method];

		
	}

	/**
	 * Runs through all Module.php files and calls {@see \go\core\module\Base::defineListeners()}
	 * 
	 * Then stores all these listeners in the cache.
	 */
	public function init() {
		
		if (isset($this->listeners)) {			
			return;
		}

		$this->listeners = App::get()->getCache()->get('listeners');

		if (is_array($this->listeners)) {
			return;
		}

		App::get()->debug("Initializing event listeners");
		
		//create lock. If another user is already doing this we should not save to cache.
		$lock = new  \go\core\util\Lock("listeners");
		$locked = $lock->lock();

		$this->listeners = [];


		//disable events to prevent recursion
		EventEmitterTrait::$disableEvents = true;

		foreach (Module::find()->where(['enabled' => true]) as $module) { /* @var $module Module */
			
			if(!isset($module->package)) {//backwards compatibility hack. Remove once refactored.

				$file = \go\core\Environment::get()->getInstallFolder()->getPath() . '/modules/'.$module->name. '/' . ucfirst($module->name).'Module.php';
				
				if(!file_exists($file)) {
					continue;
				}
				
				require_once($file);
				
				$cls = "GO\\" . ucfirst($module->name) . "\\" . ucfirst($module->name).'Module';
				if(!class_exists($cls)) {
					continue;
				}
				if(method_exists($cls, 'defineListeners')){
					$cls::defineListeners();
				}
				continue;
			}
			
			if(!$module->isAvailable()) {
				continue;
			}						
			$module->module()->defineListeners();
		}

		//disable events to prevent recursion
		EventEmitterTrait::$disableEvents = false;
		
		if(!$locked) {
			App::get()->getCache()->set('listeners', $this->listeners);		
		}
	}

	/**
	 * Fire an event and execute all listeners
	 * 
	 * @param string $calledClass
	 * @param int $event
	 * @param mixed[] $args
	 * @return boolean
	 */
	public function fireEvent($calledClass, $traitUser, $event, $args) {	
		$this->init();
		
		if (isset($this->listeners[$calledClass][$event])) {
			foreach ($this->listeners[$calledClass][$event] as $listener) {	
				$return = call_user_func_array($listener, $args);
				if ($return === false) {
					App::get()->debug("Listener returned false for event " . $event . " " . var_export($listener, true));
					return false;
				}
			}
		}
		
		//recurse up to the parents until the class is found that uses the eventemitter trait.
		//This way you can use go\core\orm\Entity::on(EVENT_SAVE) for all entities.

		if($calledClass != $traitUser) {
			$parent = get_parent_class($calledClass);
			if($parent) {
				return $this->fireEvent($parent, $traitUser, $event, $args);
			}
		}
		return true;
	}
}