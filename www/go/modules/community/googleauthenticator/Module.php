<?php
namespace go\modules\community\googleauthenticator;

use go\core\auth\model\User;
use go\core\module\Base;
use go\core\orm\Mapping;
use go\core\orm\Property;
use go\modules\community\googleauthenticator\model;

class Module extends Base {

	public function getAuthor() {
		return "Intermesh BV";
	}
	
	public function defineListeners() {
		User::on(Property::EVENT_MAPPING, static::class, 'onMap');
	}
	
	protected function afterInstall() {
		
		if(!Googleauthenticator::register()) {
			return false;
		}
		
		return parent::afterInstall();
	}
	
	public static function onMap(Mapping $mapping) {		
		$mapping->addRelation("googleauthenticator", model\Googleauthenticator::class, ['id' => 'userId'], false);		
		return true;
	}

}