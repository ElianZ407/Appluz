<?php
class Logger {
    private static $instance = null;
    private $logFile;
    private $logLevel; // DEBUG, INFO, WARNING, ERROR, CRITICAL
    private $colors = [
        'DEBUG' => "\033[36m",   
        'INFO' => "\033[32m",     
        'WARNING' => "\033[33m",  
        'ERROR' => "\033[31m",    
        'CRITICAL' => "\033[35m", 
        'RESET' => "\033[0m"      
    ];

    private function __construct() {
        $this->logFile = __DIR__ . '/../logs/app_' . date('Y-m-d') . '.log';
        $this->logLevel = getenv('LOG_LEVEL') ?: 'INFO';
        
        if (!file_exists(dirname($this->logFile))) {
            mkdir(dirname($this->logFile), 0755, true);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Logger();
        }
        return self::$instance;
    }

    public function log($message, $level = 'INFO', $context = []) {
        $levels = ['DEBUG' => 1, 'INFO' => 2, 'WARNING' => 3, 'ERROR' => 4, 'CRITICAL' => 5];
        
        if ($levels[$level] < $levels[$this->logLevel]) {
            return;
        }

        $logEntry = sprintf(
            "[%s] %s: %s %s\n",
            date('Y-m-d H:i:s'),
            $level,
            $message,
            !empty($context) ? json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : ''
        );

        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
        
       
        $this->writeToConsole($logEntry, $level);
    }

    private function writeToConsole($message, $level) {
       
        if (php_sapi_name() === 'cli' || php_sapi_name() === 'cli-server') {
            $coloredMessage = $this->colors[$level] . $message . $this->colors['RESET'];
            fwrite(STDERR, $coloredMessage);
        }
    }

    
    public static function debug($message, $context = []) {
        self::getInstance()->log($message, 'DEBUG', $context);
    }

    public static function info($message, $context = []) {
        self::getInstance()->log($message, 'INFO', $context);
    }

    public static function warning($message, $context = []) {
        self::getInstance()->log($message, 'WARNING', $context);
    }

    public static function error($message, $context = []) {
        self::getInstance()->log($message, 'ERROR', $context);
    }

    public static function critical($message, $context = []) {
        self::getInstance()->log($message, 'CRITICAL', $context);
    }
}