<?php

class Eclipse_PHPUnitEclipseLogger
{
    
    private $status;
    
    private $exception;
    
    private $time;
    
    private $warnings;
    
    private $varx;
    
    /**
     * data provider support - enumerates the test cases
     */
    private $dataProviderNumerator = - 1;
    
    public function __construct()
    {
        $this->cleanTest();
        
        $port = isset($_SERVER['PHPUNIT_PORT']) ? $_SERVER['PHPUNIT_PORT'] : 7478;
        $this->out = fsockopen('127.0.0.1', $port, $errno, $errstr, 5);
    }
    
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->writeTest($suite, 'start');
    }
    
    public function startTest(PHPUnit_Framework_Test $test)
    {
        ZendPHPUnitErrorHandlerTracer::getInstance()->start($test);
        $this->cleanTest();
        $this->writeTest($test, 'start', true);
    }
    
    public function addError(PHPUnit_Framework_Test $test, $e, $time)
    {
        $this->status = 'error';
        $this->exception = $e;
    }
    
    public function addWarning(PHPUnit_Framework_Test $test, PHPUnit_Framework_Warning $e, $time)
    {
        $this->status = 'warning';
        $this->exception = $e;
    }
    
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->status = 'fail';
        $this->exception = $e;
    }
    
    public function addIncompleteTest(PHPUnit_Framework_Test $test, $e, $time)
    {
        $this->status = 'incomplete';
        $this->exception = $e;
    }
    
    public function addSkippedTest(PHPUnit_Framework_Test $test, $e, $time)
    {
        $this->status = 'skip';
        $this->exception = $e;
    }
    
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        $this->warnings = ZendPHPUnitErrorHandlerTracer::getInstance()->stop();
        $this->time = $time;
        $this->writeTest($test, $this->status);
    }
    
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->writeTest($suite, 'end');
    }
    
    public function addRiskyTest(PHPUnit_Framework_Test $test, $e, $time)
    {}
    
    public function flush()
    {}
    
    public function getWrappedTrace($e)
    {
//         if ($e->getPrevious() != null) {
//             return $this->getWrappedTrace($e->getPrevious());
//         }
//         if (class_exists('ExceptionWrapper') && $e instanceof ExceptionWrapper) {
//             return $e->getSerializableTrace();
//         }
        return $e->getTrace();
    }
    
    public function getWrappedName($e)
    {
//         if ($e->getPrevious() != null) {
//             return $this->getWrappedName($e->getPrevious());
//         }
//         if (class_exists('ExceptionWrapper') && $e instanceof ExceptionWrapper) {
//             return $e->getClassName();
//         }
        return get_class($e);
    }
    
    private function cleanTest()
    {
        $this->status = 'pass';
        $this->exception = null;
        $this->warnings = array();
        $this->time = 0;
    }
    
    private function writeArray($array)
    {
        $result = $this->writeJson($this->encodeJson($array));
        return $result;
    }
    
    private function writeTest(PHPUnit_Framework_Test $test, $event, $isTestCase = false)
    {
        // echo out test output
        if ($test instanceof PHPUnit_Framework_TestCase) {
            $hasPerformed = false;
            if (method_exists($test, 'hasPerformedExpectationsOnOutput')) {
                $hasPerformed = $test->hasPerformedExpectationsOnOutput();
            } else {
                //$hasPerformed = $test->hasExpectationOnOutput();
            }
            
//             if (! $hasPerformed && $test->getActualOutput() != null) {
//                  echo $test->getActualOutput();
//             }
        }
        
        // write log
        $result = array(
            'event' => $event
        );
        if ($test instanceof PHPUnit_Framework_TestSuite) {
            if ($isTestCase) { // skip test suite called inside startTestCase
                return;
            }
            if (preg_match("*::*", $test->getName()) != 0) { // if it is a dataprovider test suite
                // $result['target'] = 'testsuite-dataprovider';
                $result['target'] = 'testsuite';
                if ($event == 'start')
                    $this->dataProviderNumerator = 0;
                    elseif ($event == 'end')
                    $this->dataProviderNumerator = - 1;
                    
                    try {
                        $ex = explode('::', $test->getName(), 2);
                        $class = new ReflectionClass($ex[0]);
                        $name = $test->getName();
                        $file = $class->getFileName();
                        $line = $class->getStartLine();
                        $result['test'] = array(
                            'name' => $name,
                            'tests' => $test->count(),
                            'file' => $file,
                            'line' => $line
                        );
                        
                        $method = $class->getMethod($ex[1]);
                        $result['test']['line'] = $method->getStartLine();
                    } catch (ReflectionException $re) {
                        $name = $test->getName();
                        $result['test'] = array(
                            'name' => $name,
                            'tests' => $test->count()
                        );
                    }
            } else {
                $result['target'] = 'testsuite';
                $this->dataProviderNumerator = - 1;
                try {
                    $class = new ReflectionClass($test->getName());
                    $name = $class->getName();
                    $file = $class->getFileName();
                    $line = $class->getStartLine();
                    $result['test'] = array(
                        'name' => $name,
                        'tests' => $test->count(),
                        'file' => $file,
                        'line' => $line
                    );
                } catch (ReflectionException $re) {
                    $name = $test->getName();
                    $result['test'] = array(
                        'name' => $name,
                        'tests' => $test->count()
                    );
                }
            }
        } else { // If we're dealing with TestCase
            $result['target'] = 'testcase';
            $result['time'] = $this->time;
            $class = new ReflectionClass($test);
            try {
                $method = $class->getMethod($test->getName());
                if ($this->dataProviderNumerator < 0) {
                    $method_name = $method->getName();
                } else {
                    $method_name = $method->getName() . "[" . $this->dataProviderNumerator . "]";
                    if ($event == 'start') {
                        $this->dataProviderNumerator ++;
                    }
                }
                $result['test'] = array(
                    'name' => $method_name,
                    'file' => $method->getFileName(),
                    'line' => $method->getStartLine()
                );
            } catch (ReflectionException $re) {
                $result['test'] = array(
                    'name' => $test->getName()
                );
            }
        }
        if ($this->exception !== null) {
            $message = $this->exception->getMessage();
            $diff = "";
            if ($this->exception instanceof PHPUnit_Framework_ExpectationFailedException) {
                if (method_exists($this->exception, "getDescription")) {
                    $message = $this->exception->getDescription();
                } else if (method_exists($this->exception, "getMessage")) { // PHPUnit 3.6.3
                    $message = $this->exception->getMessage();
                }
                if (method_exists($this->exception, "getComparisonFailure") && method_exists($this->exception->getComparisonFailure(), "getDiff")) {
                    $diff = $this->exception->getComparisonFailure()->getDiff();
                }
            }
            $message = trim(preg_replace('/\s+/m', ' ', $message));
            $result += array(
                'exception' => array(
                    'message' => $message,
                    'diff' => $diff,
                    'class' => $this->getWrappedName($this->exception),
                    'file' => $this->exception->getFile(),
                    'line' => $this->exception->getLine(),
                    'trace' => filterTrace($this->getWrappedTrace($this->exception))
                )
            );
            if (! isset($result['exception']['file'])) {
                $result['exception']['filtered'] = true;
            }
        }
        if (! empty($this->warnings)) {
            $result += array(
                'warnings' => $this->warnings
            );
        }
        if (! $this->writeArray($result)) {
            die();
        }
    }
    
    private function writeJson($buffer)
    {
        if ($this->out && ! @feof($this->out)) {
            return @fwrite($this->out, "$buffer\n");
        }
    }
    
    private function escapeString($string)
    {
        return str_replace(array(
            "\\",
            "\"",
            '/',
            "\b",
            "\f",
            "\n",
            "\r",
            "\t"
        ), array(
            '\\\\',
            '\"',
            '\/',
            '\b',
            '\f',
            '\n',
            '\r',
            '\t'
        ), $string);
    }
    
    private function encodeJson($array)
    {
        $result = '';
        if (is_scalar($array))
            $array = array(
                $array
            );
            $first = true;
            foreach ($array as $key => $value) {
                if (! $first)
                    $result .= ',';
                    else
                        $first = false;
                        $result .= sprintf('"%s":', $this->escapeString($key));
                        if (is_array($value) || is_object($value))
                            $result .= sprintf('%s', $this->encodeJson($value));
                            else
                                $result .= sprintf('"%s"', $this->escapeString($value));
            }
            return '{' . $result . '}';
    }
}

