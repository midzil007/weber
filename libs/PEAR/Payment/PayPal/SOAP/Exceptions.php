<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Payment_PayPal_SOAP is a package to easily use PayPal's SOAP API from PHP
 *
 * This file contains various package-specific exception class definitions.
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2008-2009 silverorange
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category  Payment
 * @package   Payment_PayPal_SOAP
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008-2009 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @version   CVS: $Id: Exceptions.php,v 1.5 2009/02/26 15:13:37 gauthierm Exp $
 * @link      http://pear.php.net/package/Payment_PayPal_SOAP
 */

/**
 * PEAR exception handler and base class
 */
require_once 'PEAR/Exception.php';

// {{{ class Payment_PayPal_SOAP_Exception

/**
 * Base class for exceptions thrown by the Payment_PayPal_SOAP package
 *
 * @category  Payment
 * @package   Payment_PayPal_SOAP
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008-2009 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @link      http://pear.php.net/package/Payment_PayPal_SOAP
 */
abstract class Payment_PayPal_SOAP_Exception extends PEAR_Exception
{
}

// }}}
// {{{ class Payment_PayPal_SOAP_InvalidModeException

/**
 * Exception thrown when an invalid mode is used in the client constructor
 *
 * @category  Payment
 * @package   Payment_PayPal_SOAP
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008-2009 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @link      http://pear.php.net/package/Payment_PayPal_SOAP
 */
class Payment_PayPal_SOAP_InvalidModeException extends
    Payment_PayPal_SOAP_Exception
{
    // {{{ private properties

    /**
     * The invalid mode that was used
     *
     * @var string
     *
     * @see Payment_PayPal_SOAP_InvalidModeException::getMode()
     */
    private $_mode = '';

    // }}}
    // {{{ __construct()

    /**
     * Creates a new invalid mode exception
     *
     * @param string  $message the exception message.
     * @param integer $code    the exception code.
     * @param string  $mode    the invalid mode that was used.
     */
    public function __construct($message, $code = 0, $mode = '')
    {
        parent::__construct($message, $code);
        $this->_mode = $mode;
    }

    // }}}
    // {{{ getMode()

    /**
     * Gets the invalid mode that was used
     *
     * @return string the invalid mode that was used.
     *
     * @see PaymentPayPal_SOAP_InvalidModeException::$_mode
     */
    public function getMode()
    {
        return $this->_mode;
    }

    // }}}
}

// }}}
// {{{ class Payment_PayPal_SOAP_InvalidRequestNameException

/**
 * Exception thrown when an invalid request name is used in the
 * {@link Payment_PayPal_SOAP_Client::call()} method
 *
 * @category  Payment
 * @package   Payment_PayPal_SOAP
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008-2009 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @link      http://pear.php.net/package/Payment_PayPal_SOAP
 */
class Payment_PayPal_SOAP_InvalidRequestNameException extends
    Payment_PayPal_SOAP_Exception
{
    // {{{ private properties

    /**
     * The invalid request name that was used
     *
     * @var string
     *
     * @see Payment_PayPal_SOAP_InvalidRequestNameException::getRequestName()
     */
    private $_requestName = '';

    // }}}
    // {{{ __construct()

    /**
     * Creates a new invalid request name exception
     *
     * @param string  $message     the exception message.
     * @param integer $code        the exception code.
     * @param string  $requestName the invalid request name that was used.
     */
    public function __construct($message, $code = 0, $requestName = '')
    {
        parent::__construct($message, $code);
        $this->_requestName = $requestName;
    }

    // }}}
    // {{{ getRequestName()

    /**
     * Gets the invalid request name that was used
     *
     * @return string the invalid request name that was used.
     *
     * @see PaymentPayPal_SOAP_InvalidRequestNameException::$_requestName
     */
    public function getRequestName()
    {
        return $this->_requestName;
    }

    // }}}
}

// }}}
// {{{ class Payment_PayPal_SOAP_MissingPropertyException

/**
 * Exception thrown when a required request property is missing in the
 * arguments parameter of a {@link Payment_PayPal_SOAP::call()} method
 * call
 *
 * @category  Payment
 * @package   Payment_PayPal_SOAP
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008-2009 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @link      http://pear.php.net/package/Payment_PayPal_SOAP
 */
class Payment_PayPal_SOAP_MissingPropertyException extends
    Payment_PayPal_SOAP_Exception
{
    // {{{ private properties

    /**
     * The name of the property that is missing
     *
     * @var string
     *
     * @see Payment_PayPal_SOAP_MissingPropertyNameException::getPropertyName()
     */
    private $_propertyName = '';

    // }}}
    // {{{ __construct()

    /**
     * Creates a new missing property exception
     *
     * @param string  $message      the exception message.
     * @param integer $code         the exception code.
     * @param string  $propertyName the name of the property that is missing.
     */
    public function __construct($message, $code = 0, $propertyName = '')
    {
        parent::__construct($message, $code);
        $this->_propertyName = $propertyName;
    }

    // }}}
    // {{{ getRequestName()

    /**
     * Gets the name of the property that is missing
     *
     * @return string the name of the property that is missing.
     *
     * @see PaymentPayPal_SOAP_MissingPropertyNameException::$_propertyName
     */
    public function getPropertyName()
    {
        return $this->_propertyName;
    }

    // }}}
}

// }}}
// {{{ class Payment_PayPal_SOAP_FaultException

/**
 * Exception thrown when a SOAP fault occurs
 *
 * @category  Payment
 * @package   Payment_PayPal_SOAP
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008-2009 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @link      http://pear.php.net/package/Payment_PayPal_SOAP
 */
class Payment_PayPal_SOAP_FaultException extends
    Payment_PayPal_SOAP_Exception
{
    // {{{ private properties

    /**
     * The original SoapFault that caused this exception to be thrown
     *
     * @var SoapFault
     * @see Payment_PayPal_SOAP_FaultException::getSoapFault()
     */
    private $_soapFault = null;

    /**
     * The SOAP client that caused the exception
     *
     * @var SoapClient
     * @see Payment_PayPal_SOAP_FaultException::getSoapClient()
     */
    private $_soapClient = null;

    // }}}
    // {{{ public function __construct()

    /**
     * Creates a new PayPal SOAP fault exception
     *
     * @param string     $message    the exception message.
     * @param integer    $code       the exception code.
     * @param SoapFault  $soapFault  the original SoapFault.
     * @param SoapClient $soapClient the SOAP client that caused the fault.
     */
    public function __construct($message, $code, SoapFault $soapFault,
        SoapClient $soapClient)
    {
        parent::__construct($message, $code);
        $this->_soapFault  = $soapFault;
        $this->_soapClient = $soapClient;
    }

    // }}}
    // {{{ getSoapFault()

    /**
     * Gets the original SoapFault that caused this exception to be thrown
     *
     * @return SoapFault the original SoapFault that caused this exception to
     *                   be thrown.
     */
    public function getSoapFault()
    {
        return $this->_soapFault;
    }

    // }}}
    // {{{ getSoapClient()

    /**
     * Gets the original SOAP client that caused the exception
     *
     * @return SoapClient the SOAP client that caused the exception.
     */
    public function getSoapClient()
    {
        return $this->_soapClient;
    }

    // }}}
}

// }}}
// {{{ class Payment_PayPal_SOAP_ErrorException

/**
 * Exception thrown when the SOAP response contains one or more Error elements
 *
 * A detailed error message is present in the message field, the PayPal error
 * code is present in the code field. The error severity is retrieved using the
 * {@link Payment_PayPal_SOAP_ErrorException::getSeverity()} method and the
 * full response object may be retrieved using the
 * {@link Payment_PayPal_SOAP_ErrorException::getResponse()} method.
 *
 * @category  Payment
 * @package   Payment_PayPal_SOAP
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008-2009 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @link      http://pear.php.net/package/Payment_PayPal_SOAP
 */
class Payment_PayPal_SOAP_ErrorException extends
    Payment_PayPal_SOAP_Exception
{
    // {{{ private properties

    /**
     * The severity of the PayPal error
     *
     * @var integer
     *
     * @see Payment_PayPal_SOAP_ErrorException::getSeverity()
     */
    private $_severity = Payment_PayPal_SOAP::ERROR_UNKNOWN;

    /**
     * The response object that contains the PayPal error
     *
     * @var stdClass
     *
     * @see Payment_PayPal_SOAP_ErrorException::getResponse()
     */
    private $_response = null;

    // }}}
    // {{{ __construct()

    /**
     * Creates a new error exception
     *
     * @param string   $message  the exception message.
     * @param integer  $code     the PayPal error code.
     * @param integer  $severity the severity of the PayPal error.
     * @param stdClass $response the response object that contains the PayPal
     *                           error.
     */
    public function __construct($message, $code, $severity, $response)
    {
        parent::__construct($message, $code);
        $this->_severity = $severity;
        $this->_response = $response;
    }

    // }}}
    // {{{ getSeverity()

    /**
     * Gets the severity of the PayPal error
     *
     * Will be one of the following:
     *
     * - {@link Payment_PayPal_SOAP::ERROR_WARNING},
     * - {@link Payment_PayPal_SOAP::ERROR_ERROR}, or
     * - {@link Payment_PayPal_SOAP::ERROR_UNKNOWN}
     *
     * @return integer the severity level of the PayPal error.
     *
     * @see PaymentPayPal_SOAP_ErrorException::$_severity
     */
    public function getSeverity()
    {
        return $this->_severity;
    }

    // }}}
    // {{{ getResponse()

    /**
     * Gets the response object containing the PayPal error
     *
     * Additional information about the error may be present here.
     *
     * @return stdClass the response object containing the PayPal error.
     *
     * @see PaymentPayPal_SOAP_ErrorException::$_response
     */
    public function getResponse()
    {
        return $this->_response;
    }

    // }}}
}

// }}}
// {{{ class Payment_PayPal_SOAP_ExpiredTokenException

/**
 * Exception thrown when a requests is made with an expired checkout token
 *
 * A detailed error message is present in the message field and the error
 * severity is present in the code field.
 *
 * @category  Payment
 * @package   Payment_PayPal_SOAP
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008-2009 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @link      http://pear.php.net/package/Payment_PayPal_SOAP
 */
class Payment_PayPal_SOAP_ExpiredTokenException extends
    Payment_PayPal_SOAP_ErrorException
{
}

// }}}

?>
