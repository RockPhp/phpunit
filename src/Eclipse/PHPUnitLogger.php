<?php

/*******************************************************************************
 * Copyright (c) 2017 Rogue Wave Software Inc. and others.
 *
 * This program and the accompanying materials are made
 * available under the terms of the Eclipse Public License 2.0
 * which is available at https://www.eclipse.org/legal/epl-2.0/
 *
 * SPDX-License-Identifier: EPL-2.0
 *
 * Contributors:
 *  Rogue Wave Software Inc. - initial implementation
 *******************************************************************************/
class Eclipse_PHPUnitLogger extends PHPUnit_TextUI_ResultPrinter implements PHPUnit_Framework_TestListener
{

    protected $loggers = array();

    public function __construct($out = null)
    {
        parent::__construct('php://stdout', true);
        if (class_exists('PHPUnit_Framework_ExceptionWrapper')) {
            class_alias('PHPUnit_Framework_ExceptionWrapper', 'ExceptionWrapper');
        }
        $this->loggers = array(
            new Eclipse_PHPUnitEclipseLogger()
        );
    }

    public function setAutoFlush($autoFlush)
    {
        parent::setAutoFlush($autoFlush);
        foreach ($this->loggers as $logger) {
            $logger->setAutoFlush($autoFlush);
        }
    }

    public function flush()
    {
        parent::flush();
        foreach ($this->loggers as $logger) {
            $logger->flush();
        }
    }

    public function incrementalFlush()
    {
        parent::incrementalFlush();
        foreach ($this->loggers as $logger) {
            $logger->incrementalFlush();
        }
    }

    public function addError(PHPUnit_Framework_Test $test, Exception $t, $time)
    {
        parent::addError($test, $t, $time);
        foreach ($this->loggers as $logger) {
            $logger->addError($test, $t, $time);
        }
    }

    public function addWarning(PHPUnit_Framework_Test $test, PHPUnit_Framework_Warning $e, $time)
    {
        parent::addWarning($test, $e, $time);
        foreach ($this->loggers as $logger) {
            $logger->addWarning($test, $e, $time);
        }
    }

    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        parent::addFailure($test, $e, $time);
        foreach ($this->loggers as $logger) {
            $logger->addFailure($test, $e, $time);
        }
    }

    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $t, $time)
    {
        parent::addIncompleteTest($test, $t, $time);
        foreach ($this->loggers as $logger) {
            $logger->addIncompleteTest($test, $t, $time);
        }
    }

    public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $t, $time)
    {
        parent::addRiskyTest($test, $t, $time);
        foreach ($this->loggers as $logger) {
            $logger->addRiskyTest($test, $t, $time);
        }
    }

    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $t, $time)
    {
        parent::addSkippedTest($test, $t, $time);
        foreach ($this->loggers as $logger) {
            $logger->addSkippedTest($test, $t, $time);
        }
    }

    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        parent::startTestSuite($suite);
        foreach ($this->loggers as $logger) {
            $logger->startTestSuite($suite);
        }
    }

    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        parent::endTestSuite($suite);
        foreach ($this->loggers as $logger) {
            $logger->endTestSuite($suite);
        }
    }

    public function startTest(PHPUnit_Framework_Test $test)
    {
        parent::startTest($test);

        foreach ($this->loggers as $logger) {
            $logger->startTest($test);
        }
    }

    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        parent::endTest($test, $time);
        foreach ($this->loggers as $logger) {
            $logger->endTest($test, $time);
        }
    }
}



class ZendPHPUnitErrorHandlerTracer extends ZendPHPUnitErrorHandler
{

    private static $ZendPHPUnitErrorHandlerTracer;

    /**
     *
     * @return ZendPHPUnitErrorHandlerTracer
     */
    public static function getInstance()
    {
        if (self::$ZendPHPUnitErrorHandlerTracer === null) {
            self::$ZendPHPUnitErrorHandlerTracer = new self();
        }
        return self::$ZendPHPUnitErrorHandlerTracer;
    }

    public static $errorCodes = array(
        E_ERROR => 'Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parsing Error',
        E_NOTICE => 'Notice',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Runtime Notice'
//         E_RECOVERABLE_ERROR => 'Recoverable Error',
//         E_DEPRECATED => 'Deprecated',
//         E_USER_DEPRECATED => 'User Deprecated'
    );

    protected $warnings;

    public function handle($errno, $errstr, $errfile, $errline)
    {
        parent::handle($errno, $errstr, $errfile, $errline);
        $warning = array(
            'code' => isset(self::$errorCodes[$errno]) ? self::$errorCodes[$errno] : $errno,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'trace' => filterTrace(debug_backtrace()),
            'time' => PHPUnit_Util_Timer::resourceUsage()
        );
        $return = false;
        switch ($errno) { // ignoring user abort
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
                throw new Eclipse_ZendPHPUnitUserErrorException($warning['message'], $errno);
        }
        $this->warnings[] = $warning;
        return $return;
    }

    public function start(PHPUnit_Framework_Test $test)
    {
        $this->warnings = array();
        parent::start($test);
    }

    public function stop()
    {
        parent::stop();
        $return = $this->warnings;
        $this->warnings = array();
        return $return;
    }
}

class ZendPHPUnitErrorHandler
{

    private static $ZendPHPUnitErrorHandler;

    private $convertErrors = false;

    private $convertNotices = false;

    private $convertDeprecations = false;

    private $convertWarnings = false;

    private $test;

    /**
     *
     * @return ZendPHPUnitErrorHandler
     */
    public static function getInstance()
    {
        if (self::$ZendPHPUnitErrorHandler === null) {
            self::$ZendPHPUnitErrorHandler = new self();
        }
        return self::$ZendPHPUnitErrorHandler;
    }

    public function handle($errno, $errstr, $errfile, $errline)
    {
        print_r($errno);
        print_r($errstr);
        print_r($errfile);
        print_r($errline);
        return false;
        if (! ($errno & error_reporting())) {
            return false;
        }
        // handle errors same as PHPUnit_Util_ErrorHandler
        if ($errfile === __FILE__ || (stripos($errfile, dirname(dirname(__FILE__))) === 0 && $errno !== E_USER_NOTICE)) {
            return true;
        } elseif ($errno === E_NOTICE || $errno === E_USER_NOTICE || $errno === E_STRICT) {
            if (! $this->convertNotices) {
                return false;
            }
            $exception = 'PHPUnit_Framework_Error_Notice';
        } elseif ($errno == E_WARNING || $errno == E_USER_WARNING) {
            if (! $this->convertWarnings) {
                return FALSE;
            }

            $exception = 'PHPUnit_Framework_Error_Warning';
        } elseif ($errno == E_NOTICE) {
            return FALSE;
        } elseif ($errno === E_DEPRECATED || $errno === E_USER_DEPRECATED) {
            if (! $this->convertDeprecations) {
                return false;
            }
            $exception = 'PHPUnit_Framework_Error_Deprecated';
        } else {
            if (! $this->convertErrors) {
                return false;
            }
            $exception = 'PHPUnit_Framework_Error_Error';
        }

        throw new $exception($errstr, $errno, $errfile, $errline);
    }

    public function start(PHPUnit_Framework_Test $test)
    {
        $this->test = $test;
        set_error_handler(array(
            &$this,
            'handle'
        ));
        $ref = new ReflectionClass('PHPUnit_Framework_Warning');
        if ($ref->hasProperty('enabled')) {
            $this->convertErrors = true;
            $this->convertWarnings = PHPUnit_Framework_Warning::$enabled === true;
            $this->convertNotices = PHPUnit_Framework_Warning::$enabled === true;
            $this->convertDeprecations = PHPUnit_Framework_Warning::$enabled === true;
        } 
//         elseif ($test->getTestResultObject() != null) {
//             $this->convertErrors = $test->getTestResultObject()->getConvertErrorsToExceptions();
//             $this->convertWarnings = $test->getTestResultObject()->getConvertWarningsToExceptions();
//             $this->convertNotices = $test->getTestResultObject()->getConvertNoticesToExceptions();
//             $this->convertDeprecations = $test->getTestResultObject()->getConvertDeprecationsToExceptions();
//         }
    }

    public function stop()
    {
        $this->test = null;
        restore_error_handler();
    }
}

function filterTrace($trace)
{
    $filteredTrace = array();

    $blacklist = null;
    if (class_exists('Blacklist')) {
        //$blacklist = new Blacklist();
    }
    $prefix = false;
    if (defined('__PHPUNIT_PHAR_ROOT__')) {
        $prefix = __PHPUNIT_PHAR_ROOT__;
    }
    $script = realpath($GLOBALS['_SERVER']['SCRIPT_NAME']);
    foreach ($trace as $frame) {
        if (! isset($frame['file'])) {
            if (isset($frame['class']) && isset($frame['function'])) {
                try {
                    $class = new ReflectionClass($frame['class']);
                    $frame['file'] = $class->getFileName();
                    $method = $class->getMethod($frame['function']);
                    if (! isset($frame['line'])) {
                        $frame['line'] = $method->getStartLine();
                    }
                } catch (ReflectionException $re) {
                    continue;
                }
            }
        }
        if (($blacklist && $blacklist->isBlacklisted($frame['file'])) || ! ($prefix === false || strpos($frame['file'], $prefix) !== 0) || ! is_file($frame['file']) || $frame['file'] === $script) {
            continue;
        }
        $filteredFrame = array(
            'file' => $frame['file'],
            'line' => $frame['line'],
            'function' => $frame['function']
        );
        if (isset($frame['class'])) {
            $filteredFrame += array(
                'class' => $frame['class'],
                'type' => $frame['type']
            );
            $filteredTrace[] = $filteredFrame;
        }
    }
    return $filteredTrace;
}