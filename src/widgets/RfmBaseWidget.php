<?php 

namespace AlexNet\TinyFileMan\Widgets;

use Yii;

class RfmBaseWidget extends \yii\base\Widget
{
	/**
	 * ссылка на модуль .. 
	 * @var Module
	 */
	protected $inst;

	/**
	 * ключик конфигурации 
	 * string
	 */
	protected $confKey; 

	/**
	 * доп настройки для доступа по ftp ... 
	 * массив с полями из config .. начинающиеся с ftp_ ... 
	 */
	public $confFtp = [];

	/**
	 * настройки зависящие от адреса расположения файлового манагера ... (один элемент (ключ) из baseRFMUrls  модуля.. с подстановкой параметров если надо .. Аналогично  \yii\helpers\Url::to()
	 * @var array
	 */
	public $for;

	/**
	 * использовать относительные ссылки 
	 * @var bool
	 */
	public $relativeUrl = true;

	/**
	 * Класс модального окна . ... можно пользовать разный bootstrap  
	 * @var string
	 */
	public $bootstrapModelClass;


	/**
	 * параметр определящий настройку диз urls модуля .. 
	 * @var string
	 */
	protected $elid;

	protected $urlParams = [];

	public function init()
	{
		parent::init();
		// экземпляр модуля ... 
		$this->inst = \AlexNet\TinyFileMan\FileManMod::getInstance();

		// назначение уникального идентификатора . для поля 
		//$this->elid=md5('text-area-witch-tiny-'.$this->id);
		$elid = [];
		if (!empty($this->inst)) {
			$elid[] = $this->inst->id;
			if (!$this->bootstrapModelClass && $this->inst->bootstrapModelClass) {
				$this->bootstrapModelClass = $this->inst->bootstrapModelClass;
			}
		}
		$elid[] = $this->id;

		$this->elid = 'rfm--' . implode('--', $elid);

		// если мы работаем с редактором то .. .. 
		if (!empty($this->for)) {
			$this->urlParams = $this->for;
			$this->confKey = array_shift($this->urlParams);
			$this->urlParams['elid'] = md5($this->elid);

			// провека наличия определённого ключа в настройках модуля 
			if (empty($this->inst->baseRFMUrls[$this->confKey])) {
				throw new \yii\base\InvalidConfigException("Нет подходящего пути");
			}
		}
	}

	public function afterRun($rea)
	{
		$this->saveConfigToSessi();
		return parent::afterRun($rea);
	}

	/**
	 * забрать настройки  для конкретного пути .. 
	 * @return [type] [description]
	 */
	public function getPlaceholderConfig()
	{
		return $this->inst->baseRFMUrls[$this->confKey] ?? [];
	}

	/**
	 * проверка доступа к виджету ... 
	 * @return [type] [description]
	 */
	public function checkAccess()
	{
		if (empty($this->inst->baseRFMUrls[$this->confKey]['perms'])) {
			return true;
		}

		$access = true;
		for ($i = 0; $i < count($this->inst->baseRFMUrls[$this->confKey]['perms']); $i++) {
			$p = $this->inst->baseRFMUrls[$this->confKey]['perms'][$i];
			$access = $access && ($p == '@' && !Yii::$app->user->isGuest || Yii::$app->user->can($p));
		}
		return $access;
	}
	/**
	 * сохранение настроек в сессию
	 * @param  array $data Масств настроек
	 * @return [type]       [description]
	 */
	protected function saveConfigToSessi($data = [])
	{
		// добавляем ключик .. 
		$data['filemanKey'] = empty($this->confKey) ? '' : $this->confKey;
		// сохраняем подстроечные данные для файлового менеджера..
		foreach ($this->confFtp as $x => $y) {
			$data['fileManConf']['ftp_' . $x] = $y;
		}
		// проброс настроеек редактора . если используем редактор ..
		if ($this->hasProperty('editorConfig')) {
			$data['editor'] = $this->editorConfig;
		}

		$confArr = Yii::$app->session->get('file-man-rfm', []);
		$confArr[md5($this->elid)] = $data;
		Yii::$app->session->set('file-man-rfm', $confArr);
	}

	/**
	 * создание ссылки для iframe
	 * @return [type] [description]
	 */
	protected function createUrlToManager()
	{
		$iframeurl = $this->urlParams;
		array_unshift($iframeurl, '/' . $this->inst->id . '/file-man/dialog');
		// если надо сделать относительные ссылки ..
		if ($this->relativeUrl) {
			$iframeurl['relative_url'] = 1;
		}
		return $iframeurl;
	}

	public function beforeRun()
	{
		if (!parent::beforeRun()) {
			return false;
		}

		return $this->checkAccess();
	}
}
