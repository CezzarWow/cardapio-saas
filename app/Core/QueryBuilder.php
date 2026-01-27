<?php

namespace App\Core;

use PDO;

/**
 * Query Builder simples (ETAPA 4).
 * Monta SELECT de forma fluente para reduzir SQL hardcoded em repositórios.
 *
 * Uso:
 *   $qb = new QueryBuilder(Database::connect());
 *   $rows = $qb->select('o.*, SUM(oi.price) as total')
 *     ->from('orders o LEFT JOIN order_items oi ON oi.order_id = o.id')
 *     ->where('o.restaurant_id = :rid', ['rid' => $restaurantId])
 *     ->groupBy('o.id')
 *     ->orderBy('o.created_at', 'DESC')
 *     ->limit(20)->offset(0)
 *     ->get();
 */
final class QueryBuilder
{
    private PDO $conn;
    private string $select = '*';
    private string $from = '';
    private string $join = '';
    private string $where = '';
    /** @var array<string, mixed> */
    private array $params = [];
    private string $groupBy = '';
    private string $orderBy = '';
    private ?int $limitVal = null;
    private ?int $offsetVal = null;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function select(string $columns): self
    {
        $this->select = $columns;
        return $this;
    }

    public function from(string $table): self
    {
        $this->from = $table;
        return $this;
    }

    public function join(string $clause): self
    {
        $this->join .= ' ' . $clause;
        return $this;
    }

    /**
     * @param array<string, mixed> $params
     */
    public function where(string $condition, array $params = []): self
    {
        $this->where = $condition;
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    public function groupBy(string $columns): self
    {
        $this->groupBy = $columns;
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $dir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->orderBy = $column . ' ' . $dir;
        return $this;
    }

    public function limit(int $n): self
    {
        $this->limitVal = $n;
        return $this;
    }

    public function offset(int $n): self
    {
        $this->offsetVal = $n;
        return $this;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function get(): array
    {
        $stmt = $this->conn->prepare($this->toSql());
        $stmt->execute($this->params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return is_array($result) ? $result : [];
    }

    public function toSql(): string
    {
        $sql = 'SELECT ' . $this->select . ' FROM ' . $this->from;
        if ($this->join !== '') {
            $sql .= $this->join;
        }
        if ($this->where !== '') {
            $sql .= ' WHERE ' . $this->where;
        }
        if ($this->groupBy !== '') {
            $sql .= ' GROUP BY ' . $this->groupBy;
        }
        if ($this->orderBy !== '') {
            $sql .= ' ORDER BY ' . $this->orderBy;
        }
        if ($this->limitVal !== null) {
            $sql .= ' LIMIT ' . (int) $this->limitVal;
        }
        if ($this->offsetVal !== null) {
            $sql .= ' OFFSET ' . (int) $this->offsetVal;
        }
        return $sql;
    }

}
