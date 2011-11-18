<?php
/**
 * @package Zefir_Form_SubForm
 */
/**
 * @author zefiryn
 *
 */
class Zefir_Form_SubForm extends Zend_Form_SubForm
{
	
	/**
	 * vars for validators regex
	 * @var array
	 */
	protected $_regex = array(
		'L' => 'a-zA-ZÁáČčĎďÉéĚěÍíŇňÓóŘřŠšŤťÚúŮůÝýŽžÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëíìíîïñòóôõöùúûüýÿĂĄăąĆćĈĉĊċČčĎĕĘęĚěĜĝĞğĠġĢģĤĥĴĵĶķĸĹĺĻļŁłŃŅńņŇňŎŏŔŕŖŗŘřŚśŜŝŞşŠšŢţŤťŨũŬŭŮŰůűŲųŴŵŶŸŷýŹźŻżŽž',
		'N' => '0-9',
		'S' => '&\.\-_;,"”„–:!?\(\)\/\'@ ',
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
				array('ErrorMsg', array('image' => TRUE)),
				array('Description', array('tag' => 'p', 'class' => 'label', 'placement' => 'prepend'))
				
	);
	
	/**
	 * Zefir decorators
	 * @var array
	 */
	protected $_completeZefirDecorators = array(
				array('TextField'),
				array('MyLabel', array('placement' => 'prepend')),
				array('ErrorMsg', array('image' => TRUE)),
				array('UnderDescription', array('class' => 'description', 'placement' => 'append')));
				
	/**
	 * default additional decorators
	 * @var array
	 */
	protected $_basicZefirDecorators = array(
				array('TextField'),
				array('ErrorMsg', array('image' => TRUE)),
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
}