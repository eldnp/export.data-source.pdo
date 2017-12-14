<?php
/*
 * This file is part of Eldnp/export.data-source.pdo.
 *
 * Eldnp/export.data-source.pdo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Eldnp/export.data-source.pdo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Eldnp/export.data-source.pdo. If not, see <http://www.gnu.org/licenses/>.
 *
 * @see       https://github.com/eldnp/export.data-source.pdo for the canonical source repository
 * @copyright Copyright (c) 2017 Oleg Verevskoy <verevskoy@gmail.com>
 * @license   https://github.com/eldnp/export.data-source.pdo/blob/master/LICENSE GNU GENERAL PUBLIC LICENSE Version 3
 */

namespace Eldnp\Export\DataSource\Pdo;

use Eldnp\Export\DataSource\Pdo\Exception\LogicException;
use Eldnp\Export\DataSource\Pdo\Exception\RuntimeException;
use Eldnp\Export\DataSourceInterface;

/**
 * Class PaginatedDataSource
 *
 * @package Eldnp\Export\DataSource\Pdo
 */
class PaginatedDataSource implements DataSourceInterface
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var string
     */
    private $query;

    /**
     * @var string
     */
    private $leftPlaceholder;

    /**
     * @var string
     */
    private $rightPlaceholder;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @see constants \PDO::FETCH_...
     *
     * @var int|null
     */
    private $fetchStyle;

    /**
     * @var \PDOStatement
     */
    private $currentStatement;

    /**
     * @var array|false
     */
    private $currentRawData = false;

    /**
     * @var int
     */
    private $counter = 0;

    /**
     * @var bool
     */
    private $allowRewind = true;

    /**
     * @var int
     */
    private $offset = 0;

    /**
     * PaginatedDataSource constructor.
     *
     * @param \PDO $pdo
     * @param string $query
     * @param string $leftPlaceholder
     * @param string $rightPlaceholder
     * @param int $batchSize
     * @param int|null $fetchStyle
     */
    public function __construct(\PDO $pdo, $query, $leftPlaceholder, $rightPlaceholder, $batchSize, $fetchStyle = null)
    {
        $this->pdo = $pdo;
        $this->query = $query;
        $this->leftPlaceholder = $leftPlaceholder;
        $this->rightPlaceholder = $rightPlaceholder;
        $this->setBatchSize($batchSize);
        $this->fetchStyle = $fetchStyle;
    }

    /**
     * @param int $batchSize
     */
    private function setBatchSize($batchSize)
    {
        if ($batchSize <= 0) {
            throw new LogicException('Limit must have a non-negative value');
        }
        $this->batchSize = $batchSize;
    }

    /**
     * @param string $errorMessage
     * @param array $errorInfo
     * @param string $query
     *
     * @return RuntimeException
     */
    private function createPdoException($errorMessage, $errorInfo, $query)
    {
        return new RuntimeException(sprintf(
            '%s. Error: %s. Query: %s.',
            $errorMessage,
            implode(', ', $errorInfo),
            $query
        ));
    }

    /**
     * @return \PDOStatement
     *
     * @throws RuntimeException
     */
    private function buildNextStatement()
    {
        if (false === $statement = $this->pdo->prepare($this->query)) {
            throw $this->createPdoException(
                'An error occurred while preparing the request',
                $this->pdo->errorInfo(),
                $this->query
            );
        }

        $leftBorder = $this->offset;
        $rightBorder = $this->offset += $this->batchSize;
        $statement->bindParam($this->leftPlaceholder, $leftBorder, \PDO::PARAM_INT);
        $statement->bindParam($this->rightPlaceholder, $rightBorder, \PDO::PARAM_INT);

        if (false === $statement->execute()) {
            throw $this->createPdoException(
                'An error occurred while running the request',
                $statement->errorInfo(),
                $statement->queryString
            );
        }

        return $statement;
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        return $this->currentRawData;
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        $allowNextStatement = true;
        while (false === $this->currentRawData = $this->currentStatement->fetch($this->fetchStyle)) {
            if (!$allowNextStatement) {
                return;
            }
            $this->currentStatement = $this->buildNextStatement();
            $allowNextStatement = false;
        }
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return $this->counter;
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        if ($valid = false !== $this->currentRawData) {
            $this->counter++;
        }
        return $valid;
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        if (!$this->allowRewind) {
            throw new LogicException('Rewind not supported');
        }

        $this->allowRewind = false;
        $this->currentStatement = $this->buildNextStatement();
        $this->next();
    }
}
