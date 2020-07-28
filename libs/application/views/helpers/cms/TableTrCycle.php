<?php
/**
 * CMS
 * *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 */


class Cms_View_Helper_TableTrCycle extends Zend_View_Helper_FormElement
{
	private $_cycles = array();

	public function tableTrCycle()
	{
		if (!$cycle = func_get_args()) {
			return;
		}
		if (!in_array($cycle, $this->_cycles)) {
			$this->_cycles[] = $cycle;
		}
		$key = array_search($cycle, $this->_cycles);
		$current = current($this->_cycles[$key]);
		if (!next($this->_cycles[$key])) {
			reset($this->_cycles[$key]);
		}
		return $current;
	}
	
	public function resetCycle(){
		reset($this->_cycles);
	}
}
