<?php

namespace go\modules\community\notes\controller;

use go\core\jmap\EntityController;
use go\modules\community\notes\model;


class Note extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass() {
		return model\Note::class;
	}
}