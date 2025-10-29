<?php
declare(strict_types=1);

/**
 * Returns a shared PDO connection using environment configuration.
 *
 * Expected environment variables:
 * - DB_HOST (default: localhost)
 * - DB_NAME (default: fadac3356)
 * - DB_USER (default: fadac3356)
 * - DB_PASS (default: 1a76fadac3356)
 */

if (!class_exists('PDO')) {
    if (!class_exists('mysqli')) {
        http_response_code(500);
        echo '<h1>Mangler database-utvidelser</h1>';
        echo '<p>Serveren trenger enten PDO (med pdo_mysql) eller MySQLi for å koble til databasen.</p>';
        exit;
    }

    class PDOException extends Exception {}

    class PDOStatement
    {
        private mysqli $connection;
        private ?mysqli_stmt $statement = null;
        private ?mysqli_result $result = null;
        private array $paramOrder = [];
        private int $rowCount = 0;
        private int $defaultFetchMode;

        public function __construct(
            mysqli $connection,
            string $query,
            int $defaultFetchMode,
            bool $prepared = false,
            $result = null
        ) {
            $this->connection = $connection;
            $this->defaultFetchMode = $defaultFetchMode;

            if ($prepared) {
                [$convertedQuery, $paramOrder] = $this->convertNamedParameters($query);
                $this->statement = $connection->prepare($convertedQuery);
                if (!$this->statement) {
                    throw new PDOException('Failed to prepare statement: ' . $connection->error);
                }
                $this->paramOrder = $paramOrder;
            } else {
                if ($result instanceof mysqli_result) {
                    $this->result = $result;
                    $this->rowCount = $result->num_rows;
                } else {
                    $this->rowCount = $connection->affected_rows;
                }
            }
        }

        public function __destruct()
        {
            if ($this->result instanceof mysqli_result) {
                $this->result->free();
            }
            if ($this->statement instanceof mysqli_stmt) {
                $this->statement->close();
            }
        }

        private function convertNamedParameters(string $query): array
        {
            $order = [];

            $converted = preg_replace_callback(
                '/:([a-zA-Z_][a-zA-Z0-9_]*)/',
                static function (array $matches) use (&$order): string {
                    $order[] = $matches[1];
                    return '?';
                },
                $query,
            );

            return [$converted, $order];
        }

        public function execute(?array $params = null): bool
        {
            if ($this->statement === null) {
                return true;
            }

            $params = $params ?? [];

            $orderedValues = [];
            if ($this->paramOrder !== []) {
                foreach ($this->paramOrder as $name) {
                    if (!array_key_exists($name, $params)) {
                        throw new PDOException("Missing parameter :$name");
                    }
                    $orderedValues[] = $params[$name];
                }
            } else {
                $orderedValues = array_values($params);
            }

            if ($orderedValues !== []) {
                $types = '';
                $bindValues = [];

                foreach ($orderedValues as $index => $value) {
                    $types .= $this->detectType($value);
                    $bindValues[$index] = &$orderedValues[$index];
                }

                array_unshift($bindValues, $types);

                if (!call_user_func_array([$this->statement, 'bind_param'], $bindValues)) {
                    throw new PDOException('Failed to bind parameters: ' . $this->statement->error);
                }
            }

            if (!$this->statement->execute()) {
                throw new PDOException('Failed to execute statement: ' . $this->statement->error);
            }

            $this->rowCount = $this->statement->affected_rows;
            return true;
        }

        private function detectType($value): string
        {
            if (is_int($value)) {
                return 'i';
            }
            if (is_float($value)) {
                return 'd';
            }

            return 's';
        }

        public function fetchAll(?int $mode = null): array
        {
            if (!$this->result instanceof mysqli_result) {
                return [];
            }

            $mode = $mode ?? $this->defaultFetchMode;
            $type = $mode === PDO::FETCH_ASSOC ? MYSQLI_ASSOC : MYSQLI_NUM;

            return $this->result->fetch_all($type);
        }

        public function rowCount(): int
        {
            return $this->rowCount;
        }
    }

    class PDO
    {
        public const ATTR_ERRMODE = 3;
        public const ERRMODE_EXCEPTION = 2;
        public const ATTR_DEFAULT_FETCH_MODE = 19;
        public const FETCH_ASSOC = 2;
        public const ATTR_EMULATE_PREPARES = 20;

        private mysqli $connection;
        private int $defaultFetchMode = self::FETCH_ASSOC;

        public function __construct(string $dsn, ?string $username = null, ?string $password = null, array $options = [])
        {
            if (strpos($dsn, 'mysql:') !== 0) {
                throw new PDOException('Unsupported DSN: ' . $dsn);
            }

            $configuration = $this->parseDsn(substr($dsn, 6));

            $host = $configuration['host'] ?? 'localhost';
            $dbname = $configuration['dbname'] ?? '';
            $charset = $configuration['charset'] ?? 'utf8mb4';

            $this->connection = mysqli_init();
            if (!$this->connection) {
                throw new PDOException('Unable to initialise MySQLi');
            }

            if (!$this->connection->real_connect($host, $username ?? '', $password ?? '', $dbname)) {
                throw new PDOException('Connection failed: ' . mysqli_connect_error());
            }

            if ($charset !== '') {
                $this->connection->set_charset($charset);
            }

            if (isset($options[self::ATTR_DEFAULT_FETCH_MODE])) {
                $this->defaultFetchMode = (int) $options[self::ATTR_DEFAULT_FETCH_MODE];
            }
        }

        private function parseDsn(string $dsn): array
        {
            $config = [];
            $parts = explode(';', $dsn);

            foreach ($parts as $part) {
                if ($part === '') {
                    continue;
                }

                [$key, $value] = array_pad(explode('=', $part, 2), 2, '');
                $config[$key] = $value;
            }

            return $config;
        }

        public function query(string $query): PDOStatement
        {
            $result = $this->connection->query($query);

            if ($result === false) {
                throw new PDOException('Query failed: ' . $this->connection->error);
            }

            return new PDOStatement($this->connection, $query, $this->defaultFetchMode, false, $result);
        }

        public function prepare(string $query): PDOStatement
        {
            return new PDOStatement($this->connection, $query, $this->defaultFetchMode, true);
        }

        public function setAttribute(int $attribute, $value): void
        {
            if ($attribute === self::ATTR_DEFAULT_FETCH_MODE) {
                $this->defaultFetchMode = (int) $value;
            }
        }
    }
}

function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dbHost = getenv('DB_HOST') ?: 'localhost';
    $dbName = getenv('DB_NAME') ?: 'fadac3356';
    $dbUser = getenv('DB_USER') ?: 'fadac3356';
    $dbPass = getenv('DB_PASS') ?: '1a76fadac3356';

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $dbHost, $dbName);

    try {
        $pdo = new PDO(
            $dsn,
            $dbUser,
            $dbPass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ],
        );
    } catch (Throwable $exception) {
        http_response_code(500);
        echo '<h1>Database connection failed</h1>';
        echo '<p>' . htmlspecialchars($exception->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
        exit;
    }

    return $pdo;
}
