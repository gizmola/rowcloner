<?php declare(strict_types=1);

namespace App\Gizmola;

use Monolog\Logger;
use Throwable;

final class RequestHandler {
    use DebugTrait;

    const MESSAGE_TYPE_SUCCESS = 'Success';
    const MESSAGE_TYPE_ERROR = 'Error';

    private Logger $logger;
    private $config = array();
    private $messageType = '';
    private $messageText = '';
    private $nullColumns = array();

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
        $this->loadConfigFromEnv();
    }

    public function getTableIdName()
    {
        $appendToTable = $_ENV['RC_APPEND_ID_TO_TABLE'] ?? '';
        
        $this->debug('getTableIdName', [$appendToTable]);

        if (!($appendToTable == 'true')) {
            return $_ENV['RC_DEFAULT_ID_NAME'];    
        }
        return $this->getPostVar('table') . $_ENV['RC_ID_PREFIX'] . $_ENV['RC_DEFAULT_ID_NAME'];
        
    }

    private function loadConfigFromEnv() 
    {
        $this->config['rc_dbs'] = explode(' ', $_ENV['RC_DBS']);
        $this->config['rc_tables'] = explode(' ', $_ENV['RC_TABLES']);
        
        $this->config['db']['driver'] = $_ENV['PDO_DRIVER'];
        $this->config['db']['host'] = $_ENV['DB_HOST'];
        $this->config['db']['user'] = $_ENV['DB_USER'];
        $this->config['db']['password'] = $_ENV['DB_PASSWORD']; 
        $this->config['db']['port'] = $_ENV['DB_PORT'];
        $this->config['db']['charset'] = $_ENV['DB_CHARSET'];
        $this->config['db']['dbname'] = $_ENV['RC_DEFAULT_DB'];
        $this->debug('dotenv loaded', $this->config);
    }

    public function setMessageType($status) 
    {
        $this->messageType = $status; 
    }

    public function setMessageText($message)
    {
        $this->messageText = $message; 
    }

    public function getConfig($key) 
    {
        return $this->config[$key] ?? '';
    }

    private function inConfigArray($value, $arrayName) 
    {
        return (in_array($value, $arrayName, true));
    }

    public function checkPostVar($key) 
    {
        if (!array_key_exists($key, $_POST)) {
            return false;
        }
        $_POST[$key] = trim($_POST[$key]);
        if (empty($_POST[$key])) {
            return false;
        }
        return true;
    }

    public function getPostVar($key) 
    {
        return $_POST[$key] ?? '';
    }

    public function updateConfigFromPost($key, $var) 
    {
        if ($this->checkPostVar($key)) {
            $var = $_POST[$key];
        }
    }

    public function getMessageType() 
    {
        return $this->messageType;
    }

    public function getMessageText() 
    {
        return $this->messageText;
    }

    private function setError($message): bool 
    {
        $this->setMessageType(self::MESSAGE_TYPE_ERROR);
        $this->setMessageText($message);
        return false;
    }

    public function getNullColumns()
    {
        $this->debug('Null columns list', $this->nullColumns);
        return $this->nullColumns;
    }

    public function processPost(): bool
    {
        if (!$this->checkPostVar('database')) {         
            return $this->setError('form database missing');
        } 
        
        if (!$this->inConfigArray($this->getPostVar('database'), $this->getConfig('rc_dbs'))) {
            return $this->setError('database not allowed');
        }

        if (!$this->checkPostVar('table')) {
            return $this->setError('form table missing');
        } 
        
        if (!$this->inConfigArray($this->getPostVar('table'), $this->getConfig('rc_tables'))) {
            return $this->setError('table not allowed');
        }

        if (!$this->checkPostVar('id')) {
            return $this->setError('form id missing');
        }
        
        $this->nullColumns[] = $this->getTableIdName();

        if ($this->checkPostVar('null_columns')) {
            $optionalNullColumns = explode(' ', $this->getPostVar('null_columns'));
            $this->nullColumns = array_merge($this->nullColumns, $optionalNullColumns);
            $this->debug('Optional nulls added', $this->nullColumns);
        }
        return true;
    }
}