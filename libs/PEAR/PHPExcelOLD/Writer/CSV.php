<?php
/**
 * PHPExcel
 *
 * Copyright (c) 2006 - 2009 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel_Writer
 * @copyright  Copyright (c) 2006 - 2009 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.6.7, 2009-04-22
 */


/** PHPExcel_IWriter */
require_once 'PHPExcel/Writer/IWriter.php';

/** PHPExcel_Cell */
require_once 'PHPExcel/Cell.php';

/** PHPExcel_RichText */
require_once 'PHPExcel/RichText.php';

/** PHPExcel_Shared_String */
require_once 'PHPExcel/Shared/String.php';


/**
 * PHPExcel_Writer_CSV
 *
 * @category   PHPExcel
 * @package    PHPExcel_Writer
 * @copyright  Copyright (c) 2006 - 2009 PHPExcel (http://www.codeplex.com/PHPExcel)
 */
class PHPExcel_Writer_CSV implements PHPExcel_Writer_IWriter {
	/**
	 * PHPExcel object
	 *
	 * @var PHPExcel
	 */
	private $_phpExcel;

	/**
	 * Delimiter
	 *
	 * @var string
	 */
	private $_delimiter;

	/**
	 * Enclosure
	 *
	 * @var string
	 */
	private $_enclosure;

	/**
	 * Line ending
	 *
	 * @var string
	 */
	private $_lineEnding;

	/**
	 * Sheet index to write
	 *
	 * @var int
	 */
	private $_sheetIndex;

	/**
	 * Pre-calculate formulas
	 *
	 * @var boolean
	 */
	private $_preCalculateFormulas = true;

	/**
	 * Whether to write a BOM (for UTF8).
	 *
	 * @var boolean
	 */
	private $_useBOM = false;

	/**
	 * Create a new PHPExcel_Writer_CSV
	 *
	 * @param 	PHPExcel	$phpExcel	PHPExcel object
	 */
	public function __construct(PHPExcel $phpExcel) {
		$this->_phpExcel 	= $phpExcel;
		$this->_delimiter 	= ',';
		$this->_enclosure 	= '"';
		$this->_lineEnding 	= PHP_EOL;
		$this->_sheetIndex 	= 0;
	}

	/**
	 * Save PHPExcel to file
	 *
	 * @param 	string 		$pFileName
	 * @throws 	Exception
	 */
	public function save($pFilename = null) {
		// Fetch sheet
		$sheet = $this->_phpExcel->getSheet($this->_sheetIndex);

		$saveArrayReturnType = PHPExcel_Calculation::getArrayReturnType();
		PHPExcel_Calculation::setArrayReturnType(PHPExcel_Calculation::RETURN_ARRAY_AS_VALUE);

		// Open file
		$fileHandle = fopen($pFilename, 'w');
		if ($fileHandle === false) {
			throw new Exception("Could not open file $pFilename for writing.");
		}

		if ($this->_useBOM) {
			// Write the UTF-8 BOM code
			fwrite($fileHandle, "\xEF\xBB\xBF");
		}

		// Convert sheet to array
		$cellsArray = $sheet->toArray('', $this->_preCalculateFormulas);

		// Write rows to file
		foreach ($cellsArray as $row) {
			$this->_writeLine($fileHandle, $row);
		}

		// Close file
		fclose($fileHandle);

		PHPExcel_Calculation::setArrayReturnType($saveArrayReturnType);
	}

	/**
	 * Get delimiter
	 *
	 * @return string
	 */
	public function getDelimiter() {
		return $this->_delimiter;
	}

	/**
	 * Set delimiter
	 *
	 * @param	string	$pValue		Delimiter, defaults to ,
	 */
	public function setDelimiter($pValue = ',') {
		$this->_delimiter = $pValue;
	}

	/**
	 * Get enclosure
	 *
	 * @return string
	 */
	public function getEnclosure() {
		return $this->_enclosure;
	}

	/**
	 * Set enclosure
	 *
	 * @param	string	$pValue		Enclosure, defaults to "
	 */
	public function setEnclosure($pValue = '"') {
		if ($pValue == '') {
			$pValue = null;
		}
		$this->_enclosure = $pValue;
	}

	/**
	 * Get line ending
	 *
	 * @return string
	 */
	public function getLineEnding() {
		return $this->_lineEnding;
	}

	/**
	 * Set line ending
	 *
	 * @param	string	$pValue		Line ending, defaults to OS line ending (PHP_EOL)
	 */
	public function setLineEnding($pValue = PHP_EOL) {
		$this->_lineEnding = $pValue;
	}

	/**
	 * Get whether BOM should be used
	 *
	 * @return boole                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  