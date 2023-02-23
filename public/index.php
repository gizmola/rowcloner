<?php
    use Monolog\Logger;
    use Monolog\Handler\StreamHandler;
    use Monolog\Handler\FirePHPHandler;
    use Symfony\Component\Dotenv\Dotenv;
    use Doctrine\DBAL\DriverManager;
    use Doctrine\DBAL\Exception\ConnectionException;
    use App\Gizmola\RequestHandler;
    use App\Gizmola\RowCloner;


    define('PROJECT_HOME', dirname(__DIR__));
    require_once(PROJECT_HOME . '/vendor/autoload.php');

    // Create the logger
    $logger = new Logger('main');
    // add handlers
    $logger->pushHandler(new StreamHandler(PROJECT_HOME . '/log/rowcloner.log', Logger::DEBUG));
    $logger->pushHandler(new FirePHPHandler());

    $dotenv = new Dotenv();
    $dotenv->load(PROJECT_HOME . '/.env.local');

    $requestHandler = new RequestHandler($logger);

    $processed = false;
    $bulmaMessageType = '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $requestHandler->checkPostVar('submit')) {
        $processed = true;
        if ($requestHandler->processPost()) {
            $dbConfig = $requestHandler->getConfig('db');

            $connectionParams = [
                'dbname' => $requestHandler->getPostVar('database'),
                'user' => $dbConfig['user'],
                'password' => $dbConfig['password'],
                'host' => $dbConfig['host'],
                'port' => $dbConfig['port'],
                'driver' => $dbConfig['driver'],
                'charset' => $dbConfig['charset']
            ];
            try {
                $conn = DriverManager::getConnection($connectionParams);
            } catch(ConnectionException $e) {
                $requestHandler->setMessageType(RequestHandler::MESSAGE_TYPE_ERROR);
                $requestHandler->setMessageText('Connection Error:' . $e->getMessage());
            }
            $rowcloner = new RowCloner($conn, $logger);

            if ($rowcloner->clone(
                $requestHandler->getPostVar('table'),
                $requestHandler->getTableIdName(),
                $requestHandler->getPostVar('id'),
                $requestHandler->getNullColumns()
            )){
                $requestHandler->setMessageType(RequestHandler::MESSAGE_TYPE_SUCCESS);
                $requestHandler->setMessageText('Cloned ' . $requestHandler->getPostVar('database') . '.' . $requestHandler->getPostVar('table') . ' [original Id: ' . $requestHandler->getPostVar('id') . '] New Id:' . $rowcloner->getNewId());
            } else {
                $requestHandler->setMessageType(RequestHandler::MESSAGE_TYPE_ERROR);
                $requestHandler->setMessageText('Clone failed.');
            }

        }

        $bulmaMessageType = ($requestHandler->getMessageType() == RequestHandler::MESSAGE_TYPE_SUCCESS) ? 'is-success' : 'is-danger';
    }
?>
<!DOCTYPE html>
<html lang="en" class="has-background-grey-light">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>row Cloner Tool</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.9.4/css/bulma.min.css" integrity="sha512-HqxHUkJM0SYcbvxUw5P60SzdOTy/QVwA1JJrvaXJv4q7lmbDZCmZaqz01UPOaQveoxfYRv1tHozWGPMcuTBuvQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css" integrity="sha512-SzlrxWUlpfuzQ+pcUCosxcglQRNAq/DZjVsC0lE40xsADsfeQoEypE+enwcOiGjk/bSuGGKHEyjSoQ1zVisanQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <style>
            html, body { 
                height: 100vh; 
            }
            body {
                display: flex;
                min-height: 100vh;
                flex-direction: column;
            }
        </style>
    </head>
    <body>
        <section class="hero is-info is-small mb-4">
            <div class="hero-body has-text-centered">
                <h1 class="title mt-4">
                    <span class="icon-text">
                        <span class="icon">
                            <i class="fa-solid fa-robot"></i>
                        </span>
                        <span class="ml-3">Row Cloner</span>
                    </span>
                </h1>
            </div>
        </section>
        <section id="main" class="section">
            <div class="columns">
                <div class="column is-three-fifths is-offset-one-fifth">
                    <form class="box" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
                        
                        <div class="field is-horizontal">
                            <label class="field-label is-normal icon-text">
                                <span class="icon-text">
                                    <span class="icon">
                                        <i class="fa-solid fa-database"></i>
                                    </span>
                                    <span>Database</span>
                                </span>
                            </label>
                            <div class="field-body">
                                <div class="field">
                                    <div class="control">
                                        <div class="select">
                                            <select name="database">
                                                <?php foreach($requestHandler->getConfig('rc_dbs') as $db) :?>
                                                    <option><?= $db ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="field is-horizontal">
                            <label class="field-label is-normal icon-text">
                                <span class="icon-text">
                                    <span class="icon">
                                        <i class="fa-solid fa-table"></i>
                                    </span>
                                    <span>Table</span>
                                </span>
                            </label>
                            <div class="field-body">
                                <div class="field">
                                    <div class="control is-expanded">
                                        <div class="select">
                                            <select name="table">
                                            <?php foreach($requestHandler->getConfig('rc_tables') as $table) :?>
                                                    <option><?= $table ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="field is-horizontal">
                            <label class="field-label icon-text">
                                <span class="icon-text">
                                    <span class="icon">
                                        <i class="fa-solid fa-key"></i>
                                    </span>
                                    <span>Row Id #</span>
                                </span>
                            </label>
                            <div class="field-body">
                                <div class="field">
                                    <div class="control">
                                        <input name="id" class="input" type="number" min="1" placeholder="ID to clone">
                                    </div>
                                    <p class="help is-danger">
                                        required
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="field is-horizontal">
                            <label class="field-label is-normal icon-text">
                                <span class="icon-text">
                                    <span class="icon">
                                        <i class="fa-regular fa-square-full"></i>
                                    </span>
                                    <span>null columns</span>
                                </span>
                            </label>
                            <div class="field-body">
                                <div class="field">
                                    <div class="control is-expanded">
                                        <input name="null_columns" class="input" type="text" placeholder="optional list of columns to null">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="field is-grouped is-grouped-centered">
                            <p class="control">
                                <input class="button is-primary" name="submit" type="submit" value="Submit">
                            </p>
                            <p class="control">
                                <input class="button is-light" type="reset" value="Reset">
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </section>
        <?php if ($processed): ?>
            <section class="section">
                <div class="columns">
                    <div class="column is-three-fifths is-offset-one-fifth">
                        <article class="message <?= $bulmaMessageType ?>">
                            <div class="message-header">
                            <p><?php echo $requestHandler->getMessageType(); ?></p>
                            <button class="delete" aria-label="delete"></button>
                            </div>
                            <div class="message-body">
                                <?php echo $requestHandler->getMessageText(); ?>
                            </div>
                        </article>
                    </div>
                </div>
            </section>
        <?php endif; ?>
        <footer class="section has-text-centered mt-auto">
            <strong>Row Cloner</strong> by <a href="https://www.gizmola.com">David Rolston</a>. The source code is licensed under the 
            <a href="http://opensource.org/licenses/mit-license.php">MIT</a> license. <i class="fa-brands fa-github m-1"></i>Github Project source repository <a href="https://github.com/gizmola/docker4lamp">here</a> 
        </footer>
    </body>
</html>


