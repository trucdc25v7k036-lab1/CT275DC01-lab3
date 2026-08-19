<?php

namespace CT275\Labs;

use PDO;

class PDOFactory
{
    public function create(array $config): PDO
    {
        $dsn = sprintf(
            'pgsql:host=%s;dbname=%s',
            $config['dbhost'],
            $config['dbname']
        );

        return new PDO(
            $dsn,
            $config['dbuser'],
            $config['dbpass'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }
}