<?php
class Zefir_Form extends Zend_Form
{
	/**
	 * vars for validators regex
	 * @var array
	 */
	protected $_regex = array(
		'L' => 'a-zA-ZÁáČčĎďÉéĚěÍíŇňÓóŘřŠšŤťÚúŮůÝýŽžÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëíìíîïñòóôõöùúûüýÿĂĄăąĆćĈĉĊċČčĎĕĘęĚěĜĝĞğĠġĢģĤĥĴĵĶķĸĹĺĻļŁłŃŅńņŇňŎŏŔŕŖŗŘřŚśŜŝŞşŠšŢţŤťŨũŬŭŮŰůűŲųŴŵŶŸŷýŹźŻżŽž',
		'N' => '0-9',
		'S' => '%&\.\-_;,"”„–:!?\(\)\/\'@   ',
		'E' => '\s\n\r',
		'B' => '\[\]~\/#=+'
	);
	
	/**
	 * default decorators
	 * @var array
	 */
	protected $_standardDecorators = array(
				array('ViewHelper'),
				array('MyLabel', array('placement' => 'prepend')),
				array('ErrorMsg', array('image' => FALSE)),
				array('Description', array('tag' => 'p', 'class' => 'label', 'placement' => 'prepend'))
				
	);
	
	/**
	 * Zefir decorators
	 * @var array
	 */
	protected $_completeZefirDecorators = array(
				array('TextField'),
				array('MyLabel', array('placement' => 'prepend')),
				array('ErrorMsg', array('image' => FALSE)),
				array('UnderDescription', array('class' => 'description', 'placement' => 'append')));
				
	/**
	 * default additional decorators
	 * @var array
	 */
	protected $_basicZefirDecorators = array(
				array('TextField'),
				array('ErrorMsg', array('image' => FALSE)),
				array('Description', array('tag' => 'p', 'class' => 'label', 'placement' => 'prepend'))
				
	);
	
	/**
	 * default decorators for radio fields
	 * @var array
	 */
	protected $_decoratorsRadio = array(
				array('ViewHelper'),
				array('Description', array('tag' => 'p', 'class' => 'radio_label', 'placement' => 'prepend'))
				
	);
	
	public function init()
	{
		$this->addElementPrefixPath('Zefir_Decorator', 'Zefir/Form/Decorator', 'decorator');
		$this->addPrefixPath('Zefir_Decorator', 'Zefir/Form/Decorator', 'decorator');
	}
	
	protected function _getStandardDecorators($additional = TRUE)
	{
		return $this->_standardDecorators;
	}
	
	protected function _getZefirDecorators($full = TRUE)
	{
		if ($full)
			return $this->_completeZefirDecorators;
		else
			return $this->_basicZefirDecorators;
	}
	
	
	protected function getRadioDecorators()
	{
		return $this->_decoratorsRadio;
	}
	
	
	protected function _createStandardSubmit($submit_label)
	{
		$submit = $this->createElement('submit', 'leave', array(
			'ignore' => true,
			'label' => 'leave',
			'class' => 'submit unprefered'
		));	 
		$submit->setDecorators(array(
							array('ViewHelper')
    					));
    $this->addElement($submit);


		$submit = $this->createElement('submit', 'submit', array(
			'ignore' => true,
			'label' => $submit_label,
			'class' => 'submit prefered'
		));	 
		$submit->setDecorators(array(
							array('ViewHelper')
     				));
            				
    $this->addElement($submit);
	}
	
	/*
	* Create csrf element
	* 
	* @int $ttl time to live of the hash in seconds
	* @return Zefir_Form $this
	*/
	protected function _createCsrfElement($ttl = 1800)
	{
		$this->addElement('hash', 'csrf', array(
			'ignore' => true,
			'salt' => 'unique',
			'timeout' => $ttl,
			'decorators' => array(	array('ViewHelper'))	
		));
		
		return $this;
	}
}