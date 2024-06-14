<?php

namespace App\Repository;

use App\DataBase\DBConnection;
use App\Entity\AbstractEntity;
use App\Repository\Exception\NotFoundException;
use App\URL;
use Exception;
use PDO;

class Pagination
{
    private string $query;
    private string $query_count;
    private array $params;
    private string $order;
    private PDO $pdo;
    private int $per_page;
    private ?int $count = null;
    private ?array $entities = null;

    public function __construct(
        string $query,
        string $query_count,
        array $params,
        string $order = 'id ASC',
        int $per_page = 20,
        PDO $pdo = null
    ) {
        $this->query = $query;
        $this->query_count = $query_count;
        $this->params = $params;
        $this->order = htmlentities($order);
        $this->pdo = $pdo ?: DBConnection::get_pdo();
        $this->per_page = $per_page;
        if ($this->count === null):
            $query = $this->pdo->prepare($this->query_count);
            $query->execute($this->params);
            $this->count = (int) $query->fetch(PDO::FETCH_NUM)[0];
        endif;

    }

    /**
     * @return AbstractEntity[]
     */
    public function get_entities(string $entity, string $table, ?string $message = null): ?array
    {
        if ($this->entities === null):
            if ($this->get_current_page() > $this->get_pages() && $this->get_pages() !== 0):
                if ($this->get_pages() > 1):
                    $_GET['page'] = $this->get_pages();
                else:
                    unset($_GET['page']);
                endif;
                header('Location: ' . $_SERVER['REDIRECT_URL'] . (!empty($_GET) ? '?' . http_build_query($_GET) : ''));
            endif;

            $offset = $this->per_page * ($this->get_current_page() - 1);
            $this->query .= " ORDER BY $this->order LIMIT $this->per_page OFFSET $offset";
            $query = $this->pdo->prepare($this->query);
            $query->execute($this->params);
            $this->entities = $query->fetchAll(PDO::FETCH_CLASS, $entity);
        endif;
        if ($this->entities === false):
            $fields = implode($this->params);
            throw new NotFoundException($table, $fields, $message);
        endif;
        return $this->entities;
    }

    public function get_count(): ?int
    {
        return $this->count;
    }

    public function get_per_page(): ?int
    {
        return $this->per_page;
    }

    public function get_current_page(): int
    {
        return URL::get_positif_int('page', 1);
    }

    public function get_pages(): int
    {
        return ceil($this->count / $this->per_page);
    }

    public function previous_link(string $link): ?string
    {
        $datas = $_GET;
        if (isset($datas['page'])):
            unset($datas['page']);
        endif;
        $status = ['published', 'draft', 'trashed', 'delete'];
        foreach ($status as $state):
            if (isset($datas[$state])):
                unset($datas[$state]);
            endif;
        endforeach;

        $link .= '?' . http_build_query(array_merge($datas, $this->get_current_page() > 2 ? ['page' => $this->get_current_page() - 1] : []));
        return $link;
    }

    public function number_link(string $link, int $number): ?string
    {
        $datas = $_GET;
        if (isset($datas['page'])):
            unset($datas['page']);
        endif;
        $status = ['published', 'draft', 'trashed', 'delete'];
        foreach ($status as $state):
            if (isset($datas[$state])):
                unset($datas[$state]);
            endif;
        endforeach;

        $query_string = http_build_query(array_merge($datas, $number !== 1 ? ['page' => $number] : []));
        return "$link?$query_string";
    }

    public function next_link(string $link): ?string
    {
        $datas = $_GET;
        $status = ['published', 'draft', 'trashed', 'delete'];
        foreach ($status as $state):
            if (isset($datas[$state])):
                unset($datas[$state]);
            endif;
        endforeach;

        $query_string = http_build_query(array_merge($datas, ['page' => $this->get_current_page() + 1]));
        return "$link?$query_string";
    }
}